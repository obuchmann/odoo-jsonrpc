<?php


namespace Obuchmann\OdooJsonRpc\Odoo;


class Context
{

    /**
     * Context constructor.
     * @param string|null $lang
     * @param string|null $timezone
     * @param int|null $companyId
     * @param array $contextArgs
     */
    public function __construct(
        protected ?string $lang = null,
        protected ?string $timezone = null,
        protected ?int    $companyId = null,
        protected array   $contextArgs = [],
    )
    {
    }

    public function setContextArg(string $key, mixed $value): void
    {
        $this->contextArgs[$key] = $value;
    }


    public function toArray(): array
    {
        return array_filter([
            'lang' => $this->lang,
            'tz' => $this->timezone,
            'company_id' => $this->companyId,
            ...$this->contextArgs
        ]);
    }

    public function clone(): static
    {
        return clone($this);
    }


    public function setDefaults(?Context $context): static
    {
        if($context){
            $this->lang = $this->lang ?? $context->lang;
            $this->timezone = $this->timezone ?? $context->timezone;
            $this->companyId = $this->companyId ?? $context->companyId;
            
            // Merge contextArgs, keeping existing values
            foreach ($context->contextArgs as $key => $value) {
                if (!array_key_exists($key, $this->contextArgs)) {
                    $this->setContextArg($key, $value);
                }
            }
        }
        
        return $this;
    }

}