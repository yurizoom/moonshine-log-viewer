<?php

namespace YuriZoom\MoonShineLogViewer\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract as MoonShineRequest;
use YuriZoom\MoonShineLogViewer\Drivers\LogViewerLinux;
use YuriZoom\MoonShineLogViewer\Drivers\LogViewerWindows;

class LogViewerController extends MoonShineController
{
    /**
     * @throws Exception
     */
    public function index(MoonShineRequest $request, ?string $file = null): array
    {
        if ($file === null) {
            $file = (new LogViewerWindows())->getLastModifiedLog();
        }

        $offset = (int) $request->get('offset', 0);

        $filter_level = array_filter(explode(',', $request->get('filter_level') ?? ''));
        $filter_env = $request->get('filter_env');
        $filters_time_start = Carbon::make($request->get('filter_time_start'));
        $filters_time_end = Carbon::make($request->get('filter_time_end'));
        $filter_info = $request->get('filter_info');

        $viewer = Str::of(PHP_OS)->upper()->startsWith('WIN')
            ? new LogViewerWindows($file)
            : new LogViewerLinux($file);

        return [
            'logs' => $viewer->fetch(
                $offset,
                level: $filter_level,
                env: $filter_env,
                time_start: $filters_time_start,
                time_end: $filters_time_end,
                info: $filter_info,
            ),
            'logFiles' => $viewer->getLogFiles(),
            'fileName' => $viewer->file,
            'end' => $viewer->getFilesize(),
            'prevUrl' => $viewer->getPrevPageUrl(),
            'nextUrl' => $viewer->getNextPageUrl(),
            'size' => $this->bytesToHuman($viewer->getFilesize()),
            'lastUpdate' => $viewer->getLastUpdate(),
        ];
    }

    protected function bytesToHuman($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
