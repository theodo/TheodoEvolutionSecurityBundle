Installation
============

This bundle requires Symfony 2.1 or higher.

Add the following lines to your composer.json:

::

    "require": {
        ...
        "theodo/evolution-security-bundle": "dev-security-bundle"
        "theodo/evolution-http-foundation-bundle": "dev-http-foundation-bundle"
        ...
    },

And run Composer:

::

    php composer.phar update theodo-evolution/session-bundle

Configuration
=============

* Add the bundles in your app/AppKernel.php:

::

    public function registerBundles()
    {
        $bundles = array(
            //vendors, other bundles...
            new TheodoEvolution\SessionBundle\TheodoEvolutionSessionBundle(),
            new TheodoEvolution\SecurityBundle\TheodoEvolutionSecurityBundle(),
        );
    }

Use cases with legacy app
=========================

Legacy app made with symfony 1.0
--------------------------------

See:
 - sfGuardIntegration_

.. _sfGuardIntegration: 01-sfGuardIntegration.rst
