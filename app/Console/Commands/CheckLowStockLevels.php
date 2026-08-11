<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-low-stock-levels')]
#[Description('Notify admins and managers about products at or below their reorder level')]
class CheckLowStockLevels extends Command
{
    public function handle(): void
    {
        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->withSum('stocks as stock_quantity', 'quantity')
            ->get()
            ->filter(fn (Product $product) => (int) ($product->stock_quantity ?? 0) <= $product->reorder_level);

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low stock products found.');

            return;
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'manager']))
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('No admin/manager recipients found for low stock notification.');

            return;
        }

        $body = $lowStockProducts
            ->map(fn (Product $product) => "{$product->name} ({$product->stock_quantity} left, reorder at {$product->reorder_level})")
            ->implode("\n");

        Notification::make()
            ->title("{$lowStockProducts->count()} product(s) are low on stock")
            ->body($body)
            ->warning()
            ->sendToDatabase($recipients);

        $this->info("Notified {$recipients->count()} user(s) about {$lowStockProducts->count()} low stock product(s).");
    }
}
