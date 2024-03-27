<?php

declare(strict_types=1);

namespace MoonShine\LogViewer\Pages;

use MoonShine\Attributes\Icon;
use MoonShine\LogViewer\Components\LogViewerComponent;
use MoonShine\Pages\Page;

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
