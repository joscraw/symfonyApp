<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Fetch a user entity by email address
     *
     * @param string $emailAddress
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByEmailAddress($emailAddress) {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $token
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getByPasswordResetToken($token) {
        return $this->createQueryBuilder('u')
            ->where('u.passwordResetToken = :token')
            ->setParameter('token', $token)
            ->andWhere('u.passwordResetTokenTimestamp >= :timestamp')
            ->setParameter('timestamp', new \DateTime('-23 hours 59 minutes 59 seconds'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
