<?php

namespace Tests\Unit\Services\Procurement;

use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationSeries;
use App\Services\Procurement\ImportOrderCalculator;
use App\Services\Procurement\LocalOrderCalculator;
use Tests\TestCase;

class OrderCalculatorTest extends TestCase
{
    public function test_local_calculator_matches_screenshot_row_one(): void
    {
        $product = new Product(['min_selling_price' => 200]);
        $item = new QuotationItem([
            'quantity' => 22,
            'unit_price' => 160,
            'transport' => 0,
        ]);

        $calculated = (new LocalOrderCalculator)->calculateLine($item, $product);

        $this->assertEquals(3520.00, (float) $calculated->total_purchase_price);
        $this->assertEquals(160.00, (float) $calculated->unit_cost_arrival);
        $this->assertEquals(40.00, (float) $calculated->margin_amount);
        $this->assertEquals(20.00, (float) $calculated->margin_percent);
        $this->assertEquals(4400.00, (float) $calculated->expected_sales);
        $this->assertEquals(880.00, (float) $calculated->expected_margin);
    }

    public function test_local_calculator_with_transport(): void
    {
        $product = new Product(['min_selling_price' => 350]);
        $item = new QuotationItem([
            'quantity' => 5,
            'unit_price' => 300,
            'transport' => 50,
        ]);

        $calculated = (new LocalOrderCalculator)->calculateLine($item, $product);

        $this->assertEquals(1550.00, (float) $calculated->actual_total_cost);
        $this->assertEquals(310.00, (float) $calculated->unit_cost_arrival);
        $this->assertEquals(40.00, (float) $calculated->margin_amount);
    }

    public function test_import_calculator_derives_packets_from_quantity(): void
    {
        $this->assertEquals(70.0, ImportOrderCalculator::deriveNumberOfPackets(70, 1));
        $this->assertEquals(300.0, ImportOrderCalculator::deriveNumberOfPackets(300, 1));
        $this->assertEquals(12.5, ImportOrderCalculator::deriveNumberOfPackets(100, 8));
    }

    public function test_import_calculator_uses_market_wholesale_override(): void
    {
        $series = new QuotationSeries([
            'exchange_rate' => 31.5,
            'cbm_rate' => 55033,
        ]);

        $product = new Product(['min_selling_price' => 750]);
        $item = new QuotationItem([
            'quantity' => 300,
            'unit_price_foreign' => 16.5375,
            'width' => 0.07,
            'length' => 0.17,
            'height' => 0.09,
            'quantity_per_packet' => 1,
            'number_of_packets' => 300,
            'market_wholesale_price' => 800,
        ]);

        $calculated = (new ImportOrderCalculator)->calculateLine($item, $product, $series);

        $this->assertEquals(800.00, (float) $calculated->market_wholesale_price);
        $this->assertEqualsWithDelta(220.16, (float) $calculated->margin_amount, 0.05);
    }

    public function test_import_calculator_matches_screenshot_row_one(): void
    {
        $series = new QuotationSeries([
            'exchange_rate' => 31.5,
            'cbm_rate' => 55033,
        ]);

        $product = new Product(['min_selling_price' => 750]);
        $item = new QuotationItem([
            'quantity' => 300,
            'unit_price_foreign' => 16.5375,
            'width' => 0.07,
            'length' => 0.17,
            'height' => 0.09,
            'quantity_per_packet' => 1,
            'number_of_packets' => 300,
        ]);

        $calculated = (new ImportOrderCalculator)->calculateLine($item, $product, $series);

        $this->assertEqualsWithDelta(520.93, (float) $calculated->unit_price_ksh, 0.02);
        $this->assertEqualsWithDelta(0.32, (float) $calculated->total_cbm, 0.01);
        $this->assertEqualsWithDelta(58.91, (float) $calculated->transport_per_unit, 0.05);
        $this->assertEqualsWithDelta(579.84, (float) $calculated->unit_cost_arrival, 0.05);
        $this->assertEqualsWithDelta(170.16, (float) $calculated->margin_amount, 0.05);
    }
}
