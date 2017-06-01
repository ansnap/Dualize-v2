<?php

namespace Dualize\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dualize\ForumBundle\Form\PostType;
use Dualize\ForumBundle\Entity\Post;
use Dualize\ForumBundle\Entity\Topic;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Dualize\ForumBundle\Form\MoveTopicType;
use Dualize\ForumBundle\Form\RenameTopicType;

class TopicController extends Controller
{

    public function viewAction(Request $request, $id, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        // Message form if user logged in
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $post = new Post();
            $form = $this->createForm(new PostType(), $post);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $post->setPoster($user);

                $topic = $em->getRepository('DualizeForumBundle:Topic')->find($id);
                if (!$topic) {
                    throw $this->createNotFoundException('Не найдена тема с id ' . $id);
                }
                $post->setTopic($topic);

                $em->persist($post);
                $em->flush();

                return $this->redirect($this->generateUrl('forum_topic_last_post', array('id' => $id)));
            }
        }

        // Topic view
        $qb1 = $em->createQueryBuilder()
                ->select('COUNT(p1)')
                ->from('DualizeForumBundle:Post', 'p1')
                ->andWhere('p1.poster = u');

        $qb = $em->createQueryBuilder()
                ->select('p', 't', 'f', 'u', 'ph', 'pr', 's', '(' . $qb1->getDQL() . ')')
                ->from('DualizeForumBundle:Post', 'p')
                ->leftJoin('p.poster', 'u')
                ->leftJoin('p.topic', 't')
                ->leftJoin('t.forum', 'f')
                ->leftJoin('u.profile', 'pr')
                ->leftJoin('pr.sociotype', 's')
                ->leftJoin('u.photos', 'ph')
                ->addOrderBy('p.id', 'ASC')
                ->andWhere('t.id = :id')
                ->setParameter('id', $id);

        $paginator = $this->get('knp_paginator');
        $posts_per_page = $this->container->getParameter('dualize_forum.posts_per_page');

        // Params: 1st - query, 2nd - page number, 3rd - limit per page
        $posts_paged = $paginator->paginate($qb->getQuery(), $page, $posts_per_page);

        if ($posts_paged->getTotalItemCount() == 0) {
            throw $this->createNotFoundException('Не найдена тема с id ' . $id);
        }

        $posts_paged->setUsedRoute('forum_topic'); // For clean urls
        $posts_paged->setParam('id', $id);

        $edit_time = $this->container->getParameter('dualize_forum.edit_time');

        return $this->render('DualizeForumBundle:Topic:topic.html.twig', array(
                    'posts' => $posts_paged,
                    'per_page' => $posts_per_page,
                    'edit_time' => $edit_time,
                    'form' => (isset($form)) ? $form->createView() : null,
        ));
    }

    public function lastPostAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $posts_per_page = $this->container->getParameter('dualize_forum.posts_per_page');

        $posts_count = $em->createQueryBuilder()
                        ->select('COUNT(p)')
                        ->from('DualizeForumBundle:Post', 'p')
                        ->leftJoin('p.topic', 't')
                        ->andWhere('t.id = :id')
                        ->setParameter('id', $id)
                        ->getQuery()
                        ->getResult()[0][1];

        if (!$posts_count) {
            throw $this->createNotFoundException('Не найдена тема с id ' . $id);
        }

        $last_post_id = $em->createQueryBuilder()
                        ->select('p.id')
                        ->from('DualizeForumBundle:Post', 'p')
                        ->leftJoin('p.topic', 't')
                        ->andWhere('t.id = :id')
                        ->addOrderBy('p.id', 'DESC')
                        ->setParameter('id', $id)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult()[0]['id'];

        $page = ceil($posts_count / $posts_per_page);

        return $this->redirect(
                        $this->generateUrl('forum_topic', array(
                            'id' => $id,
                            'page' => $page,
                        ))
                        . '#p' . $last_post_id
        );
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     * @ParamConverter("topic", class="DualizeForumBundle:Topic")
     */
    public function deleteAction(Topic $topic)
    {
        $forum_id = $topic->getForum()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($topic);
        $em->flush();

        return $this->redirect($this->generateUrl('forum_view', array(
                            'id' => $forum_id,
        )));
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     * @ParamConverter("topic", class="DualizeForumBundle:Topic")
     */
    public function moveAction(Request $request, Topic $topic)
    {
        $form = $this->createForm(new MoveTopicType(), $topic, array(
            'action' => $this->generateUrl('forum_topic_move', array(
                'id' => $topic->getId(),
            )),
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('forum_topic', array('id' => $topic->getId())));
        }

        return $this->render('DualizeUserBundle::form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     * @ParamConverter("topic", class="DualizeForumBundle:Topic")
     */
    public function renameAction(Request $request, Topic $topic)
    {
        $form = $this->createForm(new RenameTopicType(), $topic, array(
            'action' => $this->generateUrl('forum_topic_rename', array(
                'id' => $topic->getId(),
            )),
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('forum_topic', array('id' => $topic->getId())));
        }

        return $this->render('DualizeUserBundle::form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}
