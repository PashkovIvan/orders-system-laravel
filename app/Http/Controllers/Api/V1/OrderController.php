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
 *     title="Orders API",
 *     description="API для управления заказами"
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
     *     path="/api/v1/orders",
     *     summary="Создание нового заказа",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $orderData = $request->toDto();
            $order = $this->orderService->createOrder($orderData);
            
            // Запускаем обработку созданного заказа
            $processingData = OrderProcessingData::forStatusChange(
                orderId: $order->id,
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
     *     path="/api/v1/orders/{id}",
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
            $order = $this->orderService->getOrder($order->id);
            return OrderResponse::make($order);
        } catch (\Exception $e) {
            return OrderResponse::error($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="Получение списка заказов",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderResponse")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $orders = $this->orderService->listOrders();
            return OrderResponse::collection($orders);
        } catch (\Exception $e) {
            return OrderResponse::error($e);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
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
     *         @OA\JsonContent(ref="#/components/schemas/OrderStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус заказа обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/OrderProcessingResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заказ не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function updateStatus(Order $order, OrderStatusRequest $request): JsonResponse
    {
        try {
            $oldStatus = $order->status;
            $newStatus = $request->validated('status');

            $processingData = OrderProcessingData::forStatusChange(
                orderId: $order->id,
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