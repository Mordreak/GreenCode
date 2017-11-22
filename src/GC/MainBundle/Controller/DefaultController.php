<?php

namespace GC\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $working = false;
        $this->get('memcache.default')->set('someKey', 'someValue', 0, true);
        $working = $this->get('memcache.default')->get('someKey');
        return $this->render('GCMainBundle:Default:index.html.twig', array(
            'working' => $working
        ));
    }
}
