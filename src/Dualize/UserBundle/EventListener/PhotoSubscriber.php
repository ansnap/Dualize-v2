<?php

namespace Dualize\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Dualize\UserBundle\Entity\Photo;

class PhotoSubscriber implements EventSubscriber
{

	private $gaufrette;
	private $liip;
	private $kernel;

	public function __construct(\AppKernel $kernel)
	{
		$this->kernel = $kernel;
		$this->gaufrette = $kernel->getContainer()->get('knp_gaufrette.filesystem_map')->get('profile_photos_fs');
		$this->liip = $kernel->getContainer()->get('liip_imagine.cache.manager');
	}

	public function getSubscribedEvents()
	{
		return array(
			'postPersist',
			'postRemove',
		);
	}

	public function postPersist(LifecycleEventArgs $args)
	{
		$photo = $args->getEntity();
		if (!($photo instanceof Photo) || $photo->getImage() === null) {
			return;
		}

		// Convert image
		$imagine = new Imagine();
		ob_start();
		$imagine->open($photo->getImage()->getPathname())
				->strip()
				->thumbnail(new Box(1280, 1280), 'inset')
				->show('jpg', array('quality' => 95));
		$image_content = ob_get_contents();
		ob_end_clean();

		// Save image
		$this->gaufrette->write($photo->getSubPath(), $image_content);
	}

	public function postRemove(LifecycleEventArgs $args)
	{
		$photo = $args->getEntity();
		if (!($photo instanceof Photo)) {
			return;
		}

		// Remove original image
		$this->gaufrette->delete($photo->getSubPath());

		// Remove generated images
		$filters = array_keys($this->kernel->getContainer()->getParameter('liip_imagine.filter_sets'));
		foreach ($filters as $filter) {
			$this->liip->remove($photo->getFullPath(), $filter);
		}
	}

}