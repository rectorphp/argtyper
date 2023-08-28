# Sherlock Types

There are more known types in your project then it meets the eye. Sherlock Types is a tool that deduces hidden types from your PHP project with help of PHPStan and Rector.


## Install

```bash
composer require tomasvotruba/sherlock-types --dev
```

## Usage

1. First, run PHPStan to generate 

```bash
vendor/bin/phpstan analyse tests --configuration vendor/tomasvotruba/sherlock-types/config/phpstan-data-collector.neon
```



@todo

