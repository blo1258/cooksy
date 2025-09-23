<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CuisinesController extends AbstractController
{
    /**
     * Renders the Cuisines page.
     * The #[Route('/cuisines', name: 'app_cuisines_index')] annotation
     * defines the URL path and the route name for this page.
     * This is what connects the URL to the template.
     */
    #[Route('/cuisines', name: 'app_cuisines_index')]
    public function index(): Response
    {
        // This method renders the cuisines/index.html.twig template.
        // It passes no data to the template, as the page is static for now.
        return $this->render('cuisines/index.html.twig', [
            // No variables are needed for this page.
        ]);
    }
}