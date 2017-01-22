<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows/{id}", name="shows")
     * @Template()
     */
    public function showsAction($id)
    {
        $maxShowsByPage = 4;
        $offset = ($id-1)*$maxShowsByPage;

        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        $shows = $repo->findBy(
            array(),
            array('name' => 'asc'),
            $maxShowsByPage,
            $offset
        );

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder();
        $query->select('count(show.id) as somme')
            ->from('AppBundle:TVShow', 'show');

        $resNb = $query->getQuery()->getSingleScalarResult();

        $endPage = ceil($resNb / $maxShowsByPage);
        
        return [
            'shows' => $shows,
            'page' => $id,
            'endPage' => $endPage,
        ];
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        return [
            'show' => $repo->find($id)
        ];        
    }

    /**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $search = $request->request->get('search');
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder();
            $query
                ->select('show')
                ->from('AppBundle:TVShow', 'show')
                ->where('show.name LIKE :textSearch OR show.synopsis LIKE :textSearch')
                ->setParameter('textSearch', '%'.$search.'%');
            $shows = $query->getQuery()->getResult();
        }
        return $this->render('AppBundle:Default:shows.html.twig', array(
            'shows' => $shows
        ));
    }

    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        $date = new \DateTime();
        $em = $this->getDoctrine()->getManager();
        $query_episodes_by_date = $em->createQueryBuilder();
        $query_episodes_by_date->select('episode')
            ->from('AppBundle:Episode', 'episode')
            ->where('episode.date >= :date')
            ->orderBy('episode.date', 'ASC')
            ->setParameter('date', $date);

        $episodes_by_date = $query_episodes_by_date->getQuery()->getResult();

        return [
            'episodes' => $episodes_by_date
        ];
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return [];
    }
}
