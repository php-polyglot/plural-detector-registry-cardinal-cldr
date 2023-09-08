# polyglot/plural-detector-registry-cardinal-cldr

> A simple [polyglot](https://packagist.org/packages/polyglot/) CLDR plural detector registry.

This plural detector registry contains plural detectors with [CLDR rules](https://www.unicode.org/cldr/charts/43/supplemental/language_plural_rules.html)
# Install

```shell
composer require polyglot/plural-detector-registry-cardinal-cldr:^1.0
```

# Using

```php
<?php

$registry = new \Polyglot\CldrCardinalPluralDetectorRegistry\CldrCardinalPluralDetectorRegistry();
$en = $registry->get('en_US'); // or $registry->get('en')
$en->getAllowedCategories(); // returns ["one", "other"]
$en->detect(1); // returns "one"
$en->detect(2); // returns "other"

$ar = $registry->get('ar');
$en->getAllowedCategories(); // returns ["zero", "one", "two", "few", "many", "other"]
$en->detect(0); // returns "zero"
$en->detect(1); // returns "one"
$en->detect(2); // returns "two"
$en->detect(rand(3, 10) + 100 * rand(0, 100)); // returns "few"
$en->detect(rand(11, 99) + 100 * rand(0, 100)); // returns "many"
$en->detect(rand(10, 99) / 10); // returns "other"

$registry->get('unknown'); // throws \Polyglot\Contract\PluralDetectorRegistry\Exception\LocaleNotSupported 
```

