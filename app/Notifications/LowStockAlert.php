<?php

namespace App\Notifications;

use App\Models\WarehouseStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public const string TYPE = 'low_stock_alert';

    public function __construct(public WarehouseStock $warehouseStock) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: '.$this->warehouseStock->product->name)
            ->line('The product "'.$this->warehouseStock->product->name.'" (SKU: '.$this->warehouseStock->product->sku.') has dropped below its reorder threshold.')
            ->line('Current Quantity: '.$this->warehouseStock->product->quantity)
            ->line('Reorder Threshold: '.$this->warehouseStock->product->reorder_threshold)
            ->action('View Product', url('/products/'.$this->warehouseStock->product->id))
            ->line('Please restock this item soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => static::TYPE,
            'warehouse_stock_id' => $this->warehouseStock->id,
            'message' => 'Product '.$this->warehouseStock->product->name.' is low on stock ('.$this->warehouseStock->quantity.' remaining).',
        ];
    }
}
