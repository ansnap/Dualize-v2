<?php

namespace Dualize\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{

	public function indexAction()
	{
		return $this->render('DualizeUserBundle:Page:index.html.twig');
	}

}
