<?php

namespace App\Service;

use App\Dto\ImportStatusDto;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ExcelImportDataHandler
{
    private const CACHE_KEY_PREFIX = 'import_status_';

    public function __construct(
        private Connection $connection,
        private CacheInterface $cache
    ) {}

    public function updateTotalRows(ImportStatusDto $dto): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET total_rows = ? WHERE filename = ?',
            [$dto->totalRows, $dto->filename]
        );

        $this->invalidateCache($dto->filename);
    }

    public function updateProgress(ImportStatusDto $dto): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET processed_rows = ?, total_rows = ? WHERE filename = ?',
            [$dto->processedRows, $dto->totalRows, $dto->filename]
        );

        $this->invalidateCache($dto->filename);
    }

    public function markAsComplete(string $filename): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET completed = 1, processed_rows = total_rows WHERE filename = ?',
            [$filename]
        );

        $this->invalidateCache($filename);
    }

    public function markAsFailed(string $filename, string $errorMessage): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET failed = true, error_message = ? WHERE filename = ?',
            [$errorMessage, $filename]
        );

        $this->invalidateCache($filename);
    }

    public function flushBatch(array $values, array $placeholders, array $types): void
    {
        $sql = 'INSERT INTO employee (name, email, position, salary) VALUES ' . implode(', ', $placeholders);
        $this->connection->executeStatement($sql, $values, $types);
    }

    public function getImportStatus(string $filename): ?ImportStatusDto
    {
        return $this->cache->get(self::CACHE_KEY_PREFIX . $filename, function (ItemInterface $item) use ($filename) {
            $item->expiresAfter(3600);

            $row = $this->connection->fetchAssociative(
                'SELECT filename, processed_rows, total_rows, completed, failed, error_message FROM import_status WHERE filename = ?',
                [$filename]
            );

            return $row ? ImportStatusDto::fromArray($row) : null;
        });
    }

    private function invalidateCache(string $filename): void
    {
        $this->cache->delete(self::CACHE_KEY_PREFIX . $filename);
    }
}
