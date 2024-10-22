<?php

namespace App\Filament\Imports;

use App\Models\Customers;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Session;

class CustomersImporter extends Importer
{
    protected static ?string $model = Customers::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('contact_no')->label('Contact Number'),
            // ImportColumn::make('attributes'),
        ];
    }

    public function resolveRecord(): ?Customers
    {
        // return Customers::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Customers();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customers import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function saveRecord(): void
    {
        $data = $this->data;
        unset($data['name']);
        unset($data['email']);
        unset($data['contact_no']);
        $this->record->attributes = json_encode($data); 
        $this->record->segment_id = Session::get('segment_id');
        $this->record->save();
    }
}
