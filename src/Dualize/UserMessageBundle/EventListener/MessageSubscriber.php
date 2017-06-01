<?php

namespace Dualize\UserMessageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Dualize\UserMessageBundle\Entity\Message;
use Dualize\UserMessageBundle\Command\ServerCommand;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class MessageSubscriber implements EventSubscriber
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
            'preUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $message = $args->getEntity();

        if (!($message instanceof Message)) {
            return;
        }

        $dialog = $message->getDialog();

        $recipient = $dialog->getUsers()
                ->filter(function($user) use (&$message) {
                    return $message->getSender() != $user;
                })
                ->first();

        if ($dialog->getMessages()->count() == 0) {
            // It's dialog creation
            $dialog->addMessage($message); // Add message to process in template, because it's not added to db yet
            $contentHTML = $this->container->get('templating')->render('DualizeUserMessageBundle:Message:dialog.html.twig', [
                'dialog' => $dialog,
                'user' => $recipient,
            ]);
        } else {
            $contentHTML = $this->container->get('templating')->render('DualizeUserMessageBundle:Message:message.html.twig', [
                'message' => $message,
                'user' => $recipient,
            ]);
        }

        $entryData = [
            'subject' => 'messages',
            'recipientId' => $recipient->getId(),
            'dialogId' => $dialog->getId(),
            'contentHTML' => $contentHTML,
        ];

        ServerCommand::pushToSubscribers($entryData);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $message = $args->getEntity();

        if (!($message instanceof Message)) {
            return;
        }

        if ($args->hasChangedField('isNew') && $args->getNewValue('isNew') == false) {

            $entryData = [
                'subject' => 'mark_read',
                'recipientId' => $message->getSender()->getId(),
                'messageId' => $message->getId(),
            ];

            ServerCommand::pushToSubscribers($entryData);
        }
    }

}
