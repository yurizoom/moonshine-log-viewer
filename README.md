Log viewer for MoonShine
============================

Компонент для отображения логов.
За основу взят [Log viewer для Laravel Admin](https://github.com/laravel-admin-extensions/log-viewer).

Реализованы работа как в Windows, так и в Linux системах.

Принцип работы в Window описан тут https://www.geekality.net/blog/php-tail-tackling-large-files .
Фильтрация в Windows при работе с очень большими файлами, может вешать запрос.

В Linux используются консольные команды cat, grep, sed, tail, head, awk.

## Скриншот

![wx20170809-165644](https://raw.githubusercontent.com/yurizoom/moonshine-log-viewer/main/blob/screenshot.jpg)

## Установка

```
$ composer require yurizoom/moonshine-log-viewer -vvv
```

## Настройка

### Путь до директории с логами

Для изменения пути до директории с логами добавьте в файл config/moonshine.php:
```php
return [
    ...
    
    'log_viewer' => [
        'path' => storage_path('logs'),
    ],
    
    ...
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
и в файле config/moonshine.app отключите автоматическое добавление в меню:
```php
return [
    ...
    
    'log_viewer' => [
        'auto_menu' => false,
    ],
    
    ...
]   
```

Лицензия
------------
[The MIT License (MIT)](LICENSE).
