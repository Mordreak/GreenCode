<?php

namespace GC\MainBundle\Controller;

use GC\MainBundle\Entity\Dentist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    const AVAILABLE_OPEN_DAYS = array(
        'mon' => 'monday',
        'tue' => 'tuesday',
        'wed' => 'wednesday',
        'thu' => 'thursday',
        'fri' => 'friday',
        'sat' => 'saturday',
        'sun' => 'sunday'
    );

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('GCMainBundle:Default:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $query    = $request->query->get('q');
        $page     = intval($request->query->get('p', 1));
        $openDays = $request->query->get('days');
        $openHour = $request->query->get('hour');

        $openDays = $openDays ? $openDays: array();

        $page = $page > 0 ? $page : 1;

        if (is_string($openDays)) {
            $openDays = array($openDays);
        }
        $openDays = !empty($openDays) ? array_values(
            array_intersect_key(self::AVAILABLE_OPEN_DAYS, array_flip($openDays))
        ) : array();

        $openHour = intval(trim(strtolower(str_replace(':', '', $openHour))));
        $openHour = $openHour ?: null;

        $dentistRepository = $this->getDoctrine()->getRepository(Dentist::class);

        $results = $dentistRepository->searchFromCriteria($this->get('memcache.default'), $query, $page, $openDays, $openHour);

        return $this->render('GCMainBundle:Default:search.html.twig', compact(
            'results', 'query', 'page'
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

        $dentist = $this->get('memcache.default')->get($dentist_id);
        if ($dentist === false) {
            $dentist = $dentistRepository->find($dentist_id);
            $this->get('memcache.default')->set($dentist_id, $dentist, 0, 345600);
        }

        return $this->render('GCMainBundle:Default:detail.html.twig', array('dentist' => $dentist));
    }
}
