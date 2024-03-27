<?php

declare(strict_types=1);

namespace MoonShine\LogViewer\Drivers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MoonShine\LogViewer\Contracts\AbstractLogViewer;

/**
 * Class LogViewer.
 */
final class LogViewerWindows extends AbstractLogViewer
{
    /**
     * Начало и конец страницы
     */
    protected array $pageOffset = [];

    /**
     * Ссылка на предыдущую страницу
     */
    public function getPrevPageUrl(): bool|string
    {
        if ($this->pageOffset['end'] >= $this->getFilesize() - 1) {
            return false;
        }

        return route('moonshine.log.viewer.file', ['file' => $this->file])."?offset={$this->pageOffset['end']}";
    }

    /**
     * Ссылка на следующую страницу
     */
    public function getNextPageUrl(): bool|string
    {
        if ($this->pageOffset['start'] == 0) {
            return false;
        }

        return route('moonshine.log.viewer.file', ['file' => $this->file])."?offset=-{$this->pageOffset['start']}";
    }

    /**
     * Чтение журнала логов
     */
    protected function tail(int $seek = 0, int $lines = 20, int $buffer = 4096): array
    {
        if (! file_exists($this->filePath) || is_dir($this->filePath)) {
            return [collect(), 0, 0];
        }
        $f = fopen($this->filePath, 'rb');

        if ($seek) {
            fseek($f, abs($seek));
        } else {
            fseek($f, 0, SEEK_END);
        }

        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }
        fseek($f, -1, SEEK_CUR);

        // Start reading
        if ($seek > 0) {
            $output = '';

            $offsetStart = ftell($f);

            while (! feof($f) && $lines >= 0) {
                $output = $output.($chunk = fread($f, $buffer));
                $lines -= substr_count($chunk, "\n[20");
            }

            $offsetEnd = ftell($f);

            while ($lines++ < 0) {
                $strpos = strrpos($output, "\n[20") + 1;
                $_ = mb_strlen($output, '8bit') - $strpos;
                $output = substr($output, 0, $strpos);
                $offsetEnd -= $_;
            }
        } else {
            $output = '';

            $offsetEnd = ftell($f);

            while (ftell($f) > 0 && $lines >= 0) {
                $offset = min(ftell($f), $buffer);
                fseek($f, -$offset, SEEK_CUR);
                $output = ($chunk = fread($f, $offset)).$output;

                fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

                $lines -= substr_count($chunk, "\n[20");
            }

            $offsetStart = ftell($f);

            while ($lines++ < 0) {
                $strpos = strpos($output, "\n[20") + 1;
                $output = substr($output, $strpos);
                $offsetStart += $strpos;
            }
        }

        fclose($f);

        return [$this->parseLog($output), $offsetStart, $offsetEnd];
    }

    /**
     * Чтение журнала логов
     */
    public function fetch(
        int $seek = 0,
        int $lines = 20,
        array $level = [],
        ?string $env = null,
        ?Carbon $time_start = null,
        ?Carbon $time_end = null,
        ?string $info = null,
    ): Collection {
        $offset = isset($this->pageOffset['start']) ? -$this->pageOffset['start'] : $seek;

        /**
         * @var Collection $logs
         */
        [$logs, $offsetStart, $offsetEnd] = $this->tail($offset, $lines);

        $this->filter($logs, $level, $env, $time_start, $time_end, $info);

        $this->pageOffset['start'] = count($logs) ? $this->pageOffset['start'] ?? $offsetStart : $offsetStart;
        $this->pageOffset['end'] ??= $offsetEnd;

        return $logs->count() >= $lines
        || $offsetStart >= $this->getFilesize() - 1
        || $offsetStart == $seek
        || $offsetStart == 0
            ? $logs
            : $logs->merge($this->fetch($seek, $lines, $level, $env, $time_start, $time_end, $info))->slice(0, $lines);
    }
}
