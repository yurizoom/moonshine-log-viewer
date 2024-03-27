<?php

namespace MoonShine\LogViewer\Contracts;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AbstractLogViewer
{
    /**
     * The log file name.
     */
    public string $file;

    protected string $dirPath;

    /**
     * The path of log file.
     *
     * @var ?string
     */
    protected ?string $filePath = null;

    /**
     * LogViewer constructor.
     *
     * @param  null  $file
     *
     * @throws Exception
     */
    public function __construct($file = null)
    {
        $this->dirPath = Str::finish(config('moonshine.log_viewer.path', storage_path('logs')), '/');

        if (is_null($file)) {
            $file = $this->getLastModifiedLog();
        }

        $this->file = $file;

        $this->getFilePath();
    }

    /**
     * Get file path by giving log file name.
     *
     * @throws Exception
     */
    public function getFilePath(): string
    {
        if (! $this->filePath) {
            $path = $this->dirPath.$this->file;

            if (! file_exists($path)) {
                throw new Exception('log not exists!');
            }

            $this->filePath = $path;
        }

        return $this->filePath;
    }

    /**
     * Get size of log file.
     */
    public function getFilesize(): int
    {
        return filesize($this->filePath);
    }

    /**
     * Get last update of log file.
     */
    public function getLastUpdate(): ?string
    {
        $time = filemtime($this->filePath);

        return $time ? Carbon::createFromTimestamp($time)->toDateTimeString() : null;
    }

    /**
     * Получить список файлов из хранилища
     */
    public function getLogFiles(int $count = 20): array
    {
        $files = glob($this->dirPath.'*');
        $files = array_combine($files, array_map('filemtime', $files));
        arsort($files);

        $files = array_map('basename', array_keys($files));

        return array_slice($files, 0, $count);
    }

    /**
     * Получить последний измененный файл
     */
    public function getLastModifiedLog(): string|bool
    {
        $logs = $this->getLogFiles();

        return current($logs);
    }

    /**
     * Ссылка на предыдущую страницу
     */
    abstract public function getPrevPageUrl(): bool|string;

    /**
     * Ссылка на следующую страницу
     */
    abstract public function getNextPageUrl(): bool|string;

    abstract public function fetch(
        int $seek = 0,
        int $lines = 20,
        array $level = [],
        ?string $env = null,
        ?Carbon $time_start = null,
        ?Carbon $time_end = null,
        ?string $info = null,
    ): Collection;

    /**
     * Парсим текст лога в коллекцию
     */
    protected function parseLog($raw): Collection
    {
        $logs = collect(
            preg_split(
                '/\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})] (\w+)\.(\w+):((?:(?!{"exception").)*)?/',
                trim($raw),
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            )
        );

        foreach ($logs as $index => $log) {
            if (preg_match('/^\d{4}/', $log)) {
                break;
            } else {
                unset($logs[$index]);
            }
        }

        if ($logs->isEmpty()) {
            return $logs;
        }

        $parsed = $logs->chunk(5)
            ->map(fn (Collection $log) => [
                'time' => $log->shift() ?? '',
                'env' => $log->shift() ?? '',
                'level' => $log->shift() ?? '',
                'info' => $log->shift() ?? '',
                'trace' => trim($log->shift() ?? ''),
            ]);

        unset($logs);

        return $parsed->sortByDesc('time')->values();
    }

    protected function filter(
        Collection $logs,
        array $level = [],
        ?string $env = null,
        ?Carbon $time_start = null,
        ?Carbon $time_end = null,
        ?string $info = null,
    ): Collection {
        return $logs->filter(
            fn ($log) => ! ($time_start && Carbon::make($log['time']) <= $time_start
                || $time_end && Carbon::make($log['time']) > $time_end
                || ! empty($level) && ! Str::contains(implode(';', $level), $log['level'], true)
                || $env && Str::lower($log['env']) != Str::lower($env)
                || $info && ! Str::of($log['info'])->contains($info, true))
        );
    }
}
