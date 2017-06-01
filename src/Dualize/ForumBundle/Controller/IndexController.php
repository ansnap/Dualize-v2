<?php

namespace Dualize\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{

    public function viewAction()
    {
        $em = $this->getDoctrine()->getManager();

        // Select forum, count of topics and messages
        $qb1 = $em->createQueryBuilder()
                ->select('COUNT(t1)')
                ->from('DualizeForumBundle:Topic', 't1')
                ->andWhere('t1.forum = f');

        $qb2 = $em->createQueryBuilder()
                ->select('COUNT(p2)')
                ->from('DualizeForumBundle:Post', 'p2')
                ->leftJoin('p2.topic', 't2')
                ->andWhere('t2.forum = f');

        $qb3 = $em->createQueryBuilder()
                ->select('MAX(t3.id)')
                ->from('DualizeForumBundle:Topic', 't3')
                ->andWhere('t3.forum = f');

        $qb4 = $em->createQueryBuilder()
                ->select('MAX(p4.id)')
                ->from('DualizeForumBundle:Post', 'p4')
                ->leftJoin('p4.topic', 't4')
                ->andWhere('t4.forum = f');

        $forums = $em->createQueryBuilder()
                ->select('f', 't', 'p', '(' . $qb1->getDQL() . ')', '(' . $qb2->getDQL() . ')')
                ->from('DualizeForumBundle:Forum', 'f')
                ->leftJoin('f.topics', 't', 'WITH', 't.id = (' . $qb3->getDQL() . ')')
                ->leftJoin('t.posts', 'p', 'WITH', 'p.id = (' . $qb4->getDQL() . ')')
                ->addOrderBy('f.position', 'ASC')
                ->getQuery()
                ->getResult();

        return $this->render('DualizeForumBundle:Index:index.html.twig', array(
                    'forums' => $forums,
        ));
    }

}
