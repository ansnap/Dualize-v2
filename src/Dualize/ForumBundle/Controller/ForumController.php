<?php

namespace Dualize\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dualize\ForumBundle\Entity\Topic;
use Dualize\ForumBundle\Entity\Post;
use Dualize\ForumBundle\Entity\Forum;
use Dualize\ForumBundle\Form\NewTopicType;
use Dualize\ForumBundle\Form\MoveTopicsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ForumController extends Controller
{

    /**
     * @ParamConverter("forum", class="DualizeForumBundle:Forum")
     */
    public function viewAction(Request $request, Forum $forum, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        // Message form if user logged in
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $topic = new Topic();
            $post = new Post();

            $topic->addPost($post);
            $topic->setForum($forum);

            $form = $this->createForm(new NewTopicType(), $topic);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $post->setPoster($user);
                $post->setTopic($topic);

                $em->persist($topic);
                $em->persist($post);
                $em->flush();

                return $this->redirect($this->generateUrl('forum_topic_last_post', array('id' => $topic->getId())));
            }
        }

        // Select forum, topic, first and last message + their senders, count of messages
        $qb1 = $em->createQueryBuilder()
                ->select('MAX(p2.id)')
                ->from('DualizeForumBundle:Post', 'p2')
                ->andWhere('p2.topic = t');

        $qb = $em->createQueryBuilder()
                ->select('t', 'p', 'p1', 'u', 'u1', 'COUNT(p1)')
                ->from('DualizeForumBundle:Topic', 't')
                ->leftJoin('t.posts', 'p')
                ->leftJoin('t.posts', 'p1')
                ->leftJoin('p.poster', 'u')
                ->leftJoin('p1.poster', 'u1')
                ->andWhere('p.id = (' . $qb1->getDQL() . ')')
                ->andWhere('t.forum = :forum')
                ->groupBy('t')
                ->addOrderBy('p.id', 'DESC')
                ->setParameter('forum', $forum);

        $paginator = $this->get('knp_paginator');

        $topics_per_page = $this->container->getParameter('dualize_forum.topics_per_page');

        // Params: 1st - query, 2nd - page number, 3rd - limit per page
        $topics_paged = $paginator->paginate($qb->getQuery(), $page, $topics_per_page);
        $topics_paged->setUsedRoute('forum_view'); // For clean urls
        $topics_paged->setParam('id', $forum->getId());

        return $this->render('DualizeForumBundle:Forum:forum.html.twig', array(
                    'forum' => $forum,
                    'topics' => $topics_paged,
                    'form' => (isset($form)) ? $form->createView() : null,
        ));
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function deleteTopicsAction(Request $request)
    {
        $topic_ids = json_decode($request->request->get('topics'));

        if (!$topic_ids) {
            return new Response('Wrong or empty request');
        }

        $em = $this->getDoctrine()->getManager();

        $topics = $em->createQueryBuilder()
                ->select('t')
                ->from('DualizeForumBundle:Topic', 't')
                ->andWhere('t.id IN (:topic_ids)')
                ->setParameter('topic_ids', $topic_ids)
                ->getQuery()
                ->getResult();

        if (count($topics) != count($topic_ids)) {
            return new Response('Wrong or empty request');
        }

        foreach ($topics as $topic) {
            $em->remove($topic);
        }
        $em->flush();

        return new Response('Success');
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function moveTopicsAction(Request $request, $id)
    {
        if ($request->isMethod('POST') && $id) {
            $em = $this->getDoctrine()->getManager();

            $forum = $em->getRepository('DualizeForumBundle:Forum')->find($id);
            if (!$forum) {
                throw $this->createNotFoundException('Не найден форум с id ' . $id);
            }

            $topic_ids = json_decode($request->request->get('topics'));

            if (!$topic_ids) {
                return new Response('Wrong or empty request');
            }

            $topics = $em->createQueryBuilder()
                    ->select('t')
                    ->from('DualizeForumBundle:Topic', 't')
                    ->andWhere('t.id IN (:topic_ids)')
                    ->setParameter('topic_ids', $topic_ids)
                    ->getQuery()
                    ->getResult();

            if (count($topics) != count($topic_ids)) {
                return new Response('Wrong or empty request');
            }

            foreach ($topics as $topic) {
                $topic->setForum($forum);
            }
            $em->flush();

            return new Response('Success');
        }

        $form = $this->createForm(new MoveTopicsType());

        return $this->render('DualizeUserBundle::form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}
