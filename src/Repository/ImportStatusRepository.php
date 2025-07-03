<?php
namespace App\Repository;

use App\Entity\ImportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class
ImportStatusRepository extends ServiceEntityRepository
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
}
