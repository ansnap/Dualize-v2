<?php

namespace Dualize\UserSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dualize\UserSearchBundle\Form\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dualize\UserSearchBundle\Model\SearchParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SearchController extends Controller
{

    /**
     * Get search users page
     * @Security("is_granted('ROLE_USER')")
     */
    public function viewAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $search_params = new SearchParams();
        $is_saved_search = false;
        $em = $this->getDoctrine()->getManager();

        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            // If user did searches and they were saved
            if ($user->getOptions()->getSearchParams()) {
                $search_params = unserialize($user->getOptions()->getSearchParams());
                $this->retrieveEntities($search_params);
                $is_saved_search = true;
            } else {
                // TODO: Provide some default values for user - his dual, +-5years, his city
            }
        } else {
            if ($this->container->get('session')->isStarted() && $this->container->get('session')->get('searchParams')) {
                $search_params = unserialize($this->container->get('session')->get('searchParams'));
                $this->retrieveEntities($search_params);
                $is_saved_search = true;
            }
        }

        $form = $this->createForm(new SearchType(), $search_params);
        $form->handleRequest($request);

        // Save search params to DB or to Session
        if ($form->isValid()) {
            if ($this->get('security.context')->isGranted('ROLE_USER')) {
                $user->getOptions()->setSearchParams(serialize($search_params));
                $em->flush();
            } else {
                $session = $this->container->get('session');
                $session->set('searchParams', serialize($search_params));
            }
            return $this->redirect($this->generateUrl('user_search'));
        }

        // Show list of users
        $qb = $em->createQueryBuilder()
                ->select('u', 'p', 'photos', 'sociotype', 'city', 'country')
                ->from('DualizeUserBundle:User', 'u')
                ->leftJoin('u.profile', 'p')
                ->leftJoin('u.photos', 'photos')
                ->leftJoin('p.sociotype', 'sociotype')
                ->leftJoin('p.city', 'city')
                ->leftJoin('city.country', 'country')
                ->addOrderBy('u.lastVisit', 'DESC')
                ->andWhere('u != :user')
                ->setParameter('user', $user);

        // Additional parameters to search query
        if ($search_params->getGender()) {
            $qb->andWhere('p.gender = :gender')
                    ->setParameter('gender', $search_params->getGender());
        }

        if ($search_params->getSociotype()) {
            $qb->andWhere('p.sociotype = :sociotype')
                    ->setParameter('sociotype', $search_params->getSociotype());
        }

        if ($search_params->getAgeFrom()) {
            $date = new \Datetime();
            $date->setTimestamp(strtotime('- ' . $search_params->getAgeFrom() . ' year'));
            $qb->andWhere('p.birthday < :dateFrom')
                    ->setParameter('dateFrom', $date);
        }

        if ($search_params->getAgeTo()) {
            $date = new \Datetime();
            $date->setTimestamp(strtotime('- ' . ($search_params->getAgeTo() + 1) . ' year'));
            $qb->andWhere('p.birthday > :dateTo')
                    ->setParameter('dateTo', $date);
        }

        if ($search_params->getLocationId() && $search_params->getLocationType()) {
            switch ($search_params->getLocationType()) {
                case 'city':
                    $qb->andWhere('p.city = :city')
                            ->setParameter('city', $search_params->getLocationId());
                    break;
                case 'region':
                    $qb->andWhere('city.region = :region')
                            ->setParameter('region', $search_params->getLocationId());
                    break;
                case 'country':
                    $qb->andWhere('city.country = :country')
                            ->setParameter('country', $search_params->getLocationId());
                    break;
            }
        }

        if ($search_params->getHasPhoto()) {
            $qb->andWhere('u.photos IS NOT EMPTY');
        }

        $paginator = $this->get('knp_paginator');
        // Params: 1st - query, 2nd - page number, 3rd - limit per page
        $applicants_paged = $paginator->paginate($qb->getQuery(), $this->get('request')->query->get('page', 1), 10);

        return $this->render('DualizeUserSearchBundle:Search:view.html.twig', array(
                    'form' => $form->createView(),
                    'applicants' => $applicants_paged,
                    'is_saved_search' => $is_saved_search,
        ));
    }

    /**
     * Get list of cities, region and countries when user typing in location field (AJAX)
     */
    public function locationAction(Request $request)
    {
        $response = new Response();
        $location_name = $request->query->get('location_name');

        if ($request->isXmlHttpRequest() && $location_name) {

            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

            $em = $this->getDoctrine()->getManager();
            $max_results = 5;

            $cities = $em->createQueryBuilder()
                    ->select('city', 'region', 'country')
                    ->from('DualizeUserBundle:City', 'city')
                    ->leftJoin('city.region', 'region')
                    ->leftJoin('city.country', 'country')
                    ->where('city.name LIKE :name')
                    ->setParameter('name', $location_name . '%')
                    ->orderBy('city.name', 'ASC')
                    ->setMaxResults($max_results)
                    ->getQuery()
                    ->getArrayResult();

            $regions = $em->createQueryBuilder()
                    ->select('region', 'country')
                    ->from('DualizeUserBundle:Region', 'region')
                    ->leftJoin('region.country', 'country')
                    ->where('region.name LIKE :name')
                    ->setParameter('name', $location_name . '%')
                    ->orderBy('region.name', 'ASC')
                    ->setMaxResults($max_results)
                    ->getQuery()
                    ->getArrayResult();

            $countries = $em->createQueryBuilder()
                    ->select('country')
                    ->from('DualizeUserBundle:Country', 'country')
                    ->where('country.name LIKE :name')
                    ->setParameter('name', $location_name . '%')
                    ->orderBy('country.name', 'ASC')
                    ->setMaxResults($max_results)
                    ->getQuery()
                    ->getArrayResult();

            $locations = array_merge($cities, $regions, $countries);

            $result = array();
            foreach ($locations as $location) {
                $type = 'country';
                if (isset($location['country'])) {
                    $type = 'region';
                }
                if (isset($location['region'])) {
                    $type = 'city';
                }
                array_push($result, array(
                    'id' => $location['id'],
                    'text' => $location['name'],
                    'type' => $type,
                    'region' => (isset($location['region']['name']) ? $location['region']['name'] : ''),
                    'country' => (isset($location['country']['name']) ? $location['country']['name'] : ''),
                ));
            }
            return $response->setContent(json_encode($result));
        }
        return $response->setContent('Wrong request');
    }

    /**
     * Fill unserialized object with full entities
     */
    private function retrieveEntities(&$params)
    {
        $em = $this->getDoctrine()->getManager();

        if ($params->getSociotypeId()) {
            $sociotype = $em->find('DualizeSocioBundle:Sociotype', $params->getSociotypeId());
            $params->setSociotype($sociotype);
        }
    }

}
