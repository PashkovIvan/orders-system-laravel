<?php

namespace App\Http\Requests;

use App\DataTransferObjects\OrderData;
use App\DataTransferObjects\OrderItemData;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
            'total_amount' => 'sometimes|numeric|min:0'
        ];
    }

    public function toDto(): OrderData
    {
        $items = array_map(
            fn($item) => new OrderItemData(
                product_name: $item['product_name'],
                quantity: $item['quantity'],
                price: $item['price'],
                total: $item['quantity'] * $item['price']
            ),
            $this->input('items')
        );

        $totalAmount = array_reduce(
            $items,
            fn($sum, OrderItemData $item) => $sum + $item->total,
            0
        );

        return new OrderData(
            customer_name: $this->input('customer_name'),
            customer_email: $this->input('customer_email'),
            items: $items,
            status: $this->input('status', 'pending'),
            total_amount: $totalAmount
        );
    }
} 