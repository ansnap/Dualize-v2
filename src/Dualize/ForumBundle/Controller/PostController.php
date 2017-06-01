<?php

namespace Dualize\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Dualize\ForumBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Dualize\ForumBundle\Form\PostType;

class PostController extends Controller
{

    /**
     * @Security("is_granted('ROLE_USER')")
     */
    public function createAction(Request $request, $id)
    {
        $post = new Post();
        $form = $this->createForm(new PostType(), $post);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $post->setPoster($user);

            $em = $this->getDoctrine()->getManager();
            $topic = $em->getRepository('DualizeForumBundle:Topic')->find($id);
            if (!$topic) {
                throw $this->createNotFoundException('Не найдена тема с id ' . $id);
            }
            $post->setTopic($topic);

            $em->persist($post);
            $em->flush();

            return new Response('Success');
        }

        return $this->render('DualizeForumBundle:Post:post_form.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("post", class="DualizeForumBundle:Post")
     */
    public function editAction(Request $request, Post $post)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $edit_time = $this->container->getParameter('dualize_forum.edit_time');

        if (($post->getPoster() == $user && $post->getCreatedAt() > new \DateTime('-' . $edit_time)) ||
                $this->get('security.context')->isGranted('ROLE_MODERATOR')) {

            $em = $this->getDoctrine()->getManager();
            $form = $this->createForm(new PostType(), $post);

            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $this->render('DualizeBBCodeBundle:BBCode:preview.html.twig', array(
                            'text' => $post->getContent(),
                ));
            } else {
                return $this->render('DualizeUserBundle::form.html.twig', array(
                            'form' => $form->createView(),
                ));
            }
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function deletePostsAction(Request $request)
    {
        $post_ids = json_decode($request->request->get('posts'));

        if (!$post_ids) {
            return new Response('Wrong or empty request');
        }

        $em = $this->getDoctrine()->getManager();

        $posts = $em->createQueryBuilder()
                ->select('p')
                ->from('DualizeForumBundle:Post', 'p')
                ->andWhere('p.id IN (:post_ids)')
                ->setParameter('post_ids', $post_ids)
                ->getQuery()
                ->getResult();

        if (count($posts) != count($post_ids)) {
            return new Response('Wrong or empty request');
        }

        $topic = $posts[0]->getTopic();

        if ($topic->getPosts()->count() == count($posts)) {
            // topic removal
            $forum_id = $topic->getForum()->getId();

            $em->remove($topic);
            $em->flush();

            return new Response(
                    'Topic deleted|' .
                    $this->generateUrl('forum_view', array(
                        'id' => $forum_id,
            )));
        } else {
            // posts removal
            foreach ($posts as $post) {
                $em->remove($post);
            }
            $em->flush();

            return new Response('Posts deleted');
        }
    }

    /**
     * @ParamConverter("post", class="DualizeForumBundle:Post")
     */
    public function viewAction(Post $post)
    {
        $per_page = $this->container->getParameter('dualize_forum.posts_per_page');
        $topic = $post->getTopic();

        $position = $topic->getPosts()->indexOf($post);
        $page = floor($position / $per_page) + 1;

        return $this->redirect(
                        $this->generateUrl('forum_topic', array(
                            'id' => $topic->getId(),
                            'page' => $page,
                        )) . '#p' . $post->getId()
        );
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     */
    public function recentPostsAction($offset = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $posts_count = 15;

        $last_post = $em->createQueryBuilder()
                ->select('p')
                ->from('DualizeForumBundle:Post', 'p')
                ->addOrderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        $qb = $em->createQueryBuilder()
                ->select('p', 't', 'u', 'ph')
                ->from('DualizeForumBundle:Post', 'p')
                ->leftJoin('p.topic', 't')
                ->leftJoin('p.poster', 'u')
                ->leftJoin('u.photos', 'ph')
                ->addOrderBy('p.id', 'DESC')
                ->addGroupBy('p')
                ->setMaxResults($posts_count);

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        $posts = $qb->getQuery()
                ->getResult();

        return $this->render('DualizeForumBundle:Post:recent_posts.html.twig', array(
                    'posts' => $posts,
                    'last_post' => $last_post,
                        )
        );
    }

}
