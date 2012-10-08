sfGuardIntegration
==================

This bundle comes with a ready-to-use the Symfony1 sfGuardPlugin integration.
Be aware thought, that it works only with default sfGuard configuration.

You may need to create your own code if sfGaurd configuration/classes are overriden.

Verify:
 1. if a custom sf_guard_plugin_check_password_callable is set
 2. if a custom algorithm is specified
 3. if all users in the db use the same algorithm
 4. if the sfGuardAuth module is not overriden adding custom validation rules

Integration instructions
------------------------

1. Configure the Encoder::

    # app/config/config.yml
    theodo_evolution_security:
        legacy: sf_guard

        #optional
        sf_guard:
            algorithm: [sha1|md5]

2. In your security.yml, define the encoder and provider for your sfGuardUser entity::

    # app/config/security.yml
    security:
        encoders:
            Acme\LegacyCompatBundle\Entity\SfGuardUser:
                id: evolution.security.encoder

        providers:
            evolution
                id: evolution.security.user_provider

3. [SF14] Configure the user repository and inject it into authentication listener::

    services:
        acme.user_repository:
            class: Acme\DemoBundle\Entity\LegacyUserRepository
            arguments:
                - @doctrine.orm.entity_manager

        evolution.security.authentication.listener:
            class: %evolution.security.authentication.listener.class%
            public: false
            arguments:
                - @security.context
                - @security.authentication.manager
                - @evolution.session.bag_manager_configuration
                - @?logger
            calls:
                - [setUserRepository, [@acme.user_repository]]

    parameters:
        evolution.security.authentication.listener.class: Theodo\Evolution\SecurityBundle\Firewall\Listener\VendorSpecific\Symfony14SecurityListener 

The LegacyUserRepostiory has to implement the Symfony14UserRepositoryInterface for symfony 1.4

