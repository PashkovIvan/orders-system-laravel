<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\OrderData;
use App\DataTransferObjects\OrderProcessingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Responses\OrderProcessingResponse;
use App\Http\Responses\OrderResponse;
use App\Models\Order;
use App\Services\OrderProcessingService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Orders System API",
 *     description="API для системы обработки заказов"
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderProcessingService $processingService
    ) {}

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Создание нового заказа",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Сообщение об ошибке"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Список ошибок валидации",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $orderData = $request?->toDto();
            $order = $this->orderService->createOrder($orderData);
            
            // Запускаем обработку созданного заказа
            $processingData = OrderProcessingData::forStatusChange(
                orderId: $order?->id,
                oldStatus: 'pending',
                newStatus: 'processing'
            );
            $this->processingService->processOrderCreated($processingData);
            
            return OrderResponse::make($order);
        } catch (\Exception $e) {
            return OrderResponse::error($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     summary="Получение информации о заказе",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о заказе",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     )
     * )
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($order?->id);
            return OrderResponse::make($order);
        } catch (\Exception $e) {
            return OrderResponse::error($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="Получение списка заказов",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество записей на странице",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OrderResponse")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            return OrderResponse::collection(
                $this->orderService->listOrders()
            );
        } catch (\Exception $e) {
            return OrderResponse::error($e);
        }
    }

    /**
     * @OA\Patch(
     *     path="/orders/{id}/status",
     *     summary="Обновление статуса заказа",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending", "processing", "completed", "cancelled"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус заказа обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Сообщение об ошибке"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Список ошибок валидации",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateStatus(Order $order, OrderStatusRequest $request): JsonResponse
    {
        try {
            $oldStatus = $order?->status;
            $newStatus = $request?->validated('status');

            if (!$this->processingService->isValidStatusTransition($oldStatus, $newStatus)) {
                return OrderProcessingResponse::error(
                    "Invalid status transition from {$oldStatus} to {$newStatus}",
                    400
                );
            }

            $processingData = OrderProcessingData::forStatusChange(
                orderId: $order?->id,
                oldStatus: $oldStatus,
                newStatus: $newStatus
            );
            
            $processedOrder = $this->processingService->processOrderStatusChanged($processingData);
            
            return OrderProcessingResponse::statusChanged(
                $processedOrder,
                $oldStatus,
                $newStatus,
                'Order status updated successfully'
            );
        } catch (\Exception $e) {
            return OrderProcessingResponse::error($e);
        }
    }
} 