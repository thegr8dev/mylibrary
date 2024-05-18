<?php

namespace App\Filament\Exports;

use App\Models\Subscription;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class SubscriptionExporter extends Exporter
{
    protected static ?string $model = Subscription::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('uuid')
                ->label('Subscription ID'),
            ExportColumn::make('subscriber.name')->formatStateUsing(fn ($state) => ucwords($state)),
            ExportColumn::make('seat.seat_no')->formatStateUsing(fn ($state) => config('seatprefix.pre').$state),
            ExportColumn::make('start_date')->formatStateUsing(fn ($state) => date('d-m-Y', strtotime($state))),
            ExportColumn::make('end_date')->formatStateUsing(fn ($state) => date('d-m-Y', strtotime($state))),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => match ($state) {
                1 => 'Active',
                0 => 'Deactive'
            }),
            ExportColumn::make('txn_id'),
            ExportColumn::make('amount'),
            ExportColumn::make('payment_method')->formatStateUsing(fn ($state) => ucwords($state)),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => ucwords($state)),
            ExportColumn::make('note')->formatStateUsing(fn ($state) => $state ?? '-'),
            ExportColumn::make('created_at')
                ->label('Created On')
                ->formatStateUsing(fn ($state) => date('d-m-Y | h:i A', strtotime($state))),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your subscription export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        return 'subscriptions-'.time();
    }
}
