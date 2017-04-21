<?php

namespace db4y\CasBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CasController extends Controller
{
    /**
     * @Route("/logout", name="db4y_cas.logout", methods={"GET"})
     */
    public function logoutAction(Request $request)
    {
        $this->get('db4y_cas.cas_authenticator')->logout(
            $request->query->get('service', null)
        );
    }

    /**
     * @Route("/restricted", name="db4y_cas.restricted", methods={"GET"})
     */
    public function restrictedAction()
    {
        $response = new Response(null, Response::HTTP_FORBIDDEN);

        return $this->render('@db4yCas/restricted.html.twig', [], $response);
    }
}
