<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\Actions\EmailInvoiceAction;
use App\Filament\Resources\PurchaseOrders\Actions\ReceiveStockAction;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReceiveStockAction::make(),
            Action::make('downloadInvoice')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn ($record) => route('documents.purchase-invoice', $record))
                ->openUrlInNewTab(),
            EmailInvoiceAction::make(),
            EditAction::make(),
        ];
    }
}
