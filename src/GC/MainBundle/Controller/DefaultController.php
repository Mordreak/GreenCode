<?php

namespace GC\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $working = false;
        $this->get('memcache.default')->set('someKey', true, 0, 345600);
        $working = $this->get('memcache.default')->get('someKey');
        return $this->render('GCMainBundle:Default:index.html.twig', array(
            'working' => $working
        ));
    }
}
