<?php

namespace GC\MainBundle\Controller;

use GC\MainBundle\Entity\Dentist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $working = false;
        // $this->get('memcache.default')->set('someKey', true, 0, 345600);
        // $working = $this->get('memcache.default')->get('someKey');

        return $this->render('GCMainBundle:Default:index.html.twig', array(
            'working' => $working
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        // Can be a first name, a last name, an address or a city
        $query = $request->query->get('q');

        // Day of opening
        $openDays          = $request->query->get('days');
        $availableOpenDays = [
            'mon' => 'monday',
            'tue' => 'tuesday',
            'wed' => 'wednesday',
            'thu' => 'thursday',
            'fri' => 'friday',
            'sat' => 'saturday',
            'sun' => 'sunday',
        ];

        $openDays = !empty($openDays) ? array_values(array_intersect_key($availableOpenDays, array_flip($openDays))) : null;

        // Hour of opening
        $openHour = $request->query->get('hour');
        $openHour = intval(trim(strtolower(str_replace(':', '', $openHour))));
        $openHour = $openHour ?: null;

        $dentistRepository = $this->getDoctrine()->getRepository(Dentist::class);

        $searchQuery = $dentistRepository->searchFromCriteria($query, $openDays, $openHour);

        $results = $searchQuery->getResult();

        return $this->render('GCMainBundle:Default:search.html.twig', compact(
            'results', 'query'
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Request $request, $dentist_id)
    {
        $dentistRepository = $this->getDoctrine()->getRepository(Dentist::class);
        $dentist = $dentistRepository->find($dentist_id);

        return $this->render('GCMainBundle:Default:detail.html.twig', array('dentist' => $dentist));
    }
}
