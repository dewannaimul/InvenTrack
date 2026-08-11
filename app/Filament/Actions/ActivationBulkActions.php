<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class ActivationBulkActions
{
    /**
     * @return array<BulkAction>
     */
    public static function make(): array
    {
        return [
            BulkAction::make('activateSelected')
                ->label('Activate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('deactivateSelected')
                ->label('Deactivate')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                ->deselectRecordsAfterCompletion(),
        ];
    }
}
