<?php

namespace MoonShine\LogViewer;

use Illuminate\Support\ServiceProvider;
use MoonShine\LogViewer\Pages\LogViewerPage;
use MoonShine\Menu\MenuItem;
use MoonShine\MoonShine;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moonshine');
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
