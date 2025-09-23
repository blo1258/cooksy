<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * Renders the About page.
     * The #[Route('/a-propos', name: 'app_about_index')] annotation
     * defines the URL path and the route name for this page.
     */
    #[Route('/a-propos', name: 'app_about_index')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            // No variables are needed for this page.
        ]);
    }
}
