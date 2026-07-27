<?php

namespace App\Notifications;

use App\Models\WarehouseStock;
use App\Notifications\Concerns\HasRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductOutOfStockNotification extends Notification implements ShouldQueue
{
    use HasRecipients, Queueable;

    public function __construct(public WarehouseStock $warehouseStock) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'product-out-of-stock';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Out of Stock: '.$this->warehouseStock->product->name)
            ->line('The product "'.$this->warehouseStock->product->name.'" (SKU: '.$this->warehouseStock->product->sku.') is now out of stock.')
            ->line('Warehouse: '.$this->warehouseStock->warehouse->name)
            ->line('Current Quantity: '.$this->warehouseStock->quantity)
            ->action('View Product', url('/products/'.$this->warehouseStock->product->id))
            ->line('Please replenish this stock as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'warehouse_stock_id' => $this->warehouseStock->id,
            'message' => sprintf(
                'Product "%s" is out of stock in warehouse "%s".',
                $this->warehouseStock->product->name,
                $this->warehouseStock->warehouse->name,
            ),
        ];
    }
}
