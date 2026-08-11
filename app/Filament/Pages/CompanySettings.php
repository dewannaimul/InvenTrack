<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.company-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 99;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:CompanySettings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(CompanySetting::current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business profile')
                    ->description('Shown on invoices, receipts, and printed documents.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Business name')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('logo')
                            ->image()
                            ->directory('company')
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                        TextInput::make('phone'),
                        TextInput::make('email')->email(),
                        TextInput::make('tax_id')->label('Tax / VAT ID'),
                    ]),
                Section::make('Regional & defaults')
                    ->columns(3)
                    ->components([
                        TextInput::make('currency_code')
                            ->label('Currency code')
                            ->required()
                            ->maxLength(3)
                            ->helperText('e.g. USD, EUR, BDT'),
                        TextInput::make('currency_symbol')
                            ->label('Currency symbol')
                            ->required()
                            ->maxLength(5),
                        Select::make('default_warehouse_id')
                            ->label('Default POS warehouse')
                            ->options(fn () => Warehouse::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                    ]),
                Section::make('Document footers')
                    ->columns(1)
                    ->components([
                        Textarea::make('invoice_footer')
                            ->label('Invoice footer text')
                            ->helperText('Printed at the bottom of A4 invoices, e.g. payment terms.'),
                        Textarea::make('receipt_footer')
                            ->label('Receipt footer text')
                            ->helperText('Printed at the bottom of POS receipts, e.g. "Thank you for shopping with us!"'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        CompanySetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Company Settings';
    }

    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }
}
