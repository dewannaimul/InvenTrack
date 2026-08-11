<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class ExportCsvBulkAction
{
    /**
     * @param  array<string, callable|string>  $columns  Map of CSV header => accessor (dot-path string or callable($record))
     */
    public static function make(string $filenamePrefix, array $columns): BulkAction
    {
        return BulkAction::make('exportCsv')
            ->label('Export CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function (Collection $records) use ($filenamePrefix, $columns) {
                return response()->streamDownload(function () use ($records, $columns) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, array_keys($columns));

                    foreach ($records as $record) {
                        $row = array_map(
                            fn ($accessor) => is_callable($accessor) ? $accessor($record) : data_get($record, $accessor),
                            array_values($columns)
                        );
                        fputcsv($out, $row);
                    }

                    fclose($out);
                }, $filenamePrefix.'-'.now()->format('Ymd-His').'.csv');
            })
            ->deselectRecordsAfterCompletion();
    }
}
