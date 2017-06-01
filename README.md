RabbitMQ supervisor bundle
==========================

[![Latest Stable Version](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/v/stable.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle) [![License](https://poser.pugx.org/phobetor/rabbitmq-supervisor-bundle/license.png)](https://packagist.org/packages/phobetor/rabbitmq-supervisor-bundle)

Symfony bundle to automatically create and update supervisor configurations for `php-amqplib/rabbitmq-bundle` (and its predecessor `oldsound/rabbitmq-bundle`) RabbitMQ consumer daemons.

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
$ apt-get install supervisor
```

Add bundle via composer
```sh
$ php composer require phobetor/rabbitmq-supervisor-bundle
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

You can use the following configuration options in your `app/config/config.yml`:
```yml
rabbit_mq_supervisor:
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
        rabbitmq_consumer:              user-specific-command:consumer
        rabbitmq_multiple_consumer:     user-specific-command:multiple-consumer
    consumer:
        general:
            messages:                   250     # consumer command option: messages to consume
            memory-limit:               32      # consumer command option: allowed memory for this process
            debug:                      true    # consumer command option: enable debugging
            without-signals:            true    # consumer command option: disable catching of system signals
            worker:
                count:                  1       # number of workers per consumer
                startsecs:              2       # supervisord worker option: seconds to consider program running
                autorestart:            true    # supervisord worker option: if supervisord should restarted program automatically
                stopsignal:             INT     # supervisord worker option: the signal used to kill the program
                stopasgroup:            true    # supervisord worker option: if whole process group should be stopped
                stopwaitsecs:           60      # supervisord worker option: seconds to wait after stop signal before sending kill signal
        individual:
            # override options for specific consumers. you can use the same options for any consumer as in consumer.general
            consumer_name_1:
                # […]
            consumer_name_2:
                # […]
```

### BC break when updating from v1.* to v2.*
If you used custom commands before version 2.0, you need to update them. In most case you can just remove everything
after the command name.

## Usage

Build or rebuild the supervisor and worker configuration and start the daemon:
```sh
$ app/console rabbitmq-supervisor:rebuild
```

Control the supervisord daemon:
```sh
$ app/console rabbitmq-supervisor:control stop
$ app/console rabbitmq-supervisor:control start
$ app/console rabbitmq-supervisor:control restart
$ app/console rabbitmq-supervisor:control hup
```
