ShowcaseBundle
==============

## Installation

Make sure Composer is installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require tom32i/showcase-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require tom32i/showcase-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Tom32i\ShowcaseBundle\Tom32iShowcaseBundle::class => ['all' => true],
];
```

### Step 3: import Showcase routes

In `config/routes.yaml`:

```yaml
# ...

tom32i_showcase:
    resource: "@Tom32iShowcaseBundle/Controller/"
    type: attribute
```

## Usage
