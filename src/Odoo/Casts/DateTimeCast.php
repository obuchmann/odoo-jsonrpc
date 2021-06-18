<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Casts;

/**
 * Class DateTime
 * @extends Cast<T>
 * @package Obuchmann\OdooJsonRpc\Odoo\Casts
 */
class DateTimeCast extends Cast
{

    public function getType(): string
    {
        return \DateTime::class;
    }

    public function cast($raw)
    {
        if($raw){
            try {
                return new \DateTime($raw);
            } catch (\Exception) {} // If no valid Date return null
        }
        return null;
    }

    public function uncast($value)
    {
        if($value instanceof \DateTime){
            return $value->format('Y-m-d H:i:s');
        }
    }
}