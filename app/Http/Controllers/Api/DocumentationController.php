<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class DocumentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/api-docs",
     *     summary="Получение документации API",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="openapi", type="string", example="3.0.0"),
     *             @OA\Property(property="info", type="object"),
     *             @OA\Property(property="paths", type="object")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(
            \OpenApi\Generator::scan([app_path()])
        );
    }
} 