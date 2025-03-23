/**
 * @OA\Schema(
 *     schema="OrderItemResponse",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID позиции заказа"
 *     ),
 *     @OA\Property(
 *         property="product_name",
 *         type="string",
 *         description="Название товара"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         description="Количество"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Цена за единицу"
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="number",
 *         format="float",
 *         description="Общая стоимость позиции"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderResponse",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID заказа"
 *     ),
 *     @OA\Property(
 *         property="customer_name",
 *         type="string",
 *         description="Имя клиента"
 *     ),
 *     @OA\Property(
 *         property="customer_email",
 *         type="string",
 *         format="email",
 *         description="Email клиента"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "processing", "completed", "cancelled"},
 *         description="Статус заказа"
 *     ),
 *     @OA\Property(
 *         property="total_amount",
 *         type="number",
 *         format="float",
 *         description="Общая сумма заказа"
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderItemResponse")
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата обновления"
 *     )
 * )
 */
class OrderResource extends JsonResource
{
    // ... существующий код ...
}

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Сообщение об ошибке"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Список ошибок валидации"
 *     )
 * )
 */ 