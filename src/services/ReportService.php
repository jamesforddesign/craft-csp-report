<?php

namespace jfd\craftcspreport\services;

use Craft;

class ReportService
{
    private string $path;

    public function __construct()
    {
        $this->path = Craft::$app->getPath()->getStoragePath() . '/csp-report.json';
    }

    public function read(): array
    {
        if (!file_exists($this->path)) {
            return $this->blank();
        }

        $data = json_decode(file_get_contents($this->path), true);

        return is_array($data) && isset($data['summary']) ? $data : $this->blank();
    }

    public function record(string $directive, string $source, string $blockedUri, string $documentUri): void
    {
        $fh = fopen($this->path, 'c+');

        if ($fh === false) {
            Craft::warning("csp-report: could not open {$this->path}", __METHOD__);
            return;
        }

        flock($fh, LOCK_EX);

        clearstatcache(true, $this->path);
        $size = filesize($this->path);
        $json = $size ? fread($fh, $size) : '';
        $data = json_decode($json, true);

        if (!is_array($data) || !isset($data['summary'])) {
            $data = $this->blank();
        }

        $data['totalReports']++;

        // Find or create the bucket for this directive + source pair
        $found = false;

        foreach ($data['summary'] as &$row) {
            if ($row['directive'] === $directive && $row['source'] === $source) {
                $row['count']++;
                $found = true;
                break;
            }
        }

        unset($row);

        if (!$found) {
            $data['summary'][] = [
                'directive' => $directive,
                'source' => $source,
                'count' => 1,
                'exampleBlockedUri' => $blockedUri,
                'exampleDocumentUri' => $documentUri,
            ];
        }

        // Sort: highest count first, then directive, then source
        usort($data['summary'], fn(array $a, array $b) =>
            $b['count'] <=> $a['count']
            ?: strcmp($a['directive'], $b['directive'])
            ?: strcmp($a['source'], $b['source'])
        );

        $data['generatedAt'] = gmdate('Y-m-d\TH:i:s.v\Z');
        $data['uniqueDirectiveSourcePairs'] = count($data['summary']);

        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    private function blank(): array
    {
        return [
            'generatedAt' => gmdate('Y-m-d\TH:i:s.v\Z'),
            'totalReports' => 0,
            'uniqueDirectiveSourcePairs' => 0,
            'summary' => [],
        ];
    }
}
