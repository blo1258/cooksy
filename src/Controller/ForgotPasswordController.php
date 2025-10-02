<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractController
{
    /**
     * Şifre sıfırlama isteği rotası. Kullanıcıdan e-posta adresini ister.
     * Bu rota, Twig dosyanızdaki `{{ path('app_forgot_password') }}` linkine hizmet eder.
     */
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function request(): Response
    {
        // Gerçek bir uygulamada, burada bir Form oluşturup işleyeceksiniz. 
        // Ancak şimdilik, sadece formu gösteren Twig şablonunu render ediyoruz.

        return $this->render('reset_password/request.html.twig', [
            'controller_name' => 'ForgotPasswordController',
        ]);
    }
}
