<?php

namespace App\Filament\Resources\SalesOrders\Actions;

use App\Mail\InvoiceMail;
use App\Models\SalesOrder;
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
            ->label('Email invoice')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->schema([
                TextInput::make('email')
                    ->label('Send to')
                    ->email()
                    ->required()
                    ->default(fn (SalesOrder $record) => $record->customer->email),
                Textarea::make('message')
                    ->label('Message')
                    ->default(fn (SalesOrder $record) => "Hi {$record->customer->name}, please find attached the invoice for order {$record->order_number}."),
            ])
            ->action(function (SalesOrder $record, array $data) {
                $pdf = app(InvoiceService::class)->salesInvoicePdf($record);

                Mail::to($data['email'])->send(new InvoiceMail(
                    subjectLine: "Invoice {$record->order_number}",
                    bodyMessage: $data['message'],
                    pdfContent: $pdf->output(),
                    pdfFilename: "invoice-{$record->order_number}.pdf",
                ));

                Notification::make()
                    ->title("Invoice emailed to {$data['email']}")
                    ->success()
                    ->send();
            });
    }
}
