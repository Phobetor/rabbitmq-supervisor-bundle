RabbitMQ supervisor bundle
==========================

[![Latest Stable Version](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/v/stable.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle) [![License](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/license.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle)

Symfony bundle to automatically create and update supervisor configurations for `php-amqplib/rabbitmq-bundle` (and its predecessor `oldsound/rabbitmq-bundle`) RabbitMQ consumer daemons.

## In a nutshell | tl;dr

If you use `php-amqplib/rabbitmq-bundle` to handle the communication with RabbitMQ, just add this bundle and run
```sh
app/console rabbitmq-supervisor:rebuild
```
to get a running `supervisord` instance that automatically manages all your consumer daemons.
When your worker configuration or your code changes, run
```sh
app/console rabbitmq-supervisor:rebuild
```
again and all the daemons will be updated.

## Installation

Add bundle via command line
```sh
php composer.phar require phobetor/rabbitmq-supervisor-bundle
```

or manually to `composer.json` file
```js
{
    "require": {
        "phobetor/rabbitmq-supervisor-bundle": "~1.3"
    }
}
```

Fetch the needed files:
```bash
$ php composer.phar update phobetor/rabbitmq-supervisor-bundle
```

This will install the bundle to your project’s `vendor` directory.

Add the bundle to your project’s `AppKernel`:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // […]
        new Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle(),
    ];
}
```

## Zero Configuration

RabbitMQ supervisor bundle works out of the box with a predefined configuration. If you leave it this way you will end
up with this directory structure:
```sh
app/supervisor/
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

## Advanced configuration

All the paths and commands can be changed in `app/config/config.yml`:
```yml
rabbit_mq_supervisor:
    worker_count:                       1 # number of workers per queue
    supervisor_instance_identifier:     instance_name
    paths:
        workspace_directory:            /path/to/workspace/
        configuration_file:             /path/to/workspace/supervisord.conf
        pid_file:                       /path/to/workspace/supervisord.pid
        sock_file:                      /path/to/workspace/supervisord.sock
        log_file:                       /path/to/workspace/supervisord.log
        worker_configuration_directory: /path/to/workspace/worker/
        worker_output_log_file:         /path/to/workspace/logs/%kernel.environment%.log
        worker_error_log_file:          /path/to/workspace/logs/%kernel.environment%.log
    commands:
        rabbitmq_consumer:              user-specific-command:consumer -m %%1$d %%2$s
        rabbitmq_multiple_consumer:     user-specific-command:multiple-consumer -m %%1$d %%2$s
```

## Usage

Build or rebuild the supervisor and worker configuration and start the daemon:
```sh
app/console rabbitmq-supervisor:rebuild
```

Control the supervisord daemon:
```sh
app/console rabbitmq-supervisor:control stop
app/console rabbitmq-supervisor:control start
app/console rabbitmq-supervisor:control restart
app/console rabbitmq-supervisor:control hup
```
