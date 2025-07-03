<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class AuditLogRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
        $this->em = $this->getEntityManager();
    }

    public function storeEvent(string $event, array $payload): void
    {
        $audit = new AuditLog();
        $audit->setEvent($event);
        $audit->setPayload($payload);
        $audit->setReceivedAt(new \DateTime());

        $this->em->persist($audit);
        $this->em->flush();
    }
}
