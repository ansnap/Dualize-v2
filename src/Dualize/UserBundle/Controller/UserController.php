<?php

namespace Dualize\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Dualize\UserBundle\Form\RegisterType;
use Dualize\UserBundle\Form\RestoreType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Dualize\UserBundle\Entity\User;
use Dualize\UserBundle\Entity\Token;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class UserController extends Controller {

    /**
     * Rendering and submission login form
     * login_check and logout actions - processing automatically
     */
    public function loginAction(Request $request) {
        // If not guest -> redirect to home
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getSchemeAndHttpHost() . $request->getBaseUrl());
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                    SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('DualizeUserBundle:User:login.html.twig', array(
                    // last username entered by the user
                    'last_email' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    /**
     * User registration
     */
    public function registerAction(Request $request) {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getSchemeAndHttpHost() . $request->getBaseUrl());
        }

        $user = new User();

        $form = $this->createForm(new RegisterType(), $user);

        // If form was submitted
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            // Captcha

            if ($em->getRepository('DualizeUserBundle:User')->findOneByEmail($user->getEmail())) {
                $form->get('email')->addError(new FormError('Пользователь с указанным email уже существует'));
            }

            if ($form->isValid()) {
                $site_name = $this->container->getParameter('site_name');
                $site_email = $this->container->getParameter('site_email');

                // Send email
                $message = \Swift_Message::newInstance()
                        ->setSubject('Регистрация на сайте ' . $site_name)
                        ->setFrom($site_email, $site_name)
                        ->setTo($user->getEmail())
                        ->setBody(
                        $this->renderView('DualizeUserBundle:User:mail/register.html.twig', array(
                            'site_name' => $site_name,
                            'name' => $user->getName(),
                            'password' => $user->getPlainPassword(),
                        ))
                );
                $this->get('mailer')->send($message);

                // Save user
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Вы успешно зарегистрированы и, по электронной почте, Вам отправлен пароль. Используйте его для входа на сайт.');

                return $this->redirect($this->generateUrl('login'));
            }
        }

        return $this->render('DualizeUserBundle:User:register.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * Send link to confirm changing password
     */
    public function restoreAction(Request $request) {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getSchemeAndHttpHost() . $request->getBaseUrl());
        }

        $form = $this->createForm(new RestoreType());

        // If form was submitted
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('DualizeUserBundle:User')->findOneByEmail($form->get('email')->getData());

            if (!$user) {
                $form->get('email')->addError(new FormError('Не найдено зарегистрированных пользователей с указанным email'));
            }

            if ($form->isValid()) {
                $token = new Token();

                $token->setUser($user);

                $link = $this->generateUrl('restore_confirm', array('code' => $token->getCode()), true);

                $site_name = $this->container->getParameter('site_name');
                $site_email = $this->container->getParameter('site_email');

                $message = \Swift_Message::newInstance()
                        ->setSubject('Восстановление пароля на сайте ' . $site_name)
                        ->setFrom($site_email, $site_name)
                        ->setTo($user->getEmail())
                        ->setBody(
                        $this->renderView('DualizeUserBundle:User:mail/restore.html.twig', array(
                            'site_name' => $site_name,
                            'name' => $user->getName(),
                            'link' => $link,
                        ))
                );
                $this->get('mailer')->send($message);

                $em->persist($token);
                $em->flush();

                return $this->render('DualizeUserBundle:User:restore.html.twig', array(
                            'message' => 'Success',
                ));
            }
        }

        return $this->render('DualizeUserBundle:User:restore.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * Change password if confirmed
     */
    public function restoreConfirmAction(Request $request, $code) {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($request->getSchemeAndHttpHost() . $request->getBaseUrl());
        }

        $em = $this->getDoctrine()->getManager();

        $token = $em->getRepository('DualizeUserBundle:Token')->findOneByCode($code);

        if (!$token) {
            throw $this->createNotFoundException('Не найден токен с кодом ' . $code);
        }

        $user = $token->getUser();
        $user->setPlainPassword($user->generatePassword());

        $site_name = $this->container->getParameter('site_name');
        $site_email = $this->container->getParameter('site_email');

        // Send email
        $message = \Swift_Message::newInstance()
                ->setSubject('Новый пароль для сайта ' . $site_name)
                ->setFrom($site_email, $site_name)
                ->setTo($user->getEmail())
                ->setBody(
                $this->renderView('DualizeUserBundle:User:mail/restoreConfirm.html.twig', array(
                    'site_name' => $site_name,
                    'name' => $user->getName(),
                    'password' => $user->getPlainPassword(),
                ))
        );
        $this->get('mailer')->send($message);

        // Save user, delete token
        $em->remove($token);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Ваш запрос подтвержден. На email Вам отправлен новый пароль для входа на сайт.');

        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     */
    public function whoIsOnlineAction() {
        $em = $this->getDoctrine()->getManager();

        $users = $em->createQueryBuilder()
                ->select('u', 'ph', 'p', 's')
                ->from('DualizeUserBundle:User', 'u')
                ->leftJoin('u.photos', 'ph')
                ->leftJoin('u.profile', 'p')
                ->leftJoin('p.sociotype', 's')
                ->andWhere('u.lastVisit > :datetime')
                ->setParameter('datetime', new \DateTime('-15 minute'))
                ->getQuery()
                ->getResult();

        shuffle($users);

        return $this->render('DualizeUserBundle:User:whoisonline.html.twig', [
                    'users' => $users,
        ]);
    }

}
