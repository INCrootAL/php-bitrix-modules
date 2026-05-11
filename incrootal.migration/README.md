# Миграции для разработчиков (1С-Битрикс) #
[![Latest Stable Version](https://poser.pugx.org/andreyryabin/incrootal.migration/v/stable.svg)](https://packagist.org/packages/andreyryabin/incrootal.migration/)
[![Total Downloads](https://img.shields.io/packagist/dt/andreyryabin/incrootal.migration.svg?style=flat)](https://packagist.org/packages/andreyryabin/incrootal.migration)

Помогает переносить изменения между нескольким копиями проекта.

Все изменения для базы данных пишутся в файлы миграций, эти файлы, как и весь код проекта, хранятся в системе контроля версий (например git) и попадают в копии разработчиков, после чего им необходимо выполнить установку новых миграций, чтобы обновить бд.

Работать можно как через консоль, так и через админку.

* Маркетплейс: [http://marketplace.1c-bitrix.ru/solutions/incrootal.migration/](http://marketplace.1c-bitrix.ru/solutions/incrootal.migration/)
* Composer: [https://packagist.org/packages/andreyryabin/incrootal.migration](https://packagist.org/packages/andreyryabin/incrootal.migration)
* Документация: [https://github.com/andreyryabin/incrootal.migration/wiki](https://github.com/andreyryabin/incrootal.migration/wiki)
* Материалы: [https://dev.1c-bitrix.ru/community/webdev/user/39653/blog/](https://dev.1c-bitrix.ru/community/webdev/user/39653/blog/)
* Группа в телеграм: [https://t.me/sprint_migration_bitrix](https://t.me/sprint_migration_bitrix)

Особая благодарность
-------------------------
Самой лучшей IDE на свете!\
[![Phpstorm](https://raw.githubusercontent.com/wiki/andreyryabin/incrootal.migration/assets/phpstorm.png)](https://www.jetbrains.com/?from=incrootal.migration)

А также всем помощникам!\
[https://github.com/andreyryabin/incrootal.migration/blob/master/contributors.txt](https://github.com/andreyryabin/incrootal.migration/blob/master/contributors.txt)


Установка через composer
-------------------------
Пример вашего composer.json с установкой модуля в local/modules/
```
{
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  },
  "require": {
    "andreyryabin/incrootal.migration": "dev-master"
  },
}

```

Консоль
-------------------------
Для работы через консоль используется скрипт 
`/bitrix/modules/incrootal.migration/tools/migrate.php`

Можно запускать его напрямую или сделать алиас, 
создав файл в корне проекта, `bin/migrate` и прописав в нем:

```
#!/usr/bin/env php
<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/incrootal.migration/tools/migrate.php';

```


Консоль symfony
-------------------------
Если у вас используется связка bitrix + symfony, то можно подключить 
модуль как бандл симфони и запускать консольные команды модуля через 

`php bin/console sprint:migration`

Пример регистрации бандла:

```
// app/AppKernel.php
use Incrootal\Migration\SymfonyBundle\SprintMigrationBundle;

public function registerBundles()
{
    $bundles = array(
        new SprintMigrationBundle(),
    );
    return $bundles;
}
```

Пример без регистрации бандла, только команды в symfony/console
```
// bin/console
use Incrootal\Migration\SymfonyBundle\Command\ConsoleCommand;
use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new ConsoleCommand());

$app->run();

```

Классы модуля должны уже быть автозагружены, через `CModule::IncludeModule('incrootal.migration')`

Или через библиотеку https://packagist.org/packages/webarchitect609/bitrix-neverinclude (рекомендую этот вариант)

Примеры команд
-------------------------
* php bin/migrate add (создать новую миграцию)
* php bin/migrate ls  (показать список миграций )
* php bin/migrate up (накатить все миграции) 
* php bin/migrate up [version] (накатить выбранную миграцию)
* php bin/migrate down (откатить все миграции)
* php bin/migrate down [version] (откатить выбранную миграцию)

Все команды: https://github.com/andreyryabin/incrootal.migration/blob/master/commands.txt


Добавлена миграция смарт-процессов для Bitrix24
-------------------------
