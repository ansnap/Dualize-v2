<?php

namespace Dualize\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Dualize\UserBundle\Entity\Token;

class TokenSubscriber implements EventSubscriber
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $token = $args->getEntity();

        if (!($token instanceof Token)) {
            return;
        }

        $ttl = $this->container->getParameter('dualize_user.token_ttl');

        $em = $args->getEntityManager();

        $em->createQueryBuilder()
                ->delete('DualizeUserBundle:Token', 't')
                ->where('t.createdAt < :datetime')
                ->setParameter(':datetime', new \DateTime('-' . $ttl))
                ->getQuery()
                ->execute();
    }

}
