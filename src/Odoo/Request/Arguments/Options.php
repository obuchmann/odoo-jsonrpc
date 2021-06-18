<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


use Obuchmann\OdooJsonRpc\Odoo\Context;

class Options
{
    public function __construct(private array $options = [], private ?Context $context = null)
    {
    }

    public function toArray(): array
    {
        $context = $this->context->toArray();
        if(empty($context)){
            return $this->options;
        }
        return ['context' => $context] + $this->options;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        return $this;
    }

    public function setRaw(string $key, $value)
    {
        $this->options[$key] = $value;
    }

    public function limit(int $value)
    {
        $this->setRaw('limit', $value);
    }

    public function offset(int $value)
    {
        $this->setRaw('offset', $value);
    }
}