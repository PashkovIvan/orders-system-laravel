<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DockerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgresql_connection(): void
    {
        try {
            DB::connection()->getPdo();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('PostgreSQL connection failed: ' . $e->getMessage());
        }
    }

    public function test_rabbitmq_connection(): void
    {
        try {
            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                config('queue.connections.rabbitmq.host'),
                config('queue.connections.rabbitmq.port'),
                config('queue.connections.rabbitmq.user'),
                config('queue.connections.rabbitmq.password')
            );
            $connection->close();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('RabbitMQ connection failed: ' . $e->getMessage());
        }
    }

    public function test_redis_connection(): void
    {
        try {
            \Illuminate\Support\Facades\Redis::ping();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Redis connection failed: ' . $e->getMessage());
        }
    }

    public function test_elasticsearch_connection(): void
    {
        try {
            $client = \Elasticsearch\ClientBuilder::create()
                ->setHosts([config('elasticsearch.hosts')])
                ->build();
            $client->info();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Elasticsearch connection failed: ' . $e->getMessage());
        }
    }

    public function test_nginx_connection(): void
    {
        $response = $this->get('/');
        $this->assertTrue($response->getStatusCode() === 200 || $response->getStatusCode() === 404);
    }

    public function test_services_health_check(): void
    {
        $services = [
            'postgresql' => 'http://postgresql:5432',
            'rabbitmq' => 'http://rabbitmq:15672',
            'redis' => 'http://redis:6379',
            'elasticsearch' => 'http://elasticsearch:9200',
            'nginx' => 'http://nginx'
        ];

        foreach ($services as $service => $url) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get($url);
                $this->assertTrue($response->getStatusCode() === 200);
            } catch (\Exception $e) {
                $this->fail("Service {$service} health check failed: " . $e->getMessage());
            }
        }
    }

    public function test_container_environment_variables(): void
    {
        $requiredEnvVars = [
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'RABBITMQ_HOST',
            'RABBITMQ_PORT',
            'REDIS_HOST',
            'ELASTICSEARCH_HOST'
        ];

        foreach ($requiredEnvVars as $var) {
            $this->assertNotNull(env($var), "Environment variable {$var} is not set");
        }
    }

    public function test_container_networking(): void
    {
        $services = [
            'postgresql',
            'rabbitmq',
            'redis',
            'elasticsearch',
            'nginx'
        ];

        foreach ($services as $service) {
            try {
                $host = gethostbyname($service);
                $this->assertNotEquals($host, $service, "Service {$service} is not resolvable");
            } catch (\Exception $e) {
                $this->fail("Service {$service} networking check failed: " . $e->getMessage());
            }
        }
    }

    public function test_container_volumes(): void
    {
        $paths = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views')
        ];

        foreach ($paths as $path) {
            $this->assertTrue(is_writable($path), "Path {$path} is not writable");
        }
    }
} 