<?php

namespace App\Enums;

enum StockLogExportFormat: string
{
    case Csv = 'csv';
    case Excel = 'excel';

    public function extension(): string
    {
        return match ($this) {
            self::Csv => 'csv',
            self::Excel => 'xlsx',
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::Csv => 'text/csv',
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }
}
