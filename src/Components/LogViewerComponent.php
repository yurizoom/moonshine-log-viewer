<?php

declare(strict_types=1);

namespace MoonShine\LogViewer\Components;

use MoonShine\Components\MoonShineComponent;

/**
 * @method static static make()
 */
final class LogViewerComponent extends MoonShineComponent
{
    protected string $view = 'moonshine::log-viewer';

    public function __construct()
    {
        //
    }

    protected function viewData(): array
    {
        return [];
    }
}
