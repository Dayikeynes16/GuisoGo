<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderConfirmationResource;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = $request->attributes->get('restaurant');

        try {
            $result = $this->orderService->store($request->validated(), $restaurant);
        } catch (\DomainException $e) {
            $errorMessages = [
                'monthly_limit_reached' => 'Lo sentimos, no podemos recibir más pedidos en este periodo.',
            ];

            return response()->json([
                'message' => $errorMessages[$e->getMessage()] ?? 'No se pudo procesar el pedido.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (ValidationException $e) {
            throw $e;
        }

        try {
            broadcast(new OrderCreated($result->order));
        } catch (\Throwable $e) {
            logger()->warning('Broadcast failed for order', ['order_id' => $result->order->id, 'error' => $e->getMessage()]);
        }

        try {
            $restaurant->load('users');
            $restaurant->notify(new NewOrderNotification($result->order));
        } catch (\Throwable $e) {
            logger()->warning('Notification failed for order', ['order_id' => $result->order->id, 'error' => $e->getMessage()]);
        }

        return (new OrderConfirmationResource($result))->response()->setStatusCode(201);
    }
}
