RabbitMQ supervisor bundle
==========================

[![Latest Stable Version](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/v/stable.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle) [![License](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/license.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle)

Symfony bundle to automatically create and update [supervisor](http://supervisord.org/) configurations for `php-amqplib/rabbitmq-bundle` (and its predecessor `oldsound/rabbitmq-bundle`) RabbitMQ consumer daemons.

## In a nutshell | tl;dr

If you use `php-amqplib/rabbitmq-bundle` to handle the communication with RabbitMQ, just install [supervisor](http://supervisord.org/), add this bundle and run
```sh
$ app/console rabbitmq-supervisor:rebuild
```
to get a running `supervisord` instance that automatically manages all your consumer daemons.
When your worker configuration or your code changes, run the command again and all the daemons will be updated.

## Installation

Install [supervisor](http://supervisord.org/). e. g. on debian based distributions via `apt-get`:
```sh
# apt-get install supervisor
```

Add bundle via composer
```sh
$ php composer require phobetor/rabbitmq-supervisor-bundle
```
This will install the bundle to your project’s `vendor` directory.

If your are not using Symfony Flex, also add the bundle to your project’s `AppKernel`:
```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // […]
        new Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle(),
    ];
}
```

Symfony 5 & 6:

```php
// config/bundles.php
return [
    ...
    Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle::class => ["all" => true],
    ...
];

```

## Zero Configuration

RabbitMQ supervisor bundle works out of the box with a predefined configuration. If you leave it this way you will end
up with this directory structure:
```sh
supervisor/
└── dev
    ├── logs
    │   ├── stderr.log
    │   └── stdout.log
    ├── supervisord.conf
    ├── supervisord.log
    ├── supervisor.pid
    ├── supervisor.sock
    └── worker
        ├── queue1.conf
        ├── queue2.conf
        ├── queue3.conf
        └── queue4.conf
```
In symfony 2 and 3 this will be placed inside your `app/` directory.

Caution with symfony 4: to not have this inside of your `src/` directory you need to set the paths to suit your needs.
E. g. to use the standard structure inside of the `var/` directory, use this:
```yml
rabbit_mq_supervisor:
    paths:
        workspace_directory:            "%kernel.project_dir%/var/supervisor/%kernel.environment%/"
        configuration_file:             "%kernel.project_dir%/var/supervisor/%kernel.environment%/supervisord.conf"
        pid_file:                       "%kernel.project_dir%/var/supervisor/%kernel.environment%/supervisor.pid"
        sock_file:                      "%kernel.project_dir%/var/supervisor/%kernel.environment%/supervisor.sock"
        log_file:                       "%kernel.project_dir%/var/supervisor/%kernel.environment%/supervisord.log"
        worker_configuration_directory: "%kernel.project_dir%/var/supervisor/%kernel.environment%/worker/"
        worker_output_log_file:         "%kernel.project_dir%/var/supervisor/%kernel.environment%/logs/stdout.log"
        worker_error_log_file:          "%kernel.project_dir%/var/supervisor/%kernel.environment%/logs/stderr.log"
```

## Advanced configuration

To see all configuration options run
```sh
$ console config:dump-reference RabbitMqSupervisorBundle
```

### BC break when updating from v1.* to v2.*
If you used custom commands before version 2.0, you need to update them. In most case you can just remove everything
after the command name.

### BC break when updating from v2.* to v3.*
Commands will by default no longer wait for `supervisord` to complete. If you need this (e. g. to get feedback on
errors) use the `--wait-for-supervisord` option.

## Usage

Build or rebuild the supervisor and worker configuration and start the daemon:
```sh
$ console rabbitmq-supervisor:rebuild
```

Control the supervisord daemon:
```sh
$ console rabbitmq-supervisor:control stop
$ console rabbitmq-supervisor:control start
$ console rabbitmq-supervisor:control restart
$ console rabbitmq-supervisor:control hup
```
