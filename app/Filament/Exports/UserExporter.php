<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\TextInput;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone_no')->label('Phone No.'),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => match ($state) {
                1 => 'Active',
                0 => 'Deactive'
            }),
            ExportColumn::make('subscription_count')->counts('subscription'),
            ExportColumn::make('created_at')->label('Created on'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
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
        return 'users-' . time();
    }
}
