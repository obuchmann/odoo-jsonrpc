<?php

namespace Obuchmann\OdooJsonRpc\Tests;

use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;
use PHPUnit\Framework\TestCase;

class DomainTest extends TestCase
{
    public function testWhere()
    {
        $result = (new Domain())
            ->where('id', '=', 1)
            ->toArray()
        ;

        $this->assertEquals([['id', '=', 1]], $result);

    }

    public function testOr()
    {
        $result = (new Domain())
            ->where('id', '=', 1)
            ->orWhere('id', '=', 2)
            ->toArray()
        ;

        $this->assertEquals(['|',['id', '=', 1],['id', '=', 2]], $result);
    }

    public function testOrWhereMix()
    {
        $result = (new Domain())
            ->where('id', '=', 1)
            ->orWhere('id', '=', 2)
            ->where('company_id', '=', 3)
            ->toArray()
        ;

        $this->assertEquals(['|',['id', '=', 1],['id', '=', 2], ['company_id', '=', 3]], $result);
    }

    public function testOrWhereMultiple()
    {
        $result = (new Domain())
            ->where('id', '=', 1)
            ->orWhere('id', '=', 2)
            ->orWhere('company_id', '=', 3)
            ->toArray()
        ;

        $this->assertEquals(['|',['id', '=', 1], '|', ['id', '=', 2], ['company_id', '=', 3]], $result);
    }

}