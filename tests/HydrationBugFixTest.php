<?php

namespace Obuchmann\OdooJsonRpc\Tests;

use Obuchmann\OdooJsonRpc\Attributes\BelongsTo;
use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\HasMany;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\Mapping\LazyHasMany;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;
use stdClass;

class HydrationBugFixTest extends TestCase
{
    public function testForeignKeyFieldDehydration()
    {
        // Create a test model that simulates the bug scenario
        $testModel = new class extends OdooModel {
            #[Field, Key]
            public int|array $journal_id;
            
            #[Field('partner_id'), Key]
            public int $partner_id;
            
            #[Field]
            public ?string $name = null;
            
            #[HasMany(TestAccountMoveLine::class, 'line_ids')]
            public array|\ArrayAccess $lines;
        };
        
        // Set up test data that mimics the bug scenario
        $testModel->journal_id = [1, "Customer invoices"]; // Array format from Odoo
        $testModel->partner_id = 683;
        $testModel->name = "Test Invoice";
        
        // Create a LazyHasMany that hasn't been loaded (simulating unmodified relationships)
        $testModel->lines = new LazyHasMany(TestAccountMoveLine::class, 'read', [[1, 2, 3]]);
        
        // Dehydrate the model
        $dehydrated = $testModel->dehydrate($testModel);
        
        // Assert that journal_id is converted to integer (not array)
        $this->assertEquals(1, $dehydrated->journal_id);
        $this->assertIsInt($dehydrated->journal_id);
        
        // Assert that partner_id remains as integer
        $this->assertEquals(683, $dehydrated->partner_id);
        $this->assertIsInt($dehydrated->partner_id);
        
        // Assert that name is preserved
        $this->assertEquals("Test Invoice", $dehydrated->name);
        
        // Assert that unloaded LazyHasMany relationships are NOT included in dehydration
        $this->assertFalse(property_exists($dehydrated, 'line_ids'));
    }
    
    public function testLoadedRelationshipDehydration()
    {
        // Create a test model with loaded relationships
        $testModel = new class extends OdooModel {
            #[Field, Key]
            public int|array $journal_id;
            
            #[HasMany(TestAccountMoveLine::class, 'line_ids')]
            public array|\ArrayAccess $lines;
        };
        
        // Create test line items with foreign key arrays
        $line1 = new TestAccountMoveLine();
        $line1->id = 1;
        $line1->account_id = [316, "800100 Omzet NL handelsgoederen 1"];
        $line1->partner_id = [683, "Test Partner"];
        $line1->name = "Test Product 1";
        
        $line2 = new TestAccountMoveLine();
        $line2->id = 2;
        $line2->account_id = [73, "110000 Debiteuren"];
        $line2->partner_id = [683, "Test Partner"];
        $line2->name = "Test Product 2";
        
        $testModel->journal_id = [1, "Customer invoices"];
        $testModel->lines = [$line1, $line2];
        
        // Dehydrate the model
        $dehydrated = $testModel->dehydrate($testModel);
        
        // Assert that journal_id is converted to integer
        $this->assertEquals(1, $dehydrated->journal_id);
        
        // Assert that line_ids contains proper update commands
        $this->assertIsArray($dehydrated->line_ids);
        $this->assertCount(2, $dehydrated->line_ids);
        
        // Check that each line command has proper structure [1, id, data]
        foreach ($dehydrated->line_ids as $command) {
            $this->assertIsArray($command);
            $this->assertCount(3, $command);
            $this->assertEquals(1, $command[0]); // Update command
            $this->assertIsInt($command[1]); // Line ID
            $this->assertIsObject($command[2]); // Line data
            
            // Assert that foreign key fields in the line data are integers
            $lineData = $command[2];
            $this->assertIsInt($lineData->account_id);
            $this->assertIsInt($lineData->partner_id);
        }
    }
    
    public function testEmptyRelationshipDehydration()
    {
        $testModel = new class extends OdooModel {
            #[HasMany(TestAccountMoveLine::class, 'line_ids')]
            public ?array $lines = null;
        };
        
        $testModel->lines = null;
        
        $dehydrated = $testModel->dehydrate($testModel);
        
        // Assert that null relationships are not included
        $this->assertFalse(property_exists($dehydrated, 'line_ids'));
    }
}

// Test model for the line items
#[Model('account.move.line')]
class TestAccountMoveLine extends OdooModel
{
    #[Field, Key]
    public int|array $account_id;

    #[Field, Key]
    public int|array $partner_id;

    #[Field]
    public string $name;
}