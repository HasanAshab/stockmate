<?php

namespace App\Enums;

use Maatwebsite\Excel\Excel as ExcelWriter;

enum StockLogExportFormat: string
{
    public const string CSV_CONTENT_TYPE = 'text/csv';
    public const string EXCEL_CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

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
            self::Csv => self::CSV_CONTENT_TYPE,
            self::Excel => self::EXCEL_CONTENT_TYPE,
        };
    }

    public function writerType(): string
    {
        return match ($this) {
            self::Csv => ExcelWriter::CSV,
            self::Excel => ExcelWriter::XLSX,
        };
    }
}