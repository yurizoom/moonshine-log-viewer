<?php

declare(strict_types=1);

namespace YuriZoom\MoonShineLogViewer\Pages;

use MoonShine\Attributes\Icon;
use MoonShine\Pages\Page;
use YuriZoom\MoonShineLogViewer\Components\LogViewerComponent;

#[Icon('heroicons.outline.circle-stack')]
class LogViewerPage extends Page
{
    public function title(): string
    {
        return __('Log viewer');
    }

    public function breadcrumbs(): array
    {
        return [
            '#' => $this->title(),
        ];
    }

    public function components(): array
    {
        return [
            LogViewerComponent::make(),
        ];
    }
}
