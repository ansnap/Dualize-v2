<?php

namespace Dualize\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dualize\UserBundle\Form\ProfileEditType;
use Dualize\UserBundle\Form\ProfileSetOptionsType;
use Dualize\UserBundle\Form\PhotoType;
use Dualize\UserBundle\Entity\User;
use Dualize\UserBundle\Entity\Photo;
use Dualize\UserBundle\Entity\Profile;
use Dualize\UserBundle\Model\Enums;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProfileController extends Controller
{

	/**
	 * Display user profile
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function viewAction(User $user)
	{

		return $this->render('DualizeUserBundle:Profile:view.html.twig', array(
					'user' => $user,
		));
	}

	/**
	 * Edit user profile
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function editAction(Request $request, User $user)
	{
		if ($this->hasEditRights($user)) {
			$form = $this->createForm(new ProfileEditType(), $user);

			$form->handleRequest($request);
			if ($form->isValid()) {
				// Save user
				$em = $this->getDoctrine()->getManager();
				$em->persist($user);
				$em->flush();

				$flash = $this->get('braincrafted_bootstrap.flash');
				$flash->success('Ваш профиль успешно изменен');

				return $this->redirect($request->getUri());
			}

			return $this->render('DualizeUserBundle:Profile:edit.html.twig', array(
						'form' => $form->createView(),
						'user' => $user,
			));
		}
	}

	/**
	 * Select list of available cities (AJAX)
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function editCityAction(Request $request, User $user)
	{
		$response = new Response();
		// type text/html because another types make problem with parseJSON from jquery
		$response->headers->set('Content-Type', 'text/html; charset=UTF-8');
		$cities = array();

		if ($this->hasEditRights($user) && $request->isXmlHttpRequest()) {

			$city_name = $request->query->get('city_name');
			if (preg_match('/^[^\d]+$/i', $city_name)) {

				$qb = $this->getDoctrine()->getManager()->createQueryBuilder();
				$qb->select('city', 'region', 'country')
						->from('DualizeUserBundle:City', 'city')
						->leftJoin('city.region', 'region')
						->leftJoin('city.country', 'country')
						->where('city.name LIKE :name')
						->setParameter('name', $city_name . '%')
						->orderBy('city.name', 'ASC')
						->setMaxResults(10);
				$result = $qb->getQuery()->getArrayResult();

				foreach ($result as $city) {
					array_push($cities, array(
						'id' => $city['id'],
						'text' => $city['name'],
						'region' => $city['region']['name'],
						'country' => $city['country']['name'],
					));
				}
			}
		}

		return $response->setContent(json_encode($cities));
	}

	/**
	 * Manage user photos
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function photoAction(Request $request, User $user)
	{
		if ($this->hasEditRights($user)) {
			$form = $this->createForm(new PhotoType());

			return $this->render('DualizeUserBundle:Profile:photo.html.twig', array(
						'photos' => $user->getPhotos(),
						'form' => $form->createView(),
						'user' => $user,
			));
		}
	}

	/**
	 * Get and save images (AJAX)
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function photoUploadAction(Request $request, User $user)
	{
		$response = new Response();

		if ($this->hasEditRights($user) && $request->isXmlHttpRequest()) {

			$response->headers->set('Content-Type', 'text/html; charset=UTF-8');

			$photo = new Photo();
			$form = $this->createForm(new PhotoType(), $photo);
			$form->handleRequest($request);

			if ($form->isValid()) {
				// If more than max photos parameter
				$max_photos = $this->container->getParameter('dualize_user.max_photos');
				if ($user->getPhotos()->count() > $max_photos - 1) {
					return $response->setContent(json_encode(array(
								'error' => 'Максимальное количество фотографий профиля: ' . $max_photos,
					)));
				}

				// Get max photo position
				$position = 0;
				foreach ($user->getPhotos() as $p) {
					if ($p->getPosition() > $position) {
						$position = $p->getPosition();
					}
				}

				$photo->setUser($user);
				$photo->setPosition(++$position);

				$em = $this->getDoctrine()->getManager();
				$em->persist($photo);
				$em->flush();

				$img_src = $this->get('liip_imagine.cache.manager')->getBrowserPath($photo->getFullPath(), 'profile');

				return $response->setContent(json_encode(array(
							'img' => $img_src,
				)));
			}

			return $response->setContent(json_encode(array(
						'error' => $form->getErrorsAsString(),
			)));
		}

		return $response->setContent('Wrong or empty request');
	}

	/**
	 * Update images position in database (AJAX)
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function photoPositionAction(Request $request, User $user)
	{
		$response = new Response();
		$response->headers->set('Content-Type', 'text/html; charset=UTF-8');

		if ($this->hasEditRights($user) && $request->isXmlHttpRequest()) {
			$img_arr = json_decode($request->getContent(), true);

			if ($img_arr && $user->getPhotos()->count() == count($img_arr)) {
				$em = $this->getDoctrine()->getManager();

				foreach ($user->getPhotos() as $photo) {
					$img_pos = $img_arr[$photo->getImageName()];

					if ($img_pos) {
						$photo->setPosition($img_pos);
					} else {
						return $response->setContent('Wrong array of images');
					}
				}

				$em->flush();

				return $response->setContent('Positions changed');
			}
		}

		return $response->setContent('Wrong or empty request');
	}

	/**
	 * Delete requested image (AJAX)
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function photoDeleteAction(Request $request, User $user, $imageName)
	{
		$response = new Response();
		$response->headers->set('Content-Type', 'text/html; charset=UTF-8');

		if ($this->hasEditRights($user) && $request->isXmlHttpRequest() && $imageName) {
			$em = $this->getDoctrine()->getManager();
			$photo = $em->getRepository('DualizeUserBundle:Photo')
					->findOneByImageName(str_replace('_', '.', $imageName));

			if (!$photo) {
				// cause the 404 page not found to be displayed
				throw $this->createNotFoundException();
			}

			$em->remove($photo);
			$em->flush();

			return $response->setContent('Image was deleted');
		}

		return $response->setContent('Wrong or empty request');
	}

	/**
	 * Change settings in user account
	 * @ParamConverter("user", class="DualizeUserBundle:User")
	 */
	public function optionsAction(Request $request, User $user)
	{
		if ($this->hasEditRights($user)) {
			$form = $this->createForm(new ProfileSetOptionsType(), $user);

			$form->handleRequest($request);
			if ($form->isValid()) {
				// Save user
				$em = $this->getDoctrine()->getManager();
				$em->flush();

				$flash = $this->get('braincrafted_bootstrap.flash');
				$flash->success('Ваши настройки успешно изменены');

				return $this->redirect($request->getUri());
			}

			return $this->render('DualizeUserBundle:Profile:options.html.twig', array(
						'form' => $form->createView(),
			));
		}
	}

    // Access to edit for profile owners, moderators and admins
	private function hasEditRights($user)
	{
		if ($user == $this->get('security.context')->getToken()->getUser() ||
				$this->get('security.context')->isGranted('ROLE_MODERATOR')) {
			return true;
		}
		throw new AccessDeniedException();
	}

}

