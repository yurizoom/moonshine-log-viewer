<?php

namespace YuriZoom\MoonShineLogViewer;

use Illuminate\Support\ServiceProvider;
use MoonShine\Menu\MenuItem;
use MoonShine\MoonShine;
use YuriZoom\MoonShineLogViewer\Pages\LogViewerPage;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moonshine-log-viewer');
        $this->loadRoutesFrom(__DIR__.'/../routes/log-viewer.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moonshine-log-viewer');
        $this->mergeConfigFrom(__DIR__.'/../config/log-viewer.php', 'moonshine.log_viewer');

        moonshine()
            ->pages([
                new LogViewerPage(),
            ])
            ->when(
                config('moonshine.log_viewer.auto_menu'),
                fn (MoonShine $moonshine) => $moonshine->
                vendorsMenu([
                    MenuItem::make(
                        static fn () => __('Log viewer'),
                        new LogViewerPage(),
                    ),
                ])
            );
    }
}
