<?php

namespace App\Repository;

use App\Entity\ImportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ImportStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportStatus::class);

        $this->em = $this->getEntityManager();
    }

    public function create(string $filename): ImportStatus
    {
        $status = new ImportStatus();
        $status->setFilename($filename)
            ->setCompleted(false);

        $this->em->persist($status);
        $this->em->flush();

        return $status;
    }

    public function updateTotalRows(string $filename, int $total): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET total_rows = ? WHERE filename = ?',
            [$total, $filename],
            [\PDO::PARAM_INT, \PDO::PARAM_STR]
        );
    }

    public function updateProgress(string $filename, int $processed, int $total): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET processed_rows = ?, total_rows = ? WHERE filename = ?',
            [$processed, $total, $filename],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_STR]
        );
    }

    public function markAsComplete(string $filename): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET completed = 1, processed_rows = total_rows WHERE filename = ?',
            [$filename],
            [\PDO::PARAM_STR]
        );
    }

    public function markAsFailed(string $filename, string $errorMessage): void
    {
        $this->connection->executeStatement(
            'UPDATE import_status SET failed = true, error_message = ? WHERE filename = ?',
            [$errorMessage, $filename],
            [\PDO::PARAM_STR, \PDO::PARAM_STR]
        );
    }
}
