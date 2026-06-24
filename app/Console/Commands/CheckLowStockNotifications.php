<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockBalance;
use App\Notifications\LowStockNotification;
use App\Services\NotificationRecipientService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckLowStockNotifications extends Command
{
    protected $signature = 'notifications:low-stock';

    protected $description = 'Notify inventory stakeholders about products at or below reorder level';

    public function handle(NotificationRecipientService $recipients): int
    {
        $aggregated = StockBalance::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity_on_hand) as total_on_hand')
            ->groupBy('product_id');

        $products = Product::query()
            ->joinSub($aggregated, 'agg', fn ($join) => $join->on('products.id', '=', 'agg.product_id'))
            ->where('products.reorder_level', '>', 0)
            ->whereColumn('agg.total_on_hand', '<=', 'products.reorder_level')
            ->where('agg.total_on_hand', '>', 0)
            ->select('products.*', DB::raw('agg.total_on_hand as total_on_hand'))
            ->get();

        if ($products->isEmpty()) {
            $this->info('No low stock products found.');

            return self::SUCCESS;
        }

        $users = $recipients->inventoryStakeholders();
        $sent = 0;

        foreach ($products as $product) {
            $onHand = (int) floor((float) $product->total_on_hand);

            foreach ($users as $user) {
                $alreadyNotified = $user->notifications()
                    ->where('type', LowStockNotification::class)
                    ->where('data->product_id', $product->id)
                    ->where('created_at', '>=', now()->subDay())
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                $user->notify(new LowStockNotification($product, $onHand, (int) $product->reorder_level));
                $sent++;
            }
        }

        $this->info("Sent {$sent} low stock notification(s) for {$products->count()} product(s).");

        return self::SUCCESS;
    }
}
