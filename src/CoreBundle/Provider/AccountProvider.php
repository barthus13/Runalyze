<?php

namespace Runalyze\Bundle\CoreBundle\Provider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NoResultException;

class AccountProvider implements UserProviderInterface
{
    protected $accountRepository;

    public function __construct(ObjectRepository $accountRepository){
        $this->accountRepository = $accountRepository;
    }

    public function loadUserByUsername($username)
    {
        $q = $this->accountRepository
            ->createQueryBuilder('u')
            ->where('u.username = :username OR u.mail = :mail')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery();

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin MyAwesomeBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->accountRepository->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->accountRepository->getClassName() === $class
        || is_subclass_of($class, $this->accountRepository->getClassName());
    }
}