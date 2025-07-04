<?php


namespace Obuchmann\OdooJsonRpc\Tests;


use Obuchmann\OdooJsonRpc\Odoo;
use Obuchmann\OdooJsonRpc\Odoo\Casts\CastHandler;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;
use Obuchmann\OdooJsonRpc\Tests\Models\Partner;
use Obuchmann\OdooJsonRpc\Tests\Models\Product;
use Obuchmann\OdooJsonRpc\Tests\Models\PurchaseOrder;
use Obuchmann\OdooJsonRpc\Tests\Models\PurchaseOrderLine;
use Obuchmann\OdooJsonRpc\Tests\Models\StockPicking;

class ModelTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        OdooModel::boot($this->odoo);
    }

    public function testFields()
    {
        $fields = Partner::listFields();

        $this->assertObjectHasAttribute('name', $fields);
    }

    public function testFind()
    {
        $partner = Partner::find(1);

        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertNotNull($partner->name);
    }

    public function testQuery()
    {
        $partner = new Partner();
        $partner->name = 'Azure Interior';
        $partner->save();

        $partner = Partner::query()
            ->where('name', '=', 'Azure Interior')
            ->first();

        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertEquals('Azure Interior', $partner->name);
    }

    public function testCreate()
    {
        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->save();


        $this->assertNotNull($partner->id);
    }

    public function testReadonlyCreate()
    {
        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->childIds = [1,2,3];
        $partner->save();


        $this->assertNotNull($partner->id);
    }

    public function testUpdate()
    {
        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->save();


        $this->assertNotNull($partner->id);

        $partner->name = "Tester2";
        $partner->save();

        $check = Partner::find($partner->id);

        $this->assertEquals("Tester2", $check->name);
    }

    public function testUpdateNullValue()
    {
        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->email = "tester@example.org";
        $partner->save();


        $this->assertNotNull($partner->id);
        $this->assertNotNull($partner->email);

        $partner->name = "Tester2";
        $partner->email = null;
        $partner->save();

        $check = Partner::find($partner->id);

        $this->assertEquals("Tester2", $check->name);
        $this->assertEquals(null, $check->email);
    }

    public function testSelectColumns()
    {
        $items = Partner::query()->limit(5)
            ->fields(['display_name'])->get();

        $this->assertCount(5, $items);
        $this->assertFalse(isset($items[0]->name));
    }

    public function testOrderBy()
    {
        $items = Partner::query()->limit(5)
            ->orderBy('id', 'desc')
            ->fields(['name'])->get();

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertGreaterThan($items[1]->id, $items[0]->id);
    }


    public function testBelongsTo()
    {

        $parent = new Partner();
        $parent->name = 'Parent';
        $parent->save();

        $child = new Partner();
        $child->parentId = $parent->id;

        $this->assertInstanceOf(Partner::class, $child->parent());
        $this->assertEquals($parent->id, $child->parent()->id);

    }

    public function testHasManyCreate()
    {

        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->save();

        $product = new Product();
        $product->name = "Tester2";
        $product->save();

        $line = new PurchaseOrderLine();
        $line->name = 'Test';
        $line->productId = $product->id;
        $line->priceUnit = 10;
        $line->productQuantity = 1;

        $order = new PurchaseOrder();
        $order->partnerId = $partner->id;
        $order->lines = [$line];
        $order->save();


        $this->assertNotNull($order->id);
    }


    public function testCast()
    {
        CastHandler::reset();
        Odoo::registerCast(new Odoo\Casts\DateTimeCast());

        $item = PurchaseOrder::query()->first();

        $this->assertNotNull($item->orderDate);
        $this->assertInstanceOf(\DateTime::class, $item->orderDate);

    }

    public function testDateTimezoneCast()
    {
        CastHandler::reset();
        Odoo::registerCast(new Odoo\Casts\DateTimeTimezoneCast(new \DateTimeZone('Europe/Vienna')));

        $item2 = PurchaseOrder::query()->first();

        $this->assertNotNull($item2->orderDate);
        $this->assertInstanceOf(\DateTime::class, $item2->orderDate);

        $this->assertEquals("Europe/Vienna", $item2->orderDate->getTimezone()->getName());

    }


    public function testNullableCast()
    {
        CastHandler::reset();
        Odoo::registerCast(new Odoo\Casts\DateTimeCast());

        $item = PurchaseOrder::query()->first();

        $this->assertNull($item->approveDate);

    }


    public function testFill()
    {
        $partner = new Partner();
        $partner->fill([
            'name' => 'test'
        ]);

        $this->assertEquals('test', $partner->name);

    }

    public function testEquals()
    {
        $partner = new Partner();
        $partner->name = "test";

        $partner2 = new Partner();
        $partner2->name = "test";

        $partner3 = new Partner();
        $partner3->name = "test";
        $partner3->email = "test";

        $partner4 = new Partner();
        $partner4->name = "test2";

        $partner5 = clone $partner;

        $partner6 = clone $partner;
        $partner6->name = "some";

        $this->assertTrue($partner->equals($partner2));
        $this->assertFalse($partner->equals($partner3));
        $this->assertFalse($partner->equals($partner4));
        $this->assertTrue($partner->equals($partner5));
        $this->assertFalse($partner->equals($partner6));
    }

    public function testHasManyRelationHydration()
    {

        \Obuchmann\OdooJsonRpc\Odoo::registerCast(new Odoo\Casts\DateTimeCast());

        // 1. Set up a Partner for the PurchaseOrder
        $testPartner = new Partner();
        $testPartner->name = 'Test Partner for PO';
        $testPartner->save();
        $this->assertNotNull($testPartner->id, "Failed to create test partner.");

        // 2. Set up a Product for PurchaseOrderLines
        $testProduct = new Product();
        $testProduct->name = 'Test Product for POLine';
        $testProduct->save();
        $this->assertNotNull($testProduct->id, "Failed to create test product.");

        // 3. Create PurchaseOrder
        $order = new PurchaseOrder();
        $order->partnerId = $testPartner->id;
        // Minimal required fields for PO, assuming orderDate might be set by Odoo
        $order->save();
        $this->assertNotNull($order->id, "Failed to create purchase order.");

        // 4. Create PurchaseOrderLine instances
        $line1 = new PurchaseOrderLine();
        $line1->orderId = $order->id;
        $line1->name = 'Line 1';
        $line1->productId = $testProduct->id;
        $line1->productQuantity = 2;
        $line1->priceUnit = 10.0;
        $line1->save();
        $this->assertNotNull($line1->id, "Failed to create purchase order line 1.");

        $line2 = new PurchaseOrderLine();
        $line2->orderId = $order->id;
        $line2->name = 'Line 2';
        $line2->productId = $testProduct->id;
        $line2->productQuantity = 5;
        $line2->priceUnit = 20.0;
        $line2->save();
        $this->assertNotNull($line2->id, "Failed to create purchase order line 2.");

        // 5. Fetch the PurchaseOrder
        /** @var PurchaseOrder $fetchedOrder */
        $fetchedOrder = PurchaseOrder::find($order->id);
        $this->assertNotNull($fetchedOrder, "Failed to fetch purchase order.");

        // 6. Assertions for the 'lines' property (assuming 'lines' is the property name for HasMany PurchaseOrderLine)
        // The actual property name for lines in PurchaseOrder model is 'lines'
        #$this->assertIsArrayAccess($fetchedOrder->lines, "Order lines property should be an array.");
        $this->assertCount(2, $fetchedOrder->lines, "Should have 2 order lines.");

        foreach ($fetchedOrder->lines as $fetchedLine) {
            $this->assertInstanceOf(PurchaseOrderLine::class, $fetchedLine, "Each line should be an instance of PurchaseOrderLine.");
            $this->assertNotNull($fetchedLine->id, "Fetched line should have an ID.");
            $this->assertNotNull($fetchedLine->name, "Fetched line should have a name.");
            $this->assertTrue(in_array($fetchedLine->id, [$line1->id, $line2->id]));
            if ($fetchedLine->id === $line1->id) {
                $this->assertEquals($line1->productQuantity, $fetchedLine->productQuantity);
                $this->assertEquals($line1->priceUnit, $fetchedLine->priceUnit);
            } elseif ($fetchedLine->id === $line2->id) {
                $this->assertEquals($line2->productQuantity, $fetchedLine->productQuantity);
                $this->assertEquals($line2->priceUnit, $fetchedLine->priceUnit);
            }
        }
    }

    public function testHasManyRelationEmptyHydration()
    {
        \Obuchmann\OdooJsonRpc\Odoo::registerCast(new Odoo\Casts\DateTimeCast());

        // 1. Set up a Partner for the PurchaseOrder
        $testPartner = new Partner();
        $testPartner->name = 'Test Partner for Empty PO';
        $testPartner->save();
        $this->assertNotNull($testPartner->id, "Failed to create test partner.");

        // 2. Create PurchaseOrder without lines
        $order = new PurchaseOrder();
        $order->partnerId = $testPartner->id;
        $order->save();
        $this->assertNotNull($order->id, "Failed to create purchase order.");

        // 3. Fetch the PurchaseOrder
        /** @var PurchaseOrder $fetchedOrder */
        $fetchedOrder = PurchaseOrder::find($order->id);
        $this->assertNotNull($fetchedOrder, "Failed to fetch purchase order.");

        // 4. Assert that the 'orderLines' property is an empty array
        // Assuming 'orderLines' is the correct property name in PurchaseOrder model for HasMany relation
        $this->assertIsArray($fetchedOrder->lines, "Order lines should be an array even if empty.");
        $this->assertEmpty($fetchedOrder->lines, "Order lines property should be an empty array.");
    }

}