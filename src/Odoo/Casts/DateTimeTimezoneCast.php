<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Casts;

/**
 * Class DateTime
 * @extends Cast<T>
 * @package Obuchmann\OdooJsonRpc\Odoo\Casts
 */
class DateTimeTimezoneCast extends Cast
{

    private \DateTimeZone $timeZone;

    public function __construct(\DateTimeZone $timeZone = null)
    {
        $this->timeZone = $timeZone ?? new \DateTimeZone('UTC');
    }

    public function getType(): string
    {
        return \DateTime::class;
    }

    public function cast($raw)
    {
        if($raw){
            try {
                $dt =  new \DateTime($raw);
                $dt->setTimezone($this->timeZone);
                return $dt;
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