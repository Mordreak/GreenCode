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
        $this->get('memcache.default')->set('someKey', true, 0, 345600);
        $working = $this->get('memcache.default')->get('someKey');

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
        $query = $request->query->get('q');

        $dentistRepository = $this->getDoctrine()->getRepository(Dentist::class);

        $searchQuery = $dentistRepository->createQueryBuilder('d')
            ->where('d.firstname LIKE :query OR d.lastname LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery();

        $results = $searchQuery->getResult();

        return $this->render('GCMainBundle:Default:search.html.twig', compact(
            'results', 'query'
        ));
    }
}
