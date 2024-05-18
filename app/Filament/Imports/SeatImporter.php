<?php

namespace App\Filament\Imports;

use App\Models\Seat;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;

class SeatImporter extends Importer
{
    protected static ?string $model = Seat::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('seat_no')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')->fillRecordUsing(function (Seat $seat, $state) {
                if (is_null($state)) {
                    $seat->status = 1;
                } else {
                    $seat->status = $state;
                }
            }),
            ImportColumn::make('note')->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Seat
    {
        if ($this->options['updateExisting'] ?? false) {
            return Seat::firstOrNew([
                'seat_no' => $this->data['seat_no'],
            ]);
        }

        if (! $this->options['updateExisting']) {
            $seat = Seat::where('seat_no', $this->data['seat_no'])->first();

            if (! $seat) {
                return new Seat();
            }

            throw new RowImportFailedException("Seat [{$this->data['seat_no']}] already exists, skipping it.");
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your seat import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing records'),
        ];
    }
}
