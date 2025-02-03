<?php

declare(strict_types=1);

namespace YuriZoom\MoonShineLogViewer\Pages;

use MoonShine\Laravel\Pages\Page;
use YuriZoom\MoonShineLogViewer\Components\LogViewerComponent;

class LogViewerPage extends Page
{
    public function getTitle(): string
    {
        return __('Log viewer');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function components(): array
    {
        return [
            LogViewerComponent::make(),
        ];
    }
}
