#README


##What is Theodo Evolution?


Theodo Evolution is a set of tools, methodologies and software components, making the code of a legacy php application more maintainable, easily scalable, secure and fast.

##TheodoEvolutionSecurityBundle

This bundle allows to manage authentication and user token from the legacy appliction, into the new symfony2 application.

This bundle has a dependence with [TheodoEvolutionHttpFoundationBundle](https://github.com/theodo/theodo-evolution/tree/http-foundation-bundle), which provides the legacy session to the sf2 app.

Works for legacy app made with:

* Symfony 1.0
* Symfony 1.4

##Installation

This bundle requires Symfony 2.1 or higher.

Add the following lines to your composer.json:

```json
    "repositories": [
        ...
        {
            "type":"vcs",
            "url":"git@github.com:theodo/theodo-evolution.git"
        }
        ...
    ],
    "require": {
        ...
        "theodo/evolution-security-bundle": "dev-security-bundle"
        "theodo/evolution-http-foundation-bundle": "dev-http-foundation-bundle"
        ...
    },

```

##Configuration

### Legacy app made with symfony 1.0

* Add the bundles in your app/AppKernel.php:

```php
public function registerBundles()
{
    $bundles = array(
        //vendors, other bundles...
        new TheodoEvolution\HttpFoundationBundle\TheodoEvolutionHttpFoundationBundle(),
        new TheodoEvolution\SecurityBundle\TheodoEvolutionSecurityBundle(),
    );
}
```

* See the [documentation](Resources/doc/index.rst) for legacy specific configuration

Tip: you can also look at the [Tests](https://github.com/theodo/theodo-evolution/tree/security-bundle/Tests)
