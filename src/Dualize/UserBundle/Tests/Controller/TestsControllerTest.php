<?php

namespace Acme\DemoBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Dualize\UserBundle\Entity\User;
use Dualize\UserBundle\Entity\Role;
use Doctrine\ORM\EntityManager;

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

	public function estSocio()
	{
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
		$u1 = $this->em->getRepository('DualizeUserBundle:SocioType')->findOneById(5);
		$u2 = $this->em->getRepository('DualizeUserBundle:SocioType')->findOneById(6);
		echo '$u1 ' . $u1->getName();
		echo '$u2 ' . $u2->getName();
		foreach ($u1->getReference()->getValues() as $reference) {
			if ($reference->getSociotype2() === $u2) {
				echo ' | ' . $reference->getRelation()->getName();
				break;
			}
		}
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
