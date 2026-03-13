<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof Restaurant && ! $notifiable->notify_new_orders) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $orderNumber = sprintf('#%04d', $order->id);

        $deliveryLabels = [
            'delivery' => 'Delivery',
            'pickup' => 'Recoger en sucursal',
            'dine_in' => 'Comer en local',
        ];

        $paymentLabels = [
            'cash' => 'Efectivo',
            'terminal' => 'Terminal',
            'transfer' => 'Transferencia',
        ];

        $mail = (new MailMessage)
            ->subject("Nuevo pedido {$orderNumber}")
            ->greeting("Nuevo pedido {$orderNumber}")
            ->line("**Fecha:** {$order->created_at->format('d/m/Y H:i')}")
            ->line('**Tipo de entrega:** '.($deliveryLabels[$order->delivery_type] ?? $order->delivery_type))
            ->line("**Sucursal:** {$order->branch->name}");

        if ($order->customer) {
            $mail->line("**Cliente:** {$order->customer->name} — {$order->customer->phone}");
        }

        $mail->line('---');
        $mail->line('**Productos:**');

        foreach ($order->items as $item) {
            $line = "• {$item->quantity}x {$item->product->name} — \${$item->unit_price}";

            $modifiers = $item->modifiers->map(
                fn ($m) => $m->modifierOption->name
            )->join(', ');

            if ($modifiers) {
                $line .= " ({$modifiers})";
            }

            $mail->line($line);
        }

        $mail->line('---');

        if ($order->delivery_type === 'delivery' && $order->address_street) {
            $address = "{$order->address_street} {$order->address_number}";
            if ($order->address_colony) {
                $address .= ", {$order->address_colony}";
            }
            $mail->line("**Dirección:** {$address}");

            if ($order->address_references) {
                $mail->line("**Referencias:** {$order->address_references}");
            }
        }

        $mail->line('**Método de pago:** '.($paymentLabels[$order->payment_method] ?? $order->payment_method));

        if ($order->delivery_cost > 0) {
            $mail->line("**Subtotal:** \${$order->subtotal}");
            $mail->line("**Envío:** \${$order->delivery_cost}");
        }

        $mail->line("**Total:** \${$order->total}");

        $mail->action('Ver pedido en el panel', url('/orders'));

        return $mail;
    }
}
