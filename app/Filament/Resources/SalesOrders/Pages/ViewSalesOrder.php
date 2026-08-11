<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\Actions\CancelOrderAction;
use App\Filament\Resources\SalesOrders\Actions\ConfirmOrderAction;
use App\Filament\Resources\SalesOrders\Actions\EmailInvoiceAction;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\SalesOrder;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ConfirmOrderAction::make(),
            CancelOrderAction::make(),
            Action::make('printReceipt')
                ->label('Print receipt')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn (SalesOrder $record) => $record->source === SalesOrder::SOURCE_POS)
                ->url(fn ($record) => route('documents.pos.receipt', $record))
                ->openUrlInNewTab(),
            Action::make('downloadInvoice')
                ->label('Download invoice')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn ($record) => route('documents.sales-invoice', $record))
                ->openUrlInNewTab(),
            EmailInvoiceAction::make(),
            EditAction::make(),
        ];
    }
}
