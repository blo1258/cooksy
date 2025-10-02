<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * Renders the contact page template.
     * This route name must match the one used in your Twig file: 'app_contact_index'
     * * @Route('/contact', name='app_contact_index)
     */
    #[Route('/contact', name: 'app_contact_index')]
    public function index(): Response
    {
        // Twig şablonunuzu render et
        // Buradaki yol, sizin templates/contact/index.html.twig dosyanızla eşleşmelidir.
        return $this->render('contact/index.html.twig', [
            // Gerekli verileri buraya aktarabilirsiniz
        ]);
    }
}