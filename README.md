# Arg Typer

There are more known types in your project then it meets the eye. This tool finds real argument types in method calls and functions call in your PHP project. Then dumps Rector config to autocomplete them. That's it.

## Todo

* [ ] add function param type support

## Install

```bash
composer require rector/argtyper --dev
```

## Usage

1. First, run PHPStan to generate `phpstan-collected-data.json`

```bash
vendor/bin/phpstan analyse tests --configuration vendor/tomasvotruba/argtyper/config/phpstan-data-collector.neon
```

2. Then generate `rector-argtyper.php` config for Rector

```bash
vendor/bin/argtyper
```

3. Last, run Rector with this config and see new type declarations in your code

```bash
vendor/bin/rector --config rector-argtyper.php
```


<br>

Happy coding!
