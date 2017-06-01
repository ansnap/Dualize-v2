<?php

namespace Dualize\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('dualize:notification:send')
                ->setDescription('Email notifications')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "Starting sending notifications...\n";

        $this->sendMessage();

        echo "All notifications sent!\n";
    }

    private function sendMessage()
    {
        $message_user_visit = $this->getContainer()->getParameter('dualize_notification.message_user_visit');
        $message_pause = $this->getContainer()->getParameter('dualize_notification.message_pause');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $message_users = $em->createQueryBuilder()
                ->select('u', 'o', 'd', 'm', 's')
                ->from('DualizeUserBundle:User', 'u')
                ->leftJoin('u.options', 'o')
                ->leftJoin('o.messageNotified', 'mn')
                ->leftJoin('u.dialogs', 'd')
                ->leftJoin('d.messages', 'm')
                ->leftJoin('m.sender', 's')
                ->andWhere('mn IS NULL OR m.id > mn.id')
                ->andWhere('s != u')
                ->andWhere('m.isNew = TRUE')
                ->andWhere('o.messageNotify = TRUE')
                ->andWhere('o.messageNotifiedAt IS NULL'
                        . ' OR o.messageNotifiedAt < u.lastVisit'
                        . ' OR o.messageNotifiedAt < :notifyPause')
                ->andWhere('u.lastVisit < :visitTimeout')
                ->setParameter('visitTimeout', new \DateTime('-' . $message_user_visit))
                ->setParameter('notifyPause', new \DateTime('-' . $message_pause))
                ->getQuery()
                ->getResult();

        foreach ($message_users as $user) {
            $message_count = 0;
            $senders = [];
            $last_message = null;

            foreach ($user->getDialogs() as $dialog) {
                $messages = $dialog->getMessages();

                $message_count += $messages->count();
                $senders[] = $messages->first()->getSender();

                if ($last_message == null || $last_message->getId() < $messages->last()->getId()) {
                    $last_message = $messages->last();
                }
            }

            $site_name = $this->getContainer()->getParameter('site_name');
            $site_email = $this->getContainer()->getParameter('site_email');

            $subject = ($message_count == 1 ? 'Новое сообщение' : 'Новые сообщения') . ' на сайте ' . $site_name;

            $mail = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($site_email, $site_name)
                    ->setTo($user->getEmail())
                    ->setBody(
                    $this->getContainer()->get('templating')->render('DualizeNotificationBundle::mail/message.html.twig', array(
                        'site_name' => $site_name,
                        'name' => $user->getName(),
                        'senders' => $senders,
                        'message_count' => $message_count,
                    )), 'text/html'
            );
            $this->getContainer()->get('mailer')->send($mail);

            $user->getOptions()->setMessageNotified($last_message)->setMessageNotifiedAt(new \Datetime());
        }
        $em->flush();

        echo "- 'Message' done (" . count($message_users) . " mails)\n";
    }

}
