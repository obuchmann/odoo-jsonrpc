<?php

namespace Obuchmann\OdooJsonRpc\Odoo;

use Obuchmann\OdooJsonRpc\Attributes\BelongsTo;
use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\HasMany;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Exceptions\ConfigurationException;
use Obuchmann\OdooJsonRpc\Exceptions\OdooModelException;
use Obuchmann\OdooJsonRpc\Exceptions\UndefinedPropertyException;
use Obuchmann\OdooJsonRpc\Odoo;
use Obuchmann\OdooJsonRpc\Odoo\Mapping\HasFields;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

class OdooModel
{
    use HasFields;

    private static Odoo $odoo;
    private static ?string $model = null;

    /** @var array Cache for relationship attributes */
    private static array $relationAttributesCache = [];

    public static function boot(Odoo $odoo)
    {
        self::$odoo = $odoo;
    }

    public static function listFields(?array $fields = null): object
    {
        return self::$odoo->fieldsGet(static::model(), $fields);
    }

    public static function find(int $id): ?static
    {
        $odooInstance = self::$odoo->find(static::model(), $id, static::fieldNames());
        if(null === $odooInstance){
            return null;
        }
        return static::hydrate($odooInstance);
    }

    public static function read(array $ids): array
    {
        return array_map(fn($item) => static::hydrate($item), self::$odoo->read(static::model(), $ids, static::fieldNames()));
    }

    protected static function model()
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $model = $reflectionClass->getAttributes(Model::class)[0] ?? throw new ConfigurationException("Missing Model Attribute");

        return $model->newInstance()->name;
    }

    public static function query()
    {
        //TODO: Lazy evaluate fields only for queries that needs feelds :low
        return new Odoo\Models\ModelQuery(static::newInstance(), self::$odoo->model(static::model())->fields(static::fieldNames()));
    }

    public static function all()
    {
        return static::query()->get();
    }

    public int $id;

    public function exists()
    {
        return isset($this->id) && $this->id > 0; // Ensure ID is valid
    }

    /**
     * @return $this
     */
    public function save(): static
    {
        if ($this->exists()) {
            $updateResponse = self::$odoo->write(static::model(), [$this->id], (array)static::dehydrate($this));
            if (false === $updateResponse) {
                throw new OdooModelException("Failed to update model");
            }
        } else {
            $createResponse = self::$odoo->create(static::model(), (array)static::dehydrate($this));
            if (false === $createResponse) {
                throw new OdooModelException("Failed to create model");
            }
            $this->id = $createResponse;
        }

        return $this;
    }

    /**
     * Get relationship definitions (BelongsTo, HasMany) from attributes.
     * Caches the results for performance.
     *
     * @return array<string, array{type: string, attribute: BelongsTo|HasMany, property: ReflectionProperty}>
     */
    protected static function getRelationAttributes(): array
    {
        $class = static::class;
        if (isset(self::$relationAttributesCache[$class])) {
            return self::$relationAttributesCache[$class];
        }

        $relations = [];
        $reflectionClass = new ReflectionClass($class);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC); // Only public properties

        foreach ($properties as $property) {
            // BelongsTo
            $belongsToAttrs = $property->getAttributes(BelongsTo::class);
            if (!empty($belongsToAttrs)) {
                if (count($belongsToAttrs) > 1) {
                    throw new ConfigurationException("Property {$property->getName()} on {$class} cannot have multiple BelongsTo attributes.");
                }
                $relations[$property->getName()] = [
                    'type' => 'BelongsTo',
                    'attribute' => $belongsToAttrs[0]->newInstance(),
                    'property' => $property,
                ];
                continue; // Process only one relationship type per property
            }

            // HasMany
            $hasManyAttrs = $property->getAttributes(HasMany::class);
            if (!empty($hasManyAttrs)) {
                if (count($hasManyAttrs) > 1) {
                    throw new ConfigurationException("Property {$property->getName()} on {$class} cannot have multiple HasMany attributes.");
                }
                 // Ensure the property type is array or iterable
                $type = $property->getType();
                if (!$type || !($type->getName() === 'array' || is_subclass_of($type->getName(), \Traversable::class))) {
                     // Or check if nullable array type etc.
                    // throw new ConfigurationException("Property {$property->getName()} on {$class} with HasMany attribute must be typed as array or iterable.");
                    // Allow untyped for now, but typing is recommended.
                }
                $relations[$property->getName()] = [
                    'type' => 'HasMany',
                    'attribute' => $hasManyAttrs[0]->newInstance(),
                    'property' => $property,
                ];
            }
        }

        self::$relationAttributesCache[$class] = $relations;
        return $relations;
    }

    /**
     * Load relationships for the current model instance.
     *
     * @param string ...$relations Names of the relationship properties to load.
     * @return $this
     * @throws ConfigurationException|OdooModelException
     */
    public function load(string ...$relations): static
    {
        if (!$this->exists()) {
            throw new OdooModelException("Cannot load relations on a non-existent model.");
        }

        $allRelationDefs = static::getRelationAttributes();
        $modelsToLoad = [$this]; // Load for this single model

        foreach ($relations as $relationName) {
            if (!isset($allRelationDefs[$relationName])) {
                throw new ConfigurationException("Relation '{$relationName}' not defined on " . static::class);
            }

            $definition = $allRelationDefs[$relationName];
            /** @var BelongsTo|HasMany $attribute */
            $attribute = $definition['attribute'];
            $relatedClass = $attribute->related;

            if (!class_exists($relatedClass) || !is_subclass_of($relatedClass, OdooModel::class)) {
                 throw new ConfigurationException("Relation '{$relationName}' on " . static::class . " points to an invalid OdooModel class '{$relatedClass}'.");
            }

            match ($definition['type']) {
                'BelongsTo' => static::loadBelongsToRelation($modelsToLoad, $relationName, $attribute),
                'HasMany'   => static::loadHasManyRelation($modelsToLoad, $relationName, $attribute),
                default     => throw new RuntimeException("Unknown relation type {$definition['type']}"),
            };
        }

        return $this;
    }

    public function executeKw(string $method, array $args = [])
    {
        return self::$odoo->executeKw(static::model(), $method, [$this->id,...$args]);
    }

    public function fill(iterable $properties)
    {
        $reflectionClass = new \ReflectionClass(static::class);

        foreach ($properties as $name => $value) {
            if($reflectionClass->hasProperty($name)){
                $this->{$name} = $value;
            }else {
                throw new UndefinedPropertyException("Property $name not defined");
            }
        }

        return $this;
    }


    /**
     * Eager load relationships for a collection of models.
     *
     * @param iterable<OdooModel> $models The collection of models.
     * @param string ...$relations Names of the relationship properties to load.
     * @return iterable<OdooModel> The collection with relations loaded.
     * @throws ConfigurationException
     */
    public static function loadRelations(iterable $models, string ...$relations): iterable
    {
        if (empty($relations)) {
            return $models;
        }

        // Convert iterable to array for easier processing, handle empty case
        $modelsArray = is_array($models) ? $models : iterator_to_array($models);
        if (empty($modelsArray)) {
            return $modelsArray;
        }

        // Get relation definitions from the first model (assuming homogeneous collection)
        $firstModel = reset($modelsArray);
        if (!$firstModel instanceof OdooModel) {
             return $modelsArray; // Or throw error if non-model found
        }
        $allRelationDefs = $firstModel::getRelationAttributes();

        foreach ($relations as $relationName) {
            if (!isset($allRelationDefs[$relationName])) {
                throw new ConfigurationException("Relation '{$relationName}' not defined on " . get_class($firstModel));
            }

            $definition = $allRelationDefs[$relationName];
            /** @var BelongsTo|HasMany $attribute */
            $attribute = $definition['attribute'];
            $relatedClass = $attribute->related;

             if (!class_exists($relatedClass) || !is_subclass_of($relatedClass, OdooModel::class)) {
                 throw new ConfigurationException("Relation '{$relationName}' on " . get_class($firstModel) . " points to an invalid OdooModel class '{$relatedClass}'.");
            }

            match ($definition['type']) {
                'BelongsTo' => static::loadBelongsToRelation($modelsArray, $relationName, $attribute),
                'HasMany'   => static::loadHasManyRelation($modelsArray, $relationName, $attribute),
                 default     => throw new RuntimeException("Unknown relation type {$definition['type']}"),
            };
        }

        return $modelsArray;
    }

    /**
     * Helper to load a BelongsTo relation for a collection of models.
     *
     * @param array<OdooModel> $models
     * @param string $relationName
     * @param BelongsTo $attribute
     * @return void
     */
    protected static function loadBelongsToRelation(array $models, string $relationName, BelongsTo $attribute): void
    {
        /** @var class-string<OdooModel> $relatedClass */
        $relatedClass = $attribute->related;
        $foreignKeyProperty = $attribute->foreignKey; // PHP Property name holding the ID

        // Collect foreign key IDs from the models
        $foreignKeys = [];
        foreach ($models as $model) {
             // Check if the foreign key property exists and is initialized
             if (property_exists($model, $foreignKeyProperty) && isset($model->{$foreignKeyProperty})) {
                $fkValue = $model->{$foreignKeyProperty};
                if (is_int($fkValue) && $fkValue > 0) {
                    $foreignKeys[$fkValue] = $fkValue; // Use keys for uniqueness
                }
             } else {
                 // Handle cases where FK property might not exist or be set, maybe log a warning
                 // Or ensure hydration always sets it (even if null)
             }
        }

        if (empty($foreignKeys)) {
             // Set relation to null for all models if no valid keys found
             foreach ($models as $model) {
                 $model->{$relationName} = null;
             }
            return;
        }

        // Fetch related models in one query
        $relatedModels = $relatedClass::query()
            ->where('id', 'in', array_values($foreignKeys))
            ->get(); // Assuming get() returns an array of OdooModel instances

        // Build a dictionary of related models keyed by their ID
        $relatedDictionary = [];
        foreach ($relatedModels as $relatedModel) {
            $relatedDictionary[$relatedModel->id] = $relatedModel;
        }

        // Assign the related models back to the original models
        foreach ($models as $model) {
            $fkValue = property_exists($model, $foreignKeyProperty) ? ($model->{$foreignKeyProperty} ?? null) : null;
             if ($fkValue && isset($relatedDictionary[$fkValue])) {
                 $model->{$relationName} = $relatedDictionary[$fkValue];
             } else {
                 $model->{$relationName} = null; // Set to null if not found or FK was null/invalid
             }
        }
    }

     /**
     * Helper to load a HasMany relation for a collection of models.
     *
     * @param array<OdooModel> $models
     * @param string $relationName
     * @param HasMany $attribute
     * @return void
     */
    protected static function loadHasManyRelation(array $models, string $relationName, HasMany $attribute): void
    {
        /** @var class-string<OdooModel> $relatedClass */
        $relatedClass = $attribute->related;
        $foreignKeyOnRelated = $attribute->foreignKey; // Odoo field name on the related model

        // Collect parent model IDs
        $parentIds = [];
        foreach ($models as $model) {
             if ($model->exists()) {
                $parentIds[$model->id] = $model->id;
             }
        }

        if (empty($parentIds)) {
            // Set relation to empty array for all models
             foreach ($models as $model) {
                 $model->{$relationName} = [];
             }
            return;
        }

        // Fetch related models in one query using the foreign key on the related table
        $relatedModels = $relatedClass::query()
            ->where($foreignKeyOnRelated, 'in', array_values($parentIds))
            // ->orderBy(...) // Optionally add default ordering
            ->get();

        // Group related models by the foreign key (which links back to the parent ID)
        $groupedRelated = [];
        foreach ($relatedModels as $relatedModel) {
            // We need the foreign key *value* from the related model.
            // Assuming the foreign key property exists and was hydrated correctly.
            // This assumes the FK property name matches the Odoo field name,
            // or we need a way to map Odoo field -> PHP property if different.
            // Let's assume for now hydration makes $relatedModel->{$foreignKeyOnRelated} available.
            // This might require ensuring the FK field is included in the related model's `fieldNames()`.
            // A safer approach might be to use Reflection on the related model to find the
            // property marked with #[Field($foreignKeyOnRelated), Key].

            // --- Safer approach using reflection (simplified) ---
            $fkValue = null;
            $relatedReflection = new ReflectionClass($relatedModel);
            foreach ($relatedReflection->getProperties() as $prop) {
                 $fieldAttrs = $prop->getAttributes(Field::class);
                 $keyAttrs = $prop->getAttributes(Key::class); // Check for Key attribute too
                 if (!empty($fieldAttrs)) {
                     /** @var Field $fieldAttrInstance */
                     $fieldAttrInstance = $fieldAttrs[0]->newInstance();
                     $odooFieldName = $fieldAttrInstance->name ?? $prop->getName();
                     if ($odooFieldName === $foreignKeyOnRelated) {
                         // Check if it's a key field (likely holds just the ID)
                          if (!empty($keyAttrs) && property_exists($relatedModel, $prop->getName())) {
                            $fkValue = $relatedModel->{$prop->getName()};
                            break;
                          }
                          // If not a Key attribute, it might be [id, name]. Extract ID.
                          elseif (property_exists($relatedModel, $prop->getName()) && is_array($relatedModel->{$prop->getName()}) && isset($relatedModel->{$prop->getName()}[0])) {
                             $fkValue = $relatedModel->{$prop->getName()}[0];
                             break;
                          }
                          // Fallback if it's just the ID directly (less common for FKs in search_read)
                           elseif (property_exists($relatedModel, $prop->getName()) && is_int($relatedModel->{$prop->getName()})) {
                             $fkValue = $relatedModel->{$prop->getName()};
                             break;
                           }
                     }
                 }
            }
            // --- End safer approach ---

            // Original simpler (less safe) approach:
            // $fkValue = $relatedModel->{$foreignKeyOnRelated} ?? null; // Needs careful hydration setup
            // if (is_array($fkValue) && isset($fkValue[0])) { $fkValue = $fkValue[0]; } // Handle [id, name]

            if ($fkValue !== null && is_int($fkValue)) {
                $groupedRelated[$fkValue][] = $relatedModel;
            }
        }

        // Assign the grouped related models back to the original models
        foreach ($models as $model) {
            if (isset($groupedRelated[$model->id])) {
                $model->{$relationName} = $groupedRelated[$model->id];
            } else {
                $model->{$relationName} = []; // Initialize as empty array if no related found
            }
        }
    }


    public function equals(OdooModel $model)
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            if($property->isInitialized($this)){
                if(!$property->isInitialized($model)){
                    return false;
                }
                if($this->{$property->name} !== $model->{$property->name}){
                    return false;
                }
            }else{
                if($property->isInitialized($model)){
                    return false;
                }
            }

        }
        return true;
    }

}
