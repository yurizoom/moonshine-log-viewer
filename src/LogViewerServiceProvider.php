<?php

namespace MoonShine\LogViewer;

use Illuminate\Support\ServiceProvider;
use MoonShine\LogViewer\Pages\LogViewerPage;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        moonshine()
            ->pages([
                new LogViewerPage(),
            ]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moonshine');

        $this->loadRoutesFrom(__DIR__.'/../routes/log-viewer.php');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moonshine');
    }
}
