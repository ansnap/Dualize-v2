<?php

namespace Dualize\UserBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * 1. Update field last visit of currently logged user with each request
 * 2. Set UserId to Session
 */
class EachRequestListener
{

    private $em;
    private $securityContext;
    private $session;
    private $isCalled = false; // to prevent multiple execution when embed in twig controller

    public function __construct(EntityManager $em, SecurityContext $securityContext, Session $session)
    {
        $this->em = $em;
        $this->securityContext = $securityContext;
        $this->session = $session;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if ($this->isCalled) {
            return;
        }
        $this->isCalled = true;

        if ($this->securityContext->isGranted('ROLE_USER')) {
            $user = $this->securityContext->getToken()->getUser();

            if (!$this->session->get('userId')) {
                $this->session->set('userId', $user->getId());
            }

            $user->setLastVisit(new \DateTime());
            $this->em->flush();
        }
    }

}
