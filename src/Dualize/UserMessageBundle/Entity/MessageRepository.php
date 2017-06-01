<?php

namespace Dualize\UserMessageBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Dualize\UserMessageBundle\Entity\Message;

/**
 * MessageRepository
 */
class MessageRepository extends EntityRepository
{

//    public function getRecipient(Message $message)
//    {
//        $q = $this->getEntityManager()
//                ->createQueryBuilder()
//                ->select('u')
//                ->from('DualizeUserBundle:User', 'u')
//                ->leftJoin('u.dialogs', 'd')
//                ->leftJoin('d.messages', 'm')
//                ->andWhere('m = :message')
//                ->andWhere('u != m.sender')
//                ->setParameter(':message', $message)
//                ->getQuery();
//
//        return $q->getSingleResult();
//    }

}
