# Arg Typer

There are more known types in your project then it meets the eye. This tool finds real argument types in method calls and functions call in your PHP project. Then dumps Rector config to autocomplete them. That's it.

## Todo

* [ ] add function param type support
* [ ] handle New_ nodes

## Install

```bash
composer require rector/argtyper --dev
```

## Usage

1. First, run PHPStan to generate `phpstan-collected-data.json`

```bash
vendor/bin/phpstan analyse tests --configuration vendor/tomasvotruba/argtyper/config/phpstan-data-collector.neon
```

2. Run Rector with this rule to add new type declarations in your code:

```php
use Rector\ArgTyper\Rector\Rector\AddParamTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddParamTypeRector::class]);
```

```bash
vendor/bin/rector --config rector.php
```


<br>

Happy coding!
