<?php
namespace App\Service;

use Doctrine\DBAL\Connection;

class ExcelImportDataHandler
{
    public function __construct(
        private Connection $connection
    ) {}

    public function updateTotalRows(string $filename, int $total): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET total_rows = ? WHERE filename = ?',
            [$total, $filename]
        );
    }

    public function updateProgress(string $filename, int $processed, int $total): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET processed_rows = ?, total_rows = ? WHERE filename = ?',
            [$processed, $total, $filename]
        );
    }

    public function markAsComplete(string $filename): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET completed = 1, processed_rows = total_rows WHERE filename = ?',
            [$filename]
        );
    }

    public function markAsFailed(string $filename, string $errorMessage): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET failed = true, error_message = ? WHERE filename = ?',
            [$errorMessage, $filename]
        );
    }

    public function flushBatch(array $values, array $placeholders, array $types): void
    {
        $sql = 'INSERT INTO employee (name, email, position, salary) VALUES ' . implode(', ', $placeholders);
        $this->connection->executeStatement($sql, $values, $types);
    }
}
