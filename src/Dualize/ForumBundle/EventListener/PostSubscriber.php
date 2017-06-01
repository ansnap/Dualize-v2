<?php

namespace Dualize\ForumBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Dualize\UserMessageBundle\Command\ServerCommand;
use Symfony\Component\DependencyInjection\Container;
use Dualize\ForumBundle\Entity\Post;

class PostSubscriber implements EventSubscriber
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
        $post = $args->getEntity();

        if (!($post instanceof Post)) {
            return;
        }

        $contentHTML = $this->container->get('templating')->render('DualizeForumBundle:Post:post_excerpt.html.twig', [
            'post' => $post,
        ]);

        $entryData = [
            'subject' => 'forum_posts',
            'contentHTML' => $contentHTML,
        ];

        ServerCommand::pushToSubscribers($entryData);
    }

}
