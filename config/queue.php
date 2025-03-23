<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'rabbitmq'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'host' => env('RABBITMQ_HOST', 'localhost'),
            'port' => env('RABBITMQ_PORT', 5672),
            'user' => env('RABBITMQ_USER', 'www-data'),
            'password' => env('RABBITMQ_PASSWORD', 'www-data'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'queue' => env('RABBITMQ_QUEUE', 'orders_queue'),
            'exchange' => env('RABBITMQ_EXCHANGE', 'orders_exchange'),
            'exchange_type' => 'topic',
            'exchange_routing_key' => env('RABBITMQ_ROUTING_KEY', 'orders.#'),
            'consumer_tag' => env('RABBITMQ_CONSUMER_TAG', 'orders_consumer'),
            'ssl_options' => [
                'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', false),
                'verify_peer_name' => env('RABBITMQ_SSL_VERIFY_PEER_NAME', false),
                'allow_self_signed' => env('RABBITMQ_SSL_ALLOW_SELF_SIGNED', true)
            ],
            'queue_properties' => [
                'x-ha-policy' => ['S', 'all'],
                'x-dead-letter-exchange' => env('RABBITMQ_DLX', 'orders_dlx'),
                'x-dead-letter-routing-key' => env('RABBITMQ_DLX_ROUTING_KEY', 'orders.dead'),
                'x-max-priority' => 10
            ],
            'exchange_declare' => [
                'durable' => true,
                'auto_delete' => false,
                'internal' => false,
                'passive' => false
            ],
            'queue_declare' => [
                'durable' => true,
                'auto_delete' => false,
                'exclusive' => false,
                'passive' => false
            ],
            'channel_properties' => [
                'prefetch_size' => 0,
                'prefetch_count' => env('RABBITMQ_PREFETCH_COUNT', 10),
                'global' => false
            ],
            'sleep_on_error' => env('RABBITMQ_ERROR_SLEEP', 5),
            'persistent' => true
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],

];
