<?php

namespace App\Filament\Resources\PurchaseOrders\Actions;

use App\Mail\InvoiceMail;
use App\Models\PurchaseOrder;
use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class EmailInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('emailInvoice')
            ->label('Email PDF')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->schema([
                TextInput::make('email')
                    ->label('Send to')
                    ->email()
                    ->required()
                    ->default(fn (PurchaseOrder $record) => $record->supplier->email),
                Textarea::make('message')
                    ->label('Message')
                    ->default(fn (PurchaseOrder $record) => "Hi {$record->supplier->name}, please find attached purchase order {$record->po_number}."),
            ])
            ->action(function (PurchaseOrder $record, array $data) {
                $pdf = app(InvoiceService::class)->purchaseInvoicePdf($record);

                Mail::to($data['email'])->send(new InvoiceMail(
                    subjectLine: "Purchase Order {$record->po_number}",
                    bodyMessage: $data['message'],
                    pdfContent: $pdf->output(),
                    pdfFilename: "po-{$record->po_number}.pdf",
                ));

                Notification::make()
                    ->title("PDF emailed to {$data['email']}")
                    ->success()
                    ->send();
            });
    }
}
