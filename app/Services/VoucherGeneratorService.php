<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\Services\Enums\VehicleClassEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;

class VoucherGeneratorService
{

    /**
     * Генерирует ваучер в формате Word и возвращает путь к нему
     *
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateDoc(OrderDTO $order): string {
        $templatePath = resource_path('templates/voucher_ru.docx');

        $templateProcessor = new TemplateProcessor(documentTemplate: $templatePath);

        $templateProcessor = $this->fillWordDocument(templateProcessor: $templateProcessor, order: $order);

        $newFn = "vouchers/{$order->orderId}/{$order->orderId}.docx";

        if (!Storage::exists("vouchers/{$order->orderId}")) {
            Storage::makeDirectory("vouchers/{$order->orderId}");
        }

        $templateProcessor->saveAs(public_path("storage/{$newFn}"));

        return $newFn;
    }

    private function fillWordDocument(TemplateProcessor $templateProcessor, OrderDTO $order): TemplateProcessor {
        $serviceManager = app(ServiceManager::class);

        $pricePayload = $order->pricePayload;

        $pickupLocation = [];
        $dropoffLocation = [];
        $pickupAt = $pricePayload['pickupAt'] ?? null;
        $pickupDate = null;
        $pickupTime = null;
        $passengerName = [];
        $passengerPhones = [];
        $servicesTable = [];

        if ($pricePayload['pickupLocation']['name'] ?? false) {
            $pickupLocation[] = $pricePayload['pickupLocation']['name'];
        }

        if ($pricePayload['pickupLocation']['address'] ?? false) {
            $pickupLocation[] = $pricePayload['pickupLocation']['address'];
        }

        if ($pricePayload['dropoffLocation']['name'] ?? false) {
            $dropoffLocation[] = $pricePayload['dropoffLocation']['name'];
        }

        if ($pricePayload['dropoffLocation']['address'] ?? false) {
            $dropoffLocation[] = $pricePayload['dropoffLocation']['address'];
        }

        if ($order->payload['passenger'] ?? false) {
            if ($order->payload['passenger']['firstName'] ?? false) {
                $passengerName[] = $order->payload['passenger']['firstName'];
            }

            if ($order->payload['passenger']['lastName'] ?? false) {
                $passengerName[] = $order->payload['passenger']['lastName'];
            }
        }

        if ($order->payload['passenger'] ?? false) {
            if ($order->payload['passenger']['phone'] ?? false) {
                $passengerPhones[] = $order->payload['passenger']['phone'];
            }

            if ($order->payload['passenger']['secondaryPhone'] ?? false) {
                $passengerPhones[] = $order->payload['passenger']['secondaryPhone'];
            }
        }

        if ($pickupAt) {
            $pickupDate = Carbon::parse($pickupAt)->format('d.m.Y');
            $pickupTime = Carbon::parse($pickupAt)->format('H:i');
        }

        $servicesTable[] = [
            'serviceName' => "Трансфер",
            'servicePrice' => $order->prices->tripPrice,
        ];

        foreach($order->payload['services'] ?? [] as $service) {
            $serviceDTO = $serviceManager->fetchById(id: $service['id']);

            if ($serviceDTO) {
                $serviceFullPrice = $service['quantity'] * $serviceDTO->price;

                $servicesTable[] = [
                    'serviceName' => $serviceDTO->title,
                    'servicePrice' => $serviceFullPrice,
                ];
            }
        }

        $templateProcessor->setValue("orderId", $order->orderId);
        $templateProcessor->setValue("orderAt", $order->createdAt->format('d.m.Y'));
        $templateProcessor->setValue("carClassName", VehicleClassEnum::byId(id: $order->vehicleClassId)?->value ?? "");
        $templateProcessor->setValue("pickupLocation", implode(", ", $pickupLocation));
        $templateProcessor->setValue("dropoffLocation", implode(", ", $dropoffLocation));
        $templateProcessor->setValue("pickupDate", $pickupDate);
        $templateProcessor->setValue("pickupTime", $pickupTime);
        $templateProcessor->setValue("passengerName", implode(" ", $passengerName));
        $templateProcessor->setValue("totalPassengers", $order->payload['passenger']['numberOfPassengers'] ?? 1);
        $templateProcessor->setValue("passengerPhones", implode(", ", $passengerPhones));
        $templateProcessor->setValue("passengerEmail", $order->payload['passenger']['email'] ?? "");
        $templateProcessor->setValue("driverComment", $order->payload['driverComment'] ?? "");
        $templateProcessor->setValue("fullPrice", $order->prices->fullPrice);
        $templateProcessor->setValue("fullPriceRefundable", $order->isRefundable ? $order->prices->fullPriceRefundable : "-");

        try {
            $templateProcessor->cloneRowAndSetValues("serviceName", $servicesTable);
        } catch (\Exception $err) {

        }

        return $templateProcessor;
    }

    /**
     * @throws \Exception
     */
    public function convertDocxToPdf(string $orderId, string $docxPath): string
    {
        if (!file_exists($docxPath)) {
            throw new \Exception("DOCX файл не найден: $docxPath");
        }

        // Временный каталог для конвертации
        $tmpDir = storage_path('app/public/tmp/convert_' . uniqid());
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        // Выполняем конвертацию
        $cmd = sprintf(
            'soffice --headless --convert-to pdf:writer_pdf_Export:ReduceImageTransparency=false %s --outdir %s 2>&1',
            escapeshellarg($docxPath),
            escapeshellarg($tmpDir)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка при конвертации DOCX → PDF: " . implode("\n", $output));
        }

        // Ищем готовый PDF
        $pdfFile = preg_replace('/\.docx$/i', '.pdf', basename($docxPath));
        $pdfFullPath = $tmpDir . DIRECTORY_SEPARATOR . $pdfFile;

        if (!file_exists($pdfFullPath)) {
            throw new \Exception("Не найден PDF после конвертации: $pdfFullPath");
        }

        // Загружаем в Laravel Storage (по умолчанию диск public)
        $pdfStoragePath = 'vouchers/' . $orderId. '/' . $pdfFile;
        Storage::put($pdfStoragePath, file_get_contents($pdfFullPath));

        // Чистим временные файлы
        @unlink($pdfFullPath);
        @rmdir($tmpDir);

        return $pdfStoragePath;
    }
}
