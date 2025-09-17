<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AstucesController extends AbstractController
{
    /**
     * Renders the Astuces (Tips) page.
     * The #[Route('/astuces', name: 'app_astuces_index')] annotation
     * defines the URL path and the route name for this page.
     * This is the missing piece that caused the error.
     */
    #[Route('/astuces', name: 'app_astuces_index')]
    public function index(): Response
    {
        // This method renders the astuces/index.html.twig template.
        // It passes no data to the template, as the page is static for now.
        return $this->render('astuces/index.html.twig', [
            // No variables are needed for this page.
        ]);
    }
}
