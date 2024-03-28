Log viewer for MoonShine
============================

Компонент для отображения логов.
За основу взят [Log viewer для Laravel Admin](https://github.com/laravel-admin-extensions/log-viewer).

Реализованы работа как в Windows, так и в Linux системах.

Принцип работы в Window описан тут https://www.geekality.net/blog/php-tail-tackling-large-files .
Фильтрация в Windows при работе с очень большими файлами, может вешать запрос.

В Linux используется работа с консольными команды.

## Скриншот

![wx20170809-165644](https://raw.githubusercontent.com/yurizoom/moonshine-log-viewer/main/blob/screenshot.jpg)

## Установка

```
$ composer require yurizoom/moonshine-log-viewer -vvv
```

## Настройка

В файле config/moonshine.php добавьте конфигурации.

```php
[
    'log_viewer' => [
        // Автоматическое добавление в меню
        'auto_menu' => true,
        // Путь до директории с логами
        'path' => storage_path('logs'),
    ]
]
```

### Добавление в меню

Для того чтобы добавить меню в другое место, вставьте следующий код в app/Providers/MoonShineServiceProvider.php:
```php
use MoonShine\LogViewer\Pages\LogViewerPage;

protected function menu(): array
    {
        return [
            ...
            
            MenuItem::make(
                static fn () => __('Log viewer'),
                new LogViewerPage(),
            ),
            
            ...
        ];
    }
```

Лицензия
------------
[The MIT License (MIT)](LICENSE).
