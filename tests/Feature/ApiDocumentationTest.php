<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_documentation_endpoint(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'openapi',
                'info' => [
                    'title',
                    'version',
                    'description'
                ],
                'paths',
                'components' => [
                    'schemas',
                    'securitySchemes'
                ]
            ]);
    }

    public function test_api_schema_validation(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'components' => [
                    'schemas' => [
                        'Order' => [
                            'type',
                            'properties' => [
                                'id',
                                'customer_name',
                                'customer_email',
                                'status',
                                'total_amount',
                                'items',
                                'created_at',
                                'updated_at'
                            ],
                            'required' => [
                                'customer_name',
                                'customer_email',
                                'items'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_examples(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'paths' => [
                    '/api/v1/orders' => [
                        'post' => [
                            'requestBody' => [
                                'content' => [
                                    'application/json' => [
                                        'example'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_authentication_documentation(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'components' => [
                    'securitySchemes' => [
                        'bearerAuth' => [
                            'type',
                            'scheme',
                            'bearerFormat'
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_error_responses(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'components' => [
                    'responses' => [
                        'Error' => [
                            'description',
                            'content' => [
                                'application/json' => [
                                    'schema'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_parameters_documentation(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'paths' => [
                    '/api/v1/orders/{id}' => [
                        'parameters' => [
                            [
                                'name',
                                'in',
                                'required',
                                'schema'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_query_parameters(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'paths' => [
                    '/api/v1/orders' => [
                        'get' => [
                            'parameters' => [
                                [
                                    'name',
                                    'in',
                                    'schema'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_api_response_schemas(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'components' => [
                    'schemas' => [
                        'Order',
                        'OrderItem',
                        'OrderCollection'
                    ]
                ]
            ]);
    }

    public function test_api_tags(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tags' => [
                    [
                        'name',
                        'description'
                    ]
                ]
            ]);
    }

    public function test_api_servers(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'servers' => [
                    [
                        'url',
                        'description'
                    ]
                ]
            ]);
    }
} 