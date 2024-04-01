<?php

declare(strict_types=1);

namespace YuriZoom\MoonShineLogViewer\Components;

use MoonShine\Components\MoonShineComponent;

/**
 * @method static static make()
 */
final class LogViewerComponent extends MoonShineComponent
{
    protected string $view = 'moonshine-log-viewer::default';

    public function __construct()
    {
        //
    }

    protected function viewData(): array
    {
        return [];
    }
}
