<?php

namespace App\Jobs;

use App\DTO\OrderDTO;
use App\DTO\UserDTO;
use App\Services\MailService;
use App\Services\UserService;
use App\Services\VoucherGeneratorService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;

class SendVoucherJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public string $email;
    public string $orderId;
    public OrderDTO $orderDTO;
    public MailService $mailService;
    public VoucherGeneratorService $voucherGeneratorService;

    public function __construct(string $email, OrderDTO $orderDTO)
    {
        $this->email = $email;
        $this->orderDTO = $orderDTO;
        $this->mailService = app(MailService::class);
        $this->voucherGeneratorService = app(VoucherGeneratorService::class);
    }

    public function handle(): void
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6); // Значение 6 соответствует TLS 1.2
        }

        try {
            $documentPath = $this->voucherGeneratorService->generateDoc(order: $this->orderDTO);
            $pdfPath = $this->voucherGeneratorService->convertDocxToPdf(orderId: $this->orderDTO->orderId, docxPath: Storage::path($documentPath));

            $this->mailService::sendVoucher(
                to: $this->email,
                subject: "✅ Заказ успешно забронирован",
                orderId: $this->orderDTO->orderId,
                voucher: $pdfPath,
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            $this->release(10);
        }
    }
}
