<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class ImportExcelMessage
{
    public function __construct(
        private string $filePath,
        private string $filename,
    ) {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
