<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Casts;


class CastHandler
{
    private static array $classHandlers = [

    ];

    /**
     * @var Cast[]
     */
    private static array $wildcardHandlers = [];

    public static function reset()
    {
        self::$classHandlers = [];
        self::$wildcardHandlers = [];
    }

    public static function hasCasts():bool {
        return !empty(self::$classHandlers) || !empty(self::$wildcardHandlers);
    }

    public static function registerCast(Cast $cast)
    {
        $type = $cast->getType();
        if($type == Cast::WILDCARD){
            self::$wildcardHandlers[] = $cast;
        }else{
            self::$classHandlers[$type] ??= [];
            self::$classHandlers[$type][] = $cast;
            if($cast->handlesNullValues()){
                self::$classHandlers["?$type"][] = $cast;
            }
        }

    }

    public static function cast(\ReflectionProperty $class, $raw)
    {
        $type = (string)$class->getType();
        if(array_key_exists($type, self::$classHandlers)){
            foreach (self::$classHandlers[$type] as $handler){
                if($handler->applies($raw)){
                    return $handler->cast($raw);
                }
            }
        }else{
            foreach (self::$wildcardHandlers as $handler){
                if($handler->applies($raw)){
                    return $handler->cast($raw);
                }
            }
        }
        return $raw;
    }

    public static function uncast(\ReflectionProperty $class, $value)
    {
        $type = (string)$class->getType();
        if(array_key_exists($type, self::$classHandlers)){
            foreach (self::$classHandlers[$type] as $handler){
                if($handler->applies($value)){
                    return $handler->uncast($value);
                }
            }
        }else{
            foreach (self::$wildcardHandlers as $handler){
                if($handler->applies($value)){
                    return $handler->uncast($value);
                }
            }
        }
        return $value;
    }
}