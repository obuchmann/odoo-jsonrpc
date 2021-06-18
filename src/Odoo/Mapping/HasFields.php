<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Mapping;


use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\HasMany;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\KeyName;
use Obuchmann\OdooJsonRpc\Odoo\Casts\CastHandler;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;
use stdClass;

trait HasFields
{
    protected static function fieldNames(): array
    {
        $fieldNames = [];

        $reflectionClass = new \ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $attributes = $property->getAttributes(Field::class);

            foreach ($attributes as $attribute) {
                $fieldNames[] = $attribute->newInstance()->name ?? $property->name;
            }
        }
        return $fieldNames;
    }

    public static function hydrate(object $response): static
    {
        $castsExists = CastHandler::hasCasts();

        $reflectionClass = new \ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        $instance = static::newInstance();
        $instance->id = $response->id ?? null; // Id is always present

        foreach ($properties as $property) {
            $isKey = !empty($property->getAttributes(Key::class));
            $isKeyName = !empty($property->getAttributes(KeyName::class));
            $attributes = $property->getAttributes(Field::class);

            foreach ($attributes as $attribute) {
                $field = $attribute->newInstance()->name ?? $property->name;
                if (isset($response->{$field})) {
                    if($isKey){
                        $value = $response->{$field}[0] ?? null;
                    }elseif($isKeyName){
                        $value = $response->{$field}[1] ?? null;
                    }else{
                        $value = $response->{$field};
                    }
                    $instance->{$property->name} = $castsExists ? CastHandler::cast($property, $value) : $value;
                }

            }
        }

        return $instance;
    }

    public static function dehydrate(OdooModel $model): object
    {
        $castsExists = CastHandler::hasCasts();
        $item = new stdClass();

        $reflectionClass = new \ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $attributes = $property->getAttributes(Field::class);

            foreach ($attributes as $attribute) {
                $field = $attribute->newInstance()->name ?? $property->name;
                if (isset($model->{$property->name})) {
                    $item->{$field} = $castsExists ? CastHandler::uncast($property, $model->{$property->name}) : $model->{$property->name} ;
                }
            }

            $hasManyRelations = $property->getAttributes(HasMany::class);
            foreach ($hasManyRelations as $attribute) {
                $field = $attribute->newInstance()->name ?? $property->name;
                if (isset($model->{$property->name})) {

                    $values = $model->{$property->name};
                    if (null === $values)
                        continue;

                    if (self::isIdArray($values)) {
                        $item->{$field} = [[6, 0, $values]]; // Syncs given Ids
                    } else {
                        $commands = [];
                        foreach ($values as $value) {
                            if ($value instanceof OdooModel) {
                                if ($value->exists()) {
                                    $commands[] = [1, $value->id, $value->dehydrate($value)]; // Update related
                                } else {
                                    $commands[] = [0, 0, $value->dehydrate($value)]; // Create related
                                }
                            }
                        }
                        $item->{$field} = $commands;
                    }

                }
            }

        }

        return $item;
    }

    protected static function newInstance()
    {
        return new static();
    }

    private static function isIdArray(array $arr)
    {
        foreach ($arr as $item) {
            if (!is_int($item))
                return false;
        }
        return true;
    }
}