<?php

namespace App\Actions\StockLog;

use App\Enums\StockLogExportFormat;
use App\Exports\StockLogExport;
use App\Models\StockLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportStockLog
{
    public function execute(StockLogExportFormat $format, ?string $from, ?string $to): BinaryFileResponse
    {
        $fileName = $this->generateFileName($format, $from, $to);
        $stockLogs = $this->getStockLogs($from, $to);

        return Excel::download(
            new StockLogExport($stockLogs),
            $fileName,
            $format->writerType(),
            [
                'Content-Type' => $format->contentType(),
            ],
        );
    }

    private function generateFileName(StockLogExportFormat $format, ?string $from, ?string $to): string
    {
        $dateSlug = match (true) {
            $from && $to => "from_{$from}_to_{$to}",
            $from => "from_{$from}",
            $to => "until_{$to}",
            default => 'all',
        };

        return "stock-logs-{$dateSlug}.{$format->extension()}";
    }

    private function getStockLogs(?string $from, ?string $to): Collection
    {
        return StockLog::with(['product', 'user'])
            ->when($from, function ($query) use ($from) {
                return $query->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                return $query->whereDate('created_at', '<=', $to);
            })
            ->get();
    }
}
