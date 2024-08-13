<?php

declare(strict_types=1);

namespace YuriZoom\MoonShineLogViewer\Drivers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use YuriZoom\MoonShineLogViewer\Contracts\AbstractLogViewer;

/**
 * Class LogViewer.
 */
final class LogViewerLinux extends AbstractLogViewer
{
    /**
     * Начало и конец страницы
     */
    protected array $pageOffset = [];

    /**
     * Количество логов в файле
     */
    protected ?int $countLogs = null;

    /**
     * Количество строк в файле
     */
    protected ?int $countLines = null;

    /**
     * Минимальная дата логов
     */
    protected ?Carbon $minDate = null;

    /**
     * Максимальная дата логов
     */
    protected ?Carbon $maxDate = null;

    /**
     * Ссылка на предыдущую страницу
     */
    public function getPrevPageUrl(): bool|string
    {
        if (! isset($this->pageOffset['prev']) || $this->pageOffset['prev'] < 0) {
            return false;
        }

        return route('moonshine.log.viewer.file', ['file' => $this->file])."?offset={$this->pageOffset['prev']}";
    }

    /**
     * Ссылка на следующую страницу
     */
    public function getNextPageUrl(): bool|string
    {
        if (! isset($this->pageOffset['next']) || $this->pageOffset['next'] < 0) {
            return false;
        }

        return route('moonshine.log.viewer.file', ['file' => $this->file])."?offset={$this->pageOffset['next']}";
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
        $logs = collect();

        if (! file_exists($this->filePath) || is_dir($this->filePath)) {
            return $logs;
        }

        if ($time_start || $time_end) {
            [$time_start, $time_end] = $this->searchThresholdDate($time_start, $time_end);

            if (! $time_start && ! $time_end) {
                return $logs;
            }
        }

        if ($seek >= $this->getCountLines() || ! $this->setRange(
                $seek,
                $lines,
                $level,
                $env,
                $time_start,
                $time_end,
                $info
            )) {
            return $logs;
        }

        $parsed = $this->parseLog($this->extractRange());

        return $this->filter($parsed, $level, $env, $time_start, $time_end, $info);
    }

    /**
     * Устанавливаем диапазон строк, для последующего извлечения
     */
    protected function setRange(
        int $seek,
        int $lines,
        array $level = [],
        ?string $env = null,
        ?Carbon $time_start = null,
        ?Carbon $time_end = null,
        ?string $info = null
    ): bool {
        $head = $this->getCountLogs() > $seek * $lines ? $lines : $this->getCountLogs() % $lines;
        $offset = $seek + $head;

        $filter_env_level = ! empty($level) || $env
            ? 'grep -E "'.($env ?: '\w+').'\.'.(! empty($level) ? '('.strtoupper(
                    implode('|', $level)
                ).')' : '\w+').'"'
            : null;

        $filter_time = $time_start && $time_end ? "sed -n \"/\\[{$time_start}/,/\\[{$time_end}/p\"" : null;
        $filter_info = $info ? "grep \"{$info}.\"" : null;

        $output = Process::pipe(array_filter([
            $this->getSearchStartLogCommand(),
            $filter_time,
            $filter_env_level,
            $filter_info,
            "tail -n {$offset}",
            "head -n {$head}",
        ]))->output();

        preg_match_all('/(\d+):\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})]\s/', trim($output), $matches);

        if (empty($matches[1])) {
            return false;
        } else {
            $this->pageOffset = [
                'start' => (int) $matches[1][0],
                'end' => (int) last($matches[1]),
                'next' => (int) $matches[1][0] !== $this->lineFirstLog() ? $seek + $lines : -1,
                'prev' => $seek - $lines,
            ];

            if ($this->pageOffset['start'] == $this->pageOffset['end']) {
                $this->pageOffset['end'] = $this->getNextLog($this->pageOffset['start']);
            }

            return true;
        }
    }

    /**
     * Извлекаем содержимое файла в диапазоне строк
     */
    protected function extractRange(): string
    {
        return Process::run(
            "sed -n '{$this->pageOffset['start']},{$this->pageOffset['end']}p' \"{$this->filePath}\""
        )->output();
    }

    /**
     * Команда на поиск строк с датами
     */
    protected function getSearchStartLogCommand(): string
    {
        return "grep -n -E \"^\\[[0-9]{4}\" \"{$this->filePath}\"";
    }

    /**
     * Получаем номер строки следующего лога или последнюю строку файла, если логов больше нет
     */
    protected function getNextLog(int $line = 0): int
    {
        $start = $line + 1;
        $output = Process::pipe([
            "cat -n \"{$this->filePath}\" | sed -n \"{$start},{$this->getCountLines()}p\"",
            "grep -E \"^[0-9]+\s\\[[0-9]{4}\"",
            "head -n 1",
            "awk '{print $1}'",
        ])->output();

        return $output ? $output - 1 : $this->getCountLines();
    }

    /**
     * Находим границы времени для лога с фильтрацией
     *
     * @return Carbon[]|null[]
     */
    protected function searchThresholdDate(?Carbon $time_start = null, ?Carbon $time_end = null): array
    {
        $result = [null, null];

        $min_day = null;
        $max_day = null;

        if (! $time_start) {
            $result[0] = $time_start = $this->getMinDate();
        }

        if (! $time_end) {
            $result[1] = $time_end = $this->getMaxDate();
        }

        if ($result[0] && $result[1] || $time_start > $this->getMaxDate() || $time_end < $this->getMinDate()) {
            return $result;
        }

        // Находим минимальную дату за выбранные дни
        if (! $result[0]) {
            foreach ($time_start->range($time_end) as $date) {
                $output = Process::pipe([
                    "grep \"\[{$date->format('Y-m-d')}\" \"{$this->filePath}\"",
                    "head -n 1",
                ])->output();

                if (preg_match('/^\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})/', $output, $matches)) {
                    $min_day = Carbon::make($matches[1]);
                    break;
                }
            }
        }

        // Находим максимальную дату за выбранные дни
        if (! $result[1]) {
            foreach (
                array_reverse(
                    max($min_day, $time_start)->range($time_end)->toArray()
                ) as $date
            ) {
                $output = Process::pipe([
                    "grep \"\[{$date->format('Y-m-d')}\" \"{$this->filePath}\"",
                    "tail -n 1",
                ])->output();

                if (preg_match('/^\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})/', $output, $matches)) {
                    $max_day = Carbon::make($matches[1]);
                    break;
                }
            }
        }

        if ($min_day && $max_day) {
            $output = Process::pipe([
                "sed -n \"/\\[{$min_day->format('Y-m-d H:i:s')}/,/\\[{$max_day->format('Y-m-d H:i:s')}/p\" \"{$this->filePath}\"",
                "grep -on -E \"^\\[[0-9]{4}(-[0-9]{2}){2} [0-9]{2}(:[0-9]{2}){2}\"",
                "awk -F[ '{print $2}'",
                "uniq"
            ])->output();
        } else {
            $date = max($min_day, $max_day);
            $output = Process::pipe([
                "grep \"\\[{$date->format('Y-m-d')}\" \"{$this->filePath}\"",
                "grep -on -E \"^\\[[0-9]{4}(-[0-9]{2}){2} [0-9]{2}(:[0-9]{2}){2}\"",
                "awk -F[ '{print $2}'",
                "uniq"
            ])->output();
        }

        $dates = Str::of($output)->explode("\n")->filter(function ($date) use ($time_start, $time_end) {
            $date = Carbon::make($date);

            return $date > $time_start && $date < $time_end;
        });

        $result[0] ??= Carbon::make($dates->min());
        $result[1] ??= Carbon::make($dates->max());

        return $result;
    }

    /**
     * Получить максимальную дату лога
     */
    protected function getMaxDate(): ?Carbon
    {
        if ($this->maxDate) {
            return $this->maxDate;
        }

        $output = Process::pipe([
            $this->getSearchStartLogCommand(),
            "tail -n 1",
        ])->output();

        if (preg_match('/^\d+:\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})/', $output, $matches)) {
            $this->maxDate = Carbon::make($matches[1]);
        }

        return $this->maxDate;
    }

    /**
     * Получить минимальную дату лога
     */
    protected function getMinDate(): ?Carbon
    {
        if ($this->minDate) {
            return $this->minDate;
        }

        $output = Process::pipe([
            $this->getSearchStartLogCommand(),
            "head -n 1"
        ])->output();

        if (preg_match('/^\d+:\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})/', $output, $matches)) {
            $this->minDate = Carbon::make($matches[1]);
        }

        return $this->minDate;
    }

    /**
     * Количество логов в файле
     */
    protected function getCountLogs(): int
    {
        if ($this->countLogs) {
            return $this->countLogs;
        }

        $output = Process::pipe([
            $this->getSearchStartLogCommand(),
            "wc -l",
        ])->output();
        $output = trim($output);

        $this->countLogs = (int) explode(' ', $output)[0];

        return $this->countLogs;
    }

    /**
     * Количество строк в файле
     */
    protected function getCountLines(): int
    {
        if ($this->countLines) {
            return $this->countLines;
        }

        $output = Process::run("wc -l \"{$this->filePath}\"")->output();
        $output = trim($output);

        $this->countLines = (int) explode(' ', $output)[0];

        return $this->countLines;
    }

    /**
     * Номер строки первого лога
     */
    protected function lineFirstLog(): int
    {
        $output = Process::run($this->getSearchStartLogCommand()." | head -n 1")->output();

        preg_match('/(\d+):\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})]\s/', trim($output), $matches);

        return (int) $matches[1] ?? 1;
    }
}
