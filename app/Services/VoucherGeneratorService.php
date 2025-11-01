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
        $servicesTable = $order->getServicePrices();

        $templateProcessor->setValue("orderId", $order->orderId);
        $templateProcessor->setValue("orderAt", $order->createdAt->format('d.m.Y'));
        $templateProcessor->setValue("carClassName", $order->getCarClassName());
        $templateProcessor->setValue("pickupLocation", $order->getPickupLocation());
        $templateProcessor->setValue("dropoffLocation", $order->getDropoffLocation());
        $templateProcessor->setValue("pickupDate", $order->getPickupDate());
        $templateProcessor->setValue("pickupTime", $order->getPickupTime());
        $templateProcessor->setValue("passengerName", $order->getPassengerName());
        $templateProcessor->setValue("totalPassengers", $order->getNumberOfPassengers());
        $templateProcessor->setValue("passengerPhones", $order->getPassengerPhone());
        $templateProcessor->setValue("passengerEmail", $order->getPassengerEmail());
        $templateProcessor->setValue("driverComment", $order->getDriverComment());
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
