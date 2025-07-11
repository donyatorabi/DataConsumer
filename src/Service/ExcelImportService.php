<?php

namespace App\Service;

use App\Entity\ImportStatus;
use App\Message\ImportExcelMessage;
use App\Repository\EmployeeRepository;
use App\Repository\ImportStatusRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class ExcelImportService
{
    private string $uploadDir;

    public function __construct(
        private MessageBusInterface $bus,
        private EmployeeRepository $employeeRepository,
        private ImportStatusRepository $importStatusRepository,
        string $projectDir,
    ) {
        $this->uploadDir = $projectDir . '/var/uploads';
    }

    public function handleUpload(UploadedFile $file): string
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $newFilename = uniqid('import_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($this->uploadDir, $newFilename);

        $this->importStatusRepository->create($newFilename);

        $this->bus->dispatch(new ImportExcelMessage($this->uploadDir . '/' . $newFilename, $newFilename));

        return $newFilename;
    }

    public function countAll(): int
    {
        return $this->employeeRepository->countAll();
    }

    public function fetchPaginated(int $limit, int $offset): array
    {
        return $this->employeeRepository->fetchPaginated($limit, $offset);
    }

    public function findImportStatusByFilename(string $filename): ?ImportStatus
    {
        return $this->importStatusRepository->find($filename);
    }
}
