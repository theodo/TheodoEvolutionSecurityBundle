#README


##What is Theodo Evolution?


Theodo Evolution is a set of tools, methodologies and software components, making the code of a legacy php application more maintainable, easily scalable, secure and fast.

##TheodoEvolutionSecurityBundle

This bundle allows to manage authentication and user token from the legacy appliction, into the new symfony2 application.

This bundle has a dependence with [TheodoEvolutionHttpFoundationBundle](https://github.com/theodo/theodo-evolution/tree/http-foundation-bundle), which provides the legacy session to the sf2 app.

Works for legacy app made with:

* Symfony 1.0

##Installation

###With Symfony 2.0

* 1. Add this in your deps file:

```
[TheodoEvolutionHttpFoundationBundle]
    git=https://github.com/theodo/theodo-evolution.git
    version=origin/http-foundation-bundle
    target=../src/TheodoEvolution/HttpFoundationBundle
[TheodoEvolutionSecurityBundle]
    git=https://github.com/theodo/theodo-evolution.git
    version=origin/security-bundle
    target=../src/TheodoEvolution/SecurityBundle
```

* 2. Then execute this command in the root of your project:

```
$ bin/vendors install
```

* 3. Finally, add the bundles in your app/autoload.php:

```php
$loader->registerNamespaces(array(
    
    // Some namespaces
    'TheodoEvolution\\HttpFoundationBundle'   => __DIR__.'/../src/TheodoEvolution/HttpFoundationBundle',
    'TheodoEvolution\\SecurityBundle'   => __DIR__.'/../src/TheodoEvolution/SecurityBundle',
));
```

###With Symfony 2.1

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

* Configure the security bundle in the app/config/config.yml file:

```
theodo_evolution_security:
    login_path: http://myPath.com/login
```


The login_path parameter should be composed in a parameter.ini (or .yml) file and put in config.yml with an alias.
This URL is used for redirect symfony2 app to the legacy login page.

* Create and define a evolution.security.legacy_user_repository service implementing LegacyUserRepositoryInterface
* Use Theodo provider in your security.yml:

```
security:
    firewalls:
        secured_area:
            pattern:    ^/demo/secured/
            evolution:
                login_path: /demo/secured/login
```

## HowTo

**TODO**: Test the service on another legacy project.

Tip: look at the [Tests](https://github.com/theodo/theodo-evolution/tree/security-bundle/Tests)
