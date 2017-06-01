<?php

namespace Dualize\SocioBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DemoControllerTest extends WebTestCase
{

    private $em;
    private $factory;
    private $mailer;

    public function __construct()
    {
        $kernelNameClass = $this->getKernelClass();
        $kernel = new $kernelNameClass('test', true);
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->factory = $kernel->getContainer()->get('security.encoder_factory');
        $this->mailer = $kernel->getContainer()->get('mailer');
    }

//	public function testAddRole(){
//		$role = new Role();
//	}

    public function testSocio()
    {
        $em = $this->em;
        $user = $em->getRepository('DualizeUserBundle:User')->findOneByEmail('snapik@inbox.ru');
//		$dialog = $em->getRepository('DualizeUserMessageBundle:Dialog')->findOneById(1);
//        $message = $em->getRepository('DualizeUserMessageBundle:Message')->findOneById(526);
//		$recipient = $em->getRepository('DualizeUserMessageBundle:Message')->getRecipient($message);
//        var_dump($recipient);
//        $message->setIsNew(false);
//        $inactive_users = $em->createQueryBuilder()
//                ->select('u')
//                ->from('DualizeUserBundle:User', 'u')
//                ->andWhere('u.id = 17')
//                ->getQuery()
//                ->getResult();
//        $temp = tmpfile();
//        fwrite($temp, fopen('https://pp.vk.me/c9311/u45471431/a_018ec33b.jpg', 'r'));
//
//        $tmpfname = tempnam(sys_get_temp_dir(), '');
//        file_put_contents($tmpfname, file_get_contents('https://pp.vk.me/c9311/u45471431/a_018ec33b.jpg'));
//        $tmp_file = new UploadedFile($tmpfname, '');
//var_dump($tmp_file);
//        fclose($tmp_file);
//        $em->persist(new \Dualize\UserBundle\Entity\Options());
        $em->remove($user);
        $em->flush();
//		echo $q1 . "\n";
//		$q2 = $this->em->createQueryBuilder()
//				->select('d1')
//				->from('DualizeUserMessageBundle:Dialog', 'd1')
//				->where('d1 in (' . $q1 . ')')
//				->leftJoin('d1.users', 'u1')
//				->andWhere(':user IN (d1.users)')
//				->groupBy('u1')
//				->setParameter('user', $user)
//				->getQuery();
//		var_dump($q2->getResult());
//		foreach ($types as $n => $type) {
//			$sr = new \Dualize\SocioBundle\Entity\SociotypeRelation();
//			$sr->setRelation($relation);
//			$sr->setSociotype1($types[$n]);
//			$sr->setSociotype2($types[$n]);
//			$this->em->persist($sr);
//		}
//
//		$this->em->remove($user);
//		$this->em->flush();
//
//		$t = new \Dualize\UserBundle\Entity\SocioType();
//		$t->setName('Maksim');
//		$this->em->persist($t);
//
//		$g = new \Dualize\UserBundle\Entity\SocioType();
//		$g->setName('Gamlet');
//		$this->em->persist($g);
//
//		$r = new \Dualize\UserBundle\Entity\SocioRelation();
//		$r->setName('Dual');
//		$this->em->persist($r);
//
//		$e = new \Dualize\UserBundle\Entity\SocioTypesReference();
//		$e->setRelation($r);
//		$e->setSociotype1($t);
//		$e->setSociotype2($g);
//		$this->em->persist($e);
//
//		$this->em->flush();
//
//		$u1 = $this->em->getRepository('DualizeSocioBundle:Sociotype')->findOneById(5);
//		$u2 = $this->em->getRepository('DualizeSocioBundle:Sociotype')->findOneById(6);
//		echo '$u1 ' . $u1->getName();
//		echo '$u2 ' . $u2->getName();
//		foreach ($u1->getReference()->getValues() as $reference) {
//			if ($reference->getSociotype2() === $u2) {
//				echo ' | ' . $reference->getRelation()->getName();
//				break;
//			}
//		}
    }

    public function estMail()
    {
// Send email
        $message = \Swift_Message::newInstance()
                ->setSubject('Registration on Dualize.ru')
                ->setFrom('snap.dualize@gmail.com')
                ->setTo('snap.dualize@gmail.com')
                ->setBody('Mail from test');
        $this->mailer->send($message);
    }

    public function estUserAdd()
    {

        $em = $this->em;
        $factory = $this->factory;

        $user = new User();
        $user->setEmail("snap.dualize@gmail.com");
        $user->setName("Dualize");

        echo $user->getPassword() . "\n";

        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($password);

        $role = $em->getRepository('DualizeUserBundle:Role')->findOneByName('ROLE_USER');

        $user->setRole($role);

        $em->persist($user);
        $em->flush();
    }

}
