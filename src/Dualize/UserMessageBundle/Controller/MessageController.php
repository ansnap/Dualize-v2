<?php

namespace Dualize\UserMessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dualize\UserBundle\Entity\User;
use Dualize\UserMessageBundle\Entity\Dialog;
use Dualize\UserMessageBundle\Entity\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Dualize\UserMessageBundle\Form\MessageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Security("is_authenticated()")
 */
class MessageController extends Controller
{

    /**
     * Show list of dialogs
     */
    public function viewDialogsAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // Load dialogs with JOIN to prevent lots of queries
        $dialogs = $em->createQueryBuilder()
                ->select('d', 'm', 'u', 'p')
                ->from('DualizeUserMessageBundle:Dialog', 'd')
                ->leftJoin('d.users', 'u')
                ->leftJoin('d.messages', 'm')
                ->leftJoin('u.photos', 'p')
                ->where('d IN (:dialogs)')
                ->orderBy('m.id', 'DESC')
                ->setParameter('dialogs', $user->getDialogs()->toArray())
                ->getQuery()
                ->getResult();

        return $this->render('DualizeUserMessageBundle:Message:viewDialogs.html.twig', array(
                    'dialogs' => $dialogs,
                    'user' => $user,
        ));
    }

    /**
     * Show list of messages inside dialog
     * @ParamConverter("dialog", class="DualizeUserMessageBundle:Dialog")
     */
    public function viewMessagesAction(Request $request, Dialog $dialog, $offset)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$dialog->getUsers()->contains($user) || !$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        $limit = $this->container->getParameter('dualize_user_message.load_list_limit');

        if ($offset && is_numeric($offset)) {
            // If it's additional request - return only messages
            $first_message = $em->createQueryBuilder()
                    ->select('m')
                    ->from('DualizeUserMessageBundle:Message', 'm')
                    ->andWhere('m.dialog = :dialog')
                    ->setParameter('dialog', $dialog)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();

            // Get list of messages with limit and offset
            $q = $em->createQueryBuilder()
                    ->select('m')
                    ->from('DualizeUserMessageBundle:Message', 'm')
                    ->andWhere('m.dialog = :dialog')
                    ->addOrderBy('m.id', 'DESC')
                    ->setParameter('dialog', $dialog)
                    ->setFirstResult($offset)
                    ->setMaxResults($limit);

            $messages = new ArrayCollection(
                    array_reverse(
                            $q->getQuery()->getResult()
                    )
            );

            $messagesHTML = '';

            foreach ($messages as $message) {
                $messagesHTML .= $this->container->get('templating')->render('DualizeUserMessageBundle:Message:message.html.twig', array(
                    'message' => $message,
                    'user' => $user,
                    'first_message' => $first_message,
                ));
            }

            return $response->setContent($messagesHTML);
        } else {
            // It's first load with all messages and marking read
            $messages = $dialog->getMessages();

            // Mark as read
            foreach ($messages as $m) {
                if ($m->getIsNew() && $m->getSender() !== $user) {
                    $m->setIsNew(false);
                }
            }
            $em->flush();

            // If deleted user is participant - hide textarea
            if ($dialog->getUsers()->count() > 1) {
                $form = $this->createForm(new MessageType());
            }

            $message_counter = $this->countNewMessages($em, $user);

            return $this->render('DualizeUserMessageBundle:Message:viewMessages.html.twig', array(
                        'dialog' => $dialog,
                        'messages' => $messages->slice($messages->count() - $limit),
                        'first_message' => $messages->first(),
                        'message_counter' => $message_counter,
                        'form' => (isset($form)) ? $form->createView() : null,
                        'user' => $user,
            ));
        }

        return $response->setContent('Wrong or empty request');
    }

    /**
     * New message inside dialog
     * @ParamConverter("dialog", class="DualizeUserMessageBundle:Dialog")
     */
    public function newMessageAction(Request $request, Dialog $dialog)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        $user = $this->get('security.context')->getToken()->getUser();

        // If deleted user is participant
        if ($dialog->getUsers()->count() > 1 && $dialog->getUsers()->contains($user) && $request->isXmlHttpRequest()) {
            $message = new Message();
            $form = $this->createForm(new MessageType(), $message);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $message->setDialog($dialog);
                $message->setSender($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();

                return $this->render('DualizeUserMessageBundle:Message:message.html.twig', array(
                            'message' => $message,
                            'user' => $user,
                ));
            }
        }

        return $response->setContent('Wrong or empty request');
    }

    /**
     * New message from profile page
     * @ParamConverter("recipient", class="DualizeUserBundle:User")
     */
    public function newProfileMessageAction(Request $request, User $recipient)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user != $recipient && $request->isXmlHttpRequest()) {
            $message = new Message();
            $form = $this->createForm(new MessageType(), $message);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $user_dialogs = $user->getDialogs()->toArray();
                $recipient_dialogs = $recipient->getDialogs()->toArray();

                // Compare if 2 arrays with object have the same elements
                $common_dialogs = array_uintersect($user_dialogs, $recipient_dialogs, function($d1, $d2) {
                    return $d1->getId() == $d2->getId() ? 0 : ($d1->getId() > $d2->getId() ? 1 : -1);
                });
                $dialog = reset($common_dialogs);

                $em = $this->getDoctrine()->getManager();

                if (!$dialog) {
                    $dialog = new Dialog();
                    $dialog->addUser($user);
                    $dialog->addUser($recipient);
                    $em->persist($dialog);
                }

                $message->setDialog($dialog);
                $message->setSender($user);
                $em->persist($message);

                $em->flush();

                return $response->setContent('Message sent');
            }

            return $this->render('DualizeUserMessageBundle:Message:newProfileMessage.html.twig', array(
                        'form' => $form->createView(),
            ));
        }

        return $response->setContent('Wrong or empty request');
    }

    /**
     * Mark messages as read
     */
    public function markAsReadAction(Request $request, $dialog_id)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {

            $message_ids = json_decode($request->get('message_ids'));
            if ($message_ids == null || !is_array($message_ids) || count($message_ids) == 0 || !is_numeric($dialog_id)) {
                return $response->setContent('Wrong or empty request');
            }

            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();

            $messages = $em->createQueryBuilder()
                    ->select('m')
                    ->from('DualizeUserMessageBundle:Message', 'm')
                    ->leftJoin('m.dialog', 'd')
                    ->leftJoin('d.users', 'u')
                    ->andWhere('m.id IN (:messageIds)')
                    ->andWhere('u = :user')
                    ->andWhere('m.sender != :user')
                    ->andWhere('m.isNew = TRUE')
                    ->andWhere('d = :dialogId')
                    ->setParameter('user', $user)
                    ->setParameter('messageIds', $message_ids)
                    ->setParameter('dialogId', $dialog_id)
                    ->getQuery()
                    ->getResult();

            if (count($messages) == count($message_ids)) {
                foreach ($messages as $m) {
                    $m->setIsNew(false);
                }

                $em->flush();

                return $response->setContent('Messages marked as read');
            }
        }

        return $response->setContent('Wrong or empty request');
    }

    public static function countNewMessages(EntityManager $em, User $user)
    {
        $message_count = $em->createQueryBuilder()
                ->select('COUNT(m)')
                ->from('DualizeUserMessageBundle:Message', 'm')
                ->leftJoin('m.dialog', 'd')
                ->leftJoin('d.users', 'u')
                ->where('u = :user')
                ->andWhere('m.sender != :user')
                ->andWhere('m.isNew = TRUE')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

        $mc = $message_count[0][1];

        return ($mc) ? $mc : '';
    }

}
