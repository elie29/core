# Core

[![Build Status](https://travis-ci.org/elie29/core.svg?branch=master)](https://travis-ci.org/elie29/core)
[![Coverage Status](https://coveralls.io/repos/github/elie29/core/badge.svg)](https://coveralls.io/github/elie29/core)

## Introduction
PHP library to create a light straightforward project using PSR-11 container.

## Installation ##

Run the command below to install via Composer:

```shell
composer require elie29/core
```

## Getting Started ##
Core framework does not require a specific structure. Only some configuration should be provided.

### Prepare the project structure ###
In order to configure the framework, we need to have a project structure. Let's assume we have the following standard
structure:
- MY_PROJECT
   - data:
      - cache: Cache folder if cache is activated
      - config:
         - config.php: Project configuration
         - container.php: Container configuration
   - public
      - index.php: Frontend controller
   - src:
      - Controllers
         - HomeIndexController: Default Core Controller that extends AbstractController
      - views
         - layouts/layout.html: Layout used by Core Framework when rendering is html
         - home/index.phtml: not required unless HomeIndexController uses fetchTemplate
   - composer.json
      - "php": "^7.1",
      - elie29/core
      - [elie29/zend-phpdi-config](https://github.com/elie29/zend-di-config): PHP-DI container configurator or choose your own

### Prepare the config file ###
Regarding the defined structure above, config.php looks as follow:

```php
// point out to MY_PROJECT
$base = dirname(dirname(__DIR__));

// should return an array
return [
    // zend-phpdi-config
    'dependencies' => [
        'aliases' => [
            RouterInterface::class => Router::class,
            RenderInterface::class => Render::class,
        ],
        'autowires' => [
            Router::class,
            Render::class,
            HomeIndexController::class
        ],
    ],

    // Core configuration
    'core' => [
        // Router configuration: Query Protocol by default
        'router' => [
            RouterConst::NAMESPACE => 'Controllers\\'
        ],
        // Render configuration
        'render' => [
            RenderConst::VIEWS_PATH => $base . '/src/views',
            RenderConst::CACHE_PATH => $base . '/data/cache',
            RenderConst::CLEAN_OUTPUT => true,
        ],
    ],
];
```

### Prepare the container ###
`container.php` depends on the installed container. For [zend-phpdi-config](https://github.com/elie29/zend-di-config), it looks like:

```php
// Protect variables from global scope
return call_user_func(function () {

    $config = require __DIR__ . '/config.php';

    $factory = new ContainerFactory();

    // Container
    return $factory(new Config($config));
});
```

### Create the index.php ###
Nothing more including `autoload.php` and instanciating `Core` class as follow:

```php
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$container = require 'data/config/container.php';

$core = new Core($container);
$core->run();
```

### Create the layout ###
`layout.phtml` is an html file with php variables assigned in the controller or returned by `preRun`, `run` or `postRun` methods.

Example of a `layout.phtml`:

```html
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="description" content="<?php echo htmlentities($description, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
   <!-- tplContent shoud display tags -->
   <div><?php echo $tplContent ?></div>
</body>
</html>
```

### Create the first Controller ###
In the config file, we specified `Controllers\\` as a default namespace, but we did not specify a default controller and a default action. In this case, the controller will be home and the action will be index. Therfore, Controller class should be named HomeIndexController with Controllers as namespace.
Each controller should implement Elie\Core\Controller or extends Elie\Core\AbstractController:

```php
namespace Controllers;

class HomeIndexController extends AbstractController
{

   public function doRun(): bool
   {
      $render = $this->container->get(RenderInterface::class);
      // true because cache_time is not defined in config.php
      return $render->hasLayoutExpired();
   }

   public function run(array $params = []): array
   {
      $render = $this->container->get(RenderInterface::class);

      // global data for layout/templates
      $render->assign([
         'description' => 'first description',
      ]);

      // layout specific data
      return ['tplContent' => '<span>my content</span>'];
   }
}
```

### Create a template ###
A template is not required, but in order to have a dynamic layout with several contents, we cas use `RenderInterface::fetchTemplate`. phtml templates should be under views_path.

Let's change `run` method in order to fetch a template:
```php
public function run(array $params = []): array
{
   $render = $this->container->get(RenderInterface::class);

   // global data for layout/templates
   $render->assign([
      'description' => 'displayed description',
   ]);

   // layout specific data
   return ['tplContent' => $this->getMyTemplate()];
}

protected function getMyTemplate(): string
{
   $cacheID = sha1(__FILE__ . 'product/detail');
   $cacheTime = 5; // 5 seconds in cache

   $render = $this->container->get(RenderInterface::class);

   // data is not needed when file is read from cache
   $data = [];

   // overrides keys assigned in run
   $render->assign([
      'description' => __METHOD__,
   ]);

   if ($render->hasTemplateExpired($cacheID, $cacheTime)) {
      $data = [
         'item' => time(),
         'description' => 'overriden by assign method'
      ];
   }

   // specific product/detail template under views_path
   return $render->fetchTemplate($data, 'product/detail', $cacheID, $cacheTime);
}
```

## Development Prerequisites ##

### Text file encoding ###
- UTF-8

### Code style formatter ###
- Zend Framework coding standard

### Composer commands ###
   - `clean`: Cleans all generated files
   - `test`: Launches unit test
   - `test-coverage`: Launches unit test with clover.xml file generation
   - `cs-check`: For code sniffer check
   - `cs-fix`: For code sniffer fix
   - `phpstan`: Launches PHP Static Analysis Tool
   - `check`: Launches `clean`, `cs-check`, `test` and `phpstan`

