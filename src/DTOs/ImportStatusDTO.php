<?php

namespace App\Dto;

class ImportStatusDto
{
    public function __construct(
        public readonly string $filename,
        public readonly int $processedRows,
        public readonly int $totalRows,
        public readonly bool $completed = false,
        public readonly bool $failed = false,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filename: $data['filename'],
            processedRows: (int) ($data['processed_rows'] ?? 0),
            totalRows: (int) ($data['total_rows'] ?? 0),
            completed: (bool) ($data['completed'] ?? false),
            failed: (bool) ($data['failed'] ?? false),
            errorMessage: $data['error_message'] ?? null,
        );
    }
}
