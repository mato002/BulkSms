<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    /**
     * @param  callable(): iterable  $rowGenerator
     */
    public static function download(string $filename, array $headers, callable $rowGenerator): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rowGenerator) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($rowGenerator() as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
