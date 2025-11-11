Log viewer for MoonShine 4
============================

Компонент для отображения логов.
За основу взят [Log viewer для Laravel Admin](https://github.com/laravel-admin-extensions/log-viewer).

Реализованы работа как в Windows, так и в Linux системах.

Принцип работы в Window описан тут https://www.geekality.net/blog/php-tail-tackling-large-files .
Фильтрация в Windows при работе с очень большими файлами, может вешать запрос.

В Linux используется работа с консольными команды.

### Поддержка версий MoonShine

| MoonShine | Пакет |
|-----------|-------|
| 2.0+      | 1.0+  |
| 3.0+      | 2.0+  |
| 4.0+      | 3.0+  |

## Скриншот

![screenshot](https://github.com/yurizoom/moonshine-log-viewer/blob/main/blob/screenshot.jpg?raw=true)

## Установка

```
$ composer require yurizoom/moonshine-log-viewer -vvv
```

## Настройка

Если необходимо изменить настройки, добавьте в файле config/moonshine.php:

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

Для того чтобы добавить меню в другое место, вставьте следующий код в app/MoonShine/Layouts/MoonShineLayout.php:
```php
use YuriZoom\MoonShineLogViewer\Pages\LogViewerPage;

protected function menu(): array
    {
        return [
            ...
            
            MenuItem::make(LogViewerPage::class),
        ];
    }
```

Лицензия
------------
[The MIT License (MIT)](LICENSE).
