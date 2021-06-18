<?php


namespace Obuchmann\OdooJsonRpc\Odoo;


class Context
{

    /**
     * Context constructor.
     * @param string|null $lang
     * @param string|null $timezone
     * @param int|null $companyId
     */
    public function __construct(
        protected ?string $lang = null,
        protected ?string $timezone = null,
        protected ?int $companyId = null
    )
    {
    }


    public function toArray(): array
    {
        return array_filter([
            'lang' => $this->lang,
            'tz' => $this->timezone,
            'company_id' => $this->companyId
        ]);
    }

    public function clone(): Context
    {
        return clone($this);
    }

}