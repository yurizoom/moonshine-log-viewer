<?php

declare(strict_types=1);

namespace YuriZoom\MoonShineLogViewer\Components;

use MoonShine\UI\Components\MoonShineComponent;

/**
 * @method static static make()
 */
final class LogViewerComponent extends MoonShineComponent
{
    protected string $view = 'moonshine-log-viewer::default';

    protected function viewData(): array
    {
        return [];
    }
}
