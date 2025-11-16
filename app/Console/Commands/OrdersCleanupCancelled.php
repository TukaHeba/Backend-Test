<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrdersCleanupCancelled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cleanup-cancelled {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes old cancelled orders (more than 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = now()->subDays(30);

        // Count orders that will be deleted
        $ordersToDelete = Order::cancelled()->olderThan($days)->count();

        if ($ordersToDelete === 0) {
            $this->info('No cancelled orders found older than 30 days.');

            return Command::SUCCESS;
        }

        $this->info("Found {$ordersToDelete} cancelled order(s) older than 30 days.");

        if (! $this->option('force') && ! $this->confirm('Do you want to delete these orders?', true)) {
            $this->info('Cleanup cancelled.');

            return Command::SUCCESS;
        }

        $deletedCount = DB::transaction(function () use ($days) {
            return Order::cancelled()->olderThan($days)->delete();
        });

        $this->info("Successfully deleted {$deletedCount} cancelled order(s).");

        return Command::SUCCESS;
    }
}
