<?php

namespace App\Services;

class PdfCompressor
{
    /**
     * Compress a PDF with Ghostscript /ebook preset (150 DPI images).
     * Returns path to compressed file, or null if gs unavailable / compression failed / output larger.
     * Caller is responsible for unlinking the returned temp path after use.
     */
    public static function compress(string $inputPath): ?string
    {
        $gs = trim(shell_exec('which gs 2>/dev/null') ?? '');
        if (! $gs || ! is_executable($gs)) {
            return null;
        }

        $output = tempnam(sys_get_temp_dir(), 'pdf_compressed_') . '.pdf';

        $cmd = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook'
            . ' -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
            escapeshellarg($gs),
            escapeshellarg($output),
            escapeshellarg($inputPath)
        );

        exec($cmd, $lines, $exitCode);

        if ($exitCode !== 0 || ! file_exists($output) || filesize($output) === 0) {
            @unlink($output);
            return null;
        }

        // Only use compressed version if it's actually smaller
        if (filesize($output) >= filesize($inputPath)) {
            @unlink($output);
            return null;
        }

        return $output;
    }
}
