<?php

namespace Obuchmann\OdooJsonRpc\Tests;

use Obuchmann\OdooJsonRpc\Exceptions\AuthenticationException;
use Obuchmann\OdooJsonRpc\Odoo;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Options;

class OdooTest extends TestCase
{



    public function testOdooAuthenticationException()
    {
        $this->expectException(AuthenticationException::class);

        (new Odoo(new Odoo\Config(
            $this->database,
            $this->host,
            $this->username,
            $this->password . 'invalid'))
        )->connect();

    }

    public function testVersion()
    {
        $version = $this->odoo->version();
        $this->assertInstanceOf(Odoo\Models\Version::class, $version);
    }

    public function testSuccessfulConnection()
    {
        $this->assertEquals('integer', gettype($this->odoo->getUid()));
    }

    public function testCheckModelAccess()
    {
        $check = $this->odoo->checkAccessRights('res.partner', 'read');

        $this->assertTrue($check);
    }

    public function testCheckModelAccessWithBuilder()
    {
        $check = $this->odoo->model('res.partner')
            ->can('read');

        $this->assertTrue($check);
    }

    public function testDirectCount()
    {
        $amount = $this->odoo->count('res.partner');
        $this->assertEquals('integer', gettype($amount));
    }
    
    public function testDirectCountWhere()
    {
        $amount = $this->odoo->count('res.partner');

        $customerAmountDomain = (new Domain())->where('is_company', '=', true);
        $customerAmount = $this->odoo->count('res.partner', $customerAmountDomain);

        $this->assertLessThan($amount, $customerAmount);
    }

    public function testModelCount()
    {
        $amount = $this->odoo
            ->model('res.partner')
            ->count();
        $this->assertEquals('integer', gettype($amount));
    }

    public function testModelCountWhere()
    {
        $amount = $this->odoo
            ->model('res.partner')
            ->count();

        $customerAmount = $this->odoo
            ->model('res.partner')
            ->where('is_company', '=', true)
            ->count();

        $this->assertLessThan($amount, $customerAmount);
    }

    public function testSearchLimit()
    {
        $ids = $this->odoo
            ->model('res.partner')
            ->limit(5)
            ->ids();

        $this->assertIsArray($ids);
    }

    public function testRead()
    {
        $ids = $this->odoo
            ->model('res.partner')
            ->limit(5)
            ->ids();

        $items = $this->odoo
            ->read('res.partner', $ids);

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
    }

    public function testFind()
    {
        $item = $this->odoo
            ->find('res.partner', 2);

        $this->assertIsObject($item);
    }

    public function testDirectSearchRead()
    {
        $items = $this->odoo->searchRead('res.partner', null, null, 0, 5);

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertNotNull($items[0]->name);
    }

    public function testDirectSearchReadFields()
    {
        $items = $this->odoo->searchRead('res.partner', null, ['name'], 0, 5);

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertNull($items[0]->email ?? null);
    }

    public function testModelSearchRead()
    {
        $items = $this->odoo
            ->model('res.partner')
            ->limit(5)
            ->get();

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertNotNull($items[0]->name);
    }

    public function testModelSearchReadFields()
    {
        $items = $this->odoo
            ->model('res.partner')
            ->fields(['name'])
            ->limit(5)
            ->get();

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertNull($items[0]->email ?? null);
    }

    public function testFirst()
    {
        $item = $this->odoo
            ->model('res.partner')
            ->first();


        $this->assertNotNull($item->name);
    }

    /** @test */
    public function testListFields()
    {
        $fields = $this->odoo
            ->listModelFields('res.partner');

        $this->assertIsObject($fields);
    }

    public function testCreateRecord()
    {

        $id = $this->odoo
            ->model('res.partner')
            ->create([
                'name' => 'Bobby Brown'
            ]);

        $this->assertEquals('integer', gettype($id));
    }

    public function testDeleteRecord()
    {
        if(!getenv('ODOO_HOST')){
            $this->markTestSkipped('Delete does not work on demo odoos');
        }
        $id = $this->odoo
            ->create('res.partner', [
                'name' => 'Bobby Brown'
            ]);

        $this->assertEquals('integer', gettype($id));

        $this->odoo
            ->deleteById('res.partner', $id);

        $ids = $this->odoo
            ->model('res.partner')
            ->where('id', '=', $id)
            ->ids();

        $this->assertEmpty($ids);
    }

    public function testDeleteSearch()
    {
        if(!getenv('ODOO_HOST')){
            $this->markTestSkipped('Delete does not work on demo odoos');
        }

        $id = $this->odoo
            ->create('res.partner', [
                'name' => 'Bobby Brown'
            ]);

        $this->assertEquals('integer', gettype($id));

        $deleteResponse = $this->odoo
            ->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->delete();

        $this->assertTrue($deleteResponse);

        $ids = $this->odoo
            ->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->ids();

        $this->assertEmpty($ids);

    }

    public function testUpdateById()
    {
        $id = $this->odoo
            ->create('res.partner', [
                'name' => 'Bobby Brown'
            ]);

        $this->assertEquals('integer', gettype($id));

        $updateResponse = $this->odoo
            ->updateById('res.partner', $id, [
                'name' => 'Dagobert Duck'
            ]);

        $this->assertTrue($updateResponse);

        $item = $this->odoo
            ->model('res.partner')
            ->where('id', '=', $id)
            ->fields(['name'])
            ->first();

        $this->assertEquals('Dagobert Duck', $item->name);
    }

    public function testUpdateSearch()
    {
        $id = $this->odoo
            ->create('res.partner', [
                'name' => 'Bobby Brown'
            ]);

        $this->assertEquals('integer', gettype($id));

        $updateResponse = $this->odoo
            ->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->update([
                'name' => 'Dagobert Duck'
            ]);

        $this->assertTrue($updateResponse);

        $ids = $this->odoo
            ->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->ids();

        $this->assertEmpty($ids);

    }

    public function testCallCustomMethod(){
        $request = new class('res.partner', 'search') extends Odoo\Request\Request {

            public function toArray(): array
            {
                return [
                    // 1.Parameter = Domain
                    [
                        ['is_company', '=', false]
                    ]
                ];
            }
        };
        $ids = $this->odoo
            ->execute($request, new Options([
                'limit' => 3
            ]));
        $this->assertIsArray($ids);
        $this->assertCount(3, $ids);
    }

    public function testCallCustomMethodOverlay(){
        $ids = $this->odoo->executeKw('res.partner', 'search', [
            [
                ['is_company', '=', false]
            ]
        ], new Options([
            'limit' => 3
        ]));
        $this->assertIsArray($ids);
        $this->assertCount(3, $ids);
    }


    public function testOr()
    {
        $id = $this->odoo
            ->model('res.partner')
            ->create([
                'name' => 'Bobby Brown'
            ]);

        $id2 = $this->odoo
            ->model('res.partner')
            ->create([
                'name' => 'Gregor Green'
            ]);

        $ids = $this->odoo->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->orWhere('name', '=', 'Gregor Green')
            ->ids();

        $this->assertTrue(in_array($id, $ids));
        $this->assertTrue(in_array($id2, $ids));

    }

    public function testAggregate()
    {

        $orderId = $this->odoo->model('sale.order')
            ->create([
                'name' => 'Aggregate Stuff',
                'partner_id' => 1
            ]);

        $time1 = $this->odoo->model('sale.order.line')
            ->create([
                'order_id' => $orderId,
                'product_id' => 1,
                'product_uom_qty' => 3
            ]);

        $time2 = $this->odoo->model('sale.order.line')
            ->create([
                'order_id' => $orderId,
                'product_id' => 1,
                'product_uom_qty' => 5
            ]);

        $response = $this->odoo->model('sale.order.line')
            ->where('order_id', '=', $orderId)
            ->groupBy(['order_id'])
            ->fields(['product_uom_qty:sum']) // Aggregation see: https://www.odoo.com/documentation/14.0/developer/reference/addons/orm.html#odoo.models.Model.read_group
            ->get();

        $this->assertEquals($response[0]->order_id[0], $orderId);
        $this->assertEquals($response[0]->product_uom_qty, 8);

    }

    public function testAuthenticationWithFixedUserId()
    {
        $fixedUserId = 1;
        $config = new Odoo\Config(
            $this->database,
            $this->host,
            'invalid-user',
            'invalid-pass',
            true,
            $fixedUserId
        );
        $odoo = new Odoo($config);
        $odoo->connect();
        $this->assertEquals($fixedUserId, $odoo->getUid());
    }

    public function testAuthenticationFallsBackToNormalWithNullFixedUserId()
    {
        $config = new Odoo\Config(
            $this->database,
            $this->host,
            $this->username,
            $this->password,
            true,
            null
        );
        $odoo = new Odoo($config);
        $odoo->connect();
        $this->assertIsInt($odoo->getUid());
        $this->assertGreaterThan(0, $odoo->getUid());
    }

    public function testAuthenticationFallsBackToNormalWithZeroFixedUserId()
    {
        $config = new Odoo\Config(
            $this->database,
            $this->host,
            $this->username,
            $this->password,
            true,
            0
        );
        $odoo = new Odoo($config);
        $odoo->connect();
        $this->assertIsInt($odoo->getUid());
        $this->assertGreaterThan(0, $odoo->getUid());
    }

    public function testAuthenticationFallsBackToNormalWithNegativeFixedUserId()
    {
        $config = new Odoo\Config(
            $this->database,
            $this->host,
            $this->username,
            $this->password,
            true,
            -5
        );
        $odoo = new Odoo($config);
        $odoo->connect();
        $this->assertIsInt($odoo->getUid());
        $this->assertGreaterThan(0, $odoo->getUid());
    }
}