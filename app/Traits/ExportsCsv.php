<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Trait for exporting data to CSV format
 *
 * This trait provides a reusable method for streaming CSV exports
 * from Eloquent queries to avoid memory issues with large datasets.
 */
trait ExportsCsv
{
    /**
     * Export query results to CSV using streaming for memory efficiency
     *
     * @param  Builder  $query  The Eloquent query to export
     * @param  array  $headers  Column headers for the CSV file
     * @param  callable  $rowMapper  Function to map each row to an array of values
     * @param  string  $filenamePrefix  Prefix for the generated filename
     * @param  int  $chunkSize  Number of rows to process at once (default: 500)
     */
    protected function exportToCsv(
        Builder $query,
        array $headers,
        callable $rowMapper,
        string $filenamePrefix,
        int $chunkSize = 500
    ): StreamedResponse {
        $filename = $filenamePrefix.'_'.now()->format('Ymd_His').'.csv';

        $callback = function () use ($query, $headers, $rowMapper, $chunkSize) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $query->chunk($chunkSize, function ($rows) use ($handle, $rowMapper) {
                foreach ($rows as $row) {
                    fputcsv($handle, $rowMapper($row));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
