<?php

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportStatusRepository::class)]
class ImportStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    #[ORM\Column(type: 'boolean')]
    private bool $failed = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: 'integer')]
    private int $totalRows = 0;

    #[ORM\Column(type: 'integer')]
    private int $processedRows = 0;

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function setTotalRows(int $totalRows): void
    {
        $this->totalRows = $totalRows;
    }

    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    public function setProcessedRows(int $processedRows): void
    {
        $this->processedRows = $processedRows;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function isFailed(): bool
    {
        return $this->failed;
    }

    public function setFailed(bool $failed): void
    {
        $this->failed = $failed;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }
}
