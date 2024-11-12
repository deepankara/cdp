<?php

namespace App\Filament\Exports;

use App\Models\EmailDump;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmailDumpExporter extends Exporter
{
    protected static ?string $model = EmailDump::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('email'),
            ExportColumn::make('status'),
            ExportColumn::make('reason'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your email dump export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}