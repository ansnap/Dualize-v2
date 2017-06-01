<?php

namespace Dualize\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Dualize\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserSubscriber implements EventSubscriber {

    protected $encoderFactory;
    protected $container;

    public function __construct(Container $container, EncoderFactory $encoderFactory) {
        $this->container = $container;
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents() {
        return array(
            'prePersist',
            'preUpdate',
            'preRemove',
            'postRemove',
        );
    }

    public function prePersist(LifecycleEventArgs $args) {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateCredentials($user);

        $ttl = $this->container->getParameter('dualize_user.inactive_user_ttl');

        $em = $args->getEntityManager();

        $inactive_users = $em->createQueryBuilder()
                ->select('u')
                ->from('DualizeUserBundle:User', 'u')
                ->andWhere('u.createdAt < :datetime')
                ->andWhere('u.lastVisit IS NULL')
                ->setParameter(':datetime', new \DateTime('-' . $ttl))
                ->getQuery()
                ->getResult();

        foreach ($inactive_users as $u) {
            $em->remove($u);
        }
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateCredentials($user);
    }

    public function preRemove(LifecycleEventArgs $args) {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $em = $args->getEntityManager();

        // Set null to sender of all user's messages and posts
        $em->createQueryBuilder()
                ->update('DualizeUserMessageBundle:Message', 'message')
                ->set('message.sender', 'NULL')
                ->where('message.sender = :user')
                ->setParameter(':user', $user)
                ->getQuery()
                ->execute();

        $em->createQueryBuilder()
                ->update('DualizeForumBundle:Post', 'post')
                ->set('post.poster', 'NULL')
                ->where('post.poster = :user')
                ->setParameter(':user', $user)
                ->getQuery()
                ->execute();

        // Remove all user confirmation tokens
        $em->createQueryBuilder()
                ->delete('DualizeUserBundle:Token', 'token')
                ->where('token.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->execute();

        // TODO: If deleting spammer - fetch all dialogs with user and delete them with $em->remove()
    }

    public function postRemove(LifecycleEventArgs $args) {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $em = $args->getEntityManager();

        // Remove dialogs with messages from dialogs without participants
        $dialogs = $em->createQueryBuilder()
                ->select('dialog')
                ->from('DualizeUserMessageBundle:Dialog', 'dialog')
                ->leftJoin('dialog.users', 'users')
                ->having('COUNT(users) = 0')
                ->getQuery()
                ->getResult();

        foreach ($dialogs as $dialog) {
            $em->remove($dialog);
        }
    }

    public function updateCredentials(User $user) {
        $plainPassword = $user->getPlainPassword();

        if (!empty($plainPassword)) {
            $salt = md5(uniqid(null, true));
            $user->setSalt($salt);

            $encoder = $this->encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($plainPassword, $salt));

            $user->setPlainPassword(null);
        }
    }

}
