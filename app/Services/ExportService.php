<?php

namespace App\Services;

class ExportService
{
    public function exportToDocx(string $content, string $filename, int $userId): string
    {
        $dir = storage_path("app/users/{$userId}/exports");
        $this->ensureDirectory($dir);

        $tempInput = storage_path("app/users/{$userId}/manuscripts/temp_{$filename}.md");
        $this->ensureDirectory(dirname($tempInput));
        $outputPath = "{$dir}/{$filename}.docx";

        file_put_contents($tempInput, $content);

        exec("pandoc \"{$tempInput}\" -o \"{$outputPath}\" --standalone", $out, $code);

        @unlink($tempInput);

        if ($code !== 0) {
            throw new \Exception(
                'Word export failed. Make sure Pandoc is installed. Download free at pandoc.org'
            );
        }

        return $outputPath;
    }

    public function exportToPdf(string $content, string $filename, int $userId): string
    {
        $dir = storage_path("app/users/{$userId}/exports");
        $this->ensureDirectory($dir);

        $tempInput = storage_path("app/users/{$userId}/manuscripts/temp_{$filename}.md");
        $this->ensureDirectory(dirname($tempInput));
        $outputPath = "{$dir}/{$filename}.pdf";

        file_put_contents($tempInput, $content);

        exec("pandoc \"{$tempInput}\" -o \"{$outputPath}\" --pdf-engine=xelatex", $out, $code);

        if ($code !== 0) {
            // Fallback without xelatex
            exec("pandoc \"{$tempInput}\" -o \"{$outputPath}\"", $out, $code);
        }

        @unlink($tempInput);

        if ($code !== 0) {
            throw new \Exception(
                'PDF export failed. Make sure Pandoc is installed. Download free at pandoc.org'
            );
        }

        return $outputPath;
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
