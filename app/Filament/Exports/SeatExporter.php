<?php

namespace App\Filament\Exports;

use App\Models\Seat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class SeatExporter extends Exporter
{
    protected static ?string $model = Seat::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('seat_no'),
            ExportColumn::make('status'),
            ExportColumn::make('note')->formatStateUsing(fn ($state) => $state ?? '-'),
            ExportColumn::make('created_at')
                ->label('Created On')
                ->formatStateUsing(fn ($state) => date('d-m-Y | h:i A')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your seat export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::BLUE);
    }

    public function getFileName(Export $export): string
    {
        return 'seats-'.time();
    }
}
