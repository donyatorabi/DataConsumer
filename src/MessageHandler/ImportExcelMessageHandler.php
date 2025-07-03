<?php

namespace App\MessageHandler;

use App\Message\ImportExcelMessage;
use App\Service\ExcelImportService;
use App\Service\RowValidatorService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportExcelMessageHandler
{
    private const BATCH_SIZE = 1000;

    public function __construct(
        private ExcelImportService $excelImportService,
        private RowValidatorService $validator,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ImportExcelMessage $message): void
    {
        $filePath = $message->getFilePath();
        $fileName = $message->getFilename();

        if (!file_exists($filePath)) {
            $this->logger->error("File not found: $filePath");
            return;
        }

        $rows = $this->loadSpreadsheetRows($filePath);
        if (empty($rows)) {
            return;
        }

        unset($rows[0]); // Remove header row
        $totalRows = count($rows);
        $this->excelImportService->updateTotalRows($fileName, $totalRows);

        try {
            $this->processRows($rows, $fileName, $totalRows);
            $this->excelImportService->markAsCompleted($fileName);
            $this->logger->info("Excel import completed for file: $fileName");
        } catch (\Throwable $exception) {
            $this->handleFailure($fileName, $exception);
        }
    }

    private function loadSpreadsheetRows(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            return $spreadsheet->getActiveSheet()->toArray();
        } catch (\Throwable $e) {
            $this->logger->error("Failed to load spreadsheet: " . $e->getMessage());
            return [];
        }
    }

    private function processRows(array $rows, string $fileName, int $totalRows): void
    {
        $insertValues = [];
        $placeholders = [];
        $types = [];
        $processedRows = 0;
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            $error = $this->validator->validate($row, $rowNumber);
            if ($error) {
                $this->logger->warning($error);
                continue;
            }

            [$name, $email, $position, $salary] = array_map('trim', $row);
            $insertValues[] = $name;
            $insertValues[] = $email;
            $insertValues[] = $position;
            $insertValues[] = (float)$salary;

            $placeholders[] = '(?, ?, ?, ?)';
            $types = array_merge($types, array_fill(0, 4, \PDO::PARAM_STR));

            if (count($placeholders) === self::BATCH_SIZE) {
                $this->excelImportService->flushBatch($insertValues, $placeholders, $types);
                $processedRows += self::BATCH_SIZE;
                $this->excelImportService->updateProgress($fileName, $processedRows, $totalRows);

                $insertValues = $placeholders = $types = [];
            }
        }

        if (!empty($insertValues)) {
            $processedRows += count($placeholders);
            $this->excelImportService->flushBatch($insertValues, $placeholders, $types);
            $this->excelImportService->updateProgress($fileName, $processedRows, $totalRows);
        }
    }

    private function handleFailure(string $filename, \Throwable $e): void
    {
        $this->logger->error("Import failed: " . $e->getMessage());

        $this->excelImportService->markAsFailed(filename: $filename, errorMessage: $e->getMessage());
    }
}
