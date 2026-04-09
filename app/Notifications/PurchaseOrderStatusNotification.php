<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PurchaseOrder $purchaseOrder,
        public readonly string $event // 'approved' | 'rejected' | 'received'
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $poNumber = $this->purchaseOrder->po_number;
        $total    = 'Rp ' . number_format((float) $this->purchaseOrder->total_amount, 0, ',', '.');

        [$subject, $line] = match ($this->event) {
            'approved' => [
                "PO {$poNumber} Disetujui",
                "Purchase Order **{$poNumber}** telah **disetujui** dan siap diproses ke supplier.",
            ],
            'rejected' => [
                "PO {$poNumber} Ditolak",
                "Purchase Order **{$poNumber}** **ditolak**. Silakan review dan ajukan kembali jika diperlukan.",
            ],
            'received' => [
                "PO {$poNumber} Diterima",
                "Barang dari Purchase Order **{$poNumber}** sudah **diterima** di gudang.",
            ],
            default => ["Update PO {$poNumber}", "Status Purchase Order {$poNumber} telah diperbarui."],
        };

        return (new MailMessage())
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($line)
            ->line('Total nilai PO: ' . $total)
            ->line('Supplier: ' . ($this->purchaseOrder->supplier?->name ?? '-'))
            ->action('Lihat Purchase Order', url('/procurement/purchase-order'))
            ->line('WebStellar ERP — sistem manajemen retail Anda.');
    }
}
