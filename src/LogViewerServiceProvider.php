<?php

namespace YuriZoom\MoonShineLogViewer;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\MenuManager\MenuItem;
use YuriZoom\MoonShineLogViewer\Pages\LogViewerPage;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core, MenuManagerContract $menu): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moonshine-log-viewer');
        $this->loadRoutesFrom(__DIR__.'/../routes/log-viewer.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moonshine-log-viewer');
        $this->mergeConfigFrom(__DIR__.'/../config/log-viewer.php', 'moonshine.log_viewer');

        $core
            ->pages([
                LogViewerPage::class,
            ]);

        $menu->add([
            MenuItem::make(
                __('Log viewer'),
                LogViewerPage::class,
            ),
        ]);
//            ->when(
//                config('moonshine.log_viewer.auto_menu'),
//                fn (MoonShine $moonshine) => $moonshine->
//                vendorsMenu([
//                    MenuItem::make(
//                        static fn () => __('Log viewer'),
//                        new LogViewerPage(),
//                    ),
//                ])
//            );
    }
}
