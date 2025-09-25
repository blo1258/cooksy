<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\RecetteRepository; // Bu depo da gerekecek
use App\Repository\CommentaireRepository; // Bu depo da gerekecek
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $recetteRepository;
    private $commentaireRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        RecetteRepository $recetteRepository,
        CommentaireRepository $commentaireRepository
    ) {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->recetteRepository = $recetteRepository;
        $this->commentaireRepository = $commentaireRepository;
    }

    #[Route('/admin', name: 'app_admin_index')]
    public function index(): Response
    {
        $totalUtilisateurs = $this->utilisateurRepository->count([]);
        $totalRecettes = $this->recetteRepository->count([]);
        $totalCommentaires = $this->commentaireRepository->count([]);
        $dernieresRecettes = $this->recetteRepository->findBy([], ['id' => 'DESC'], 5);
        $recettesAttente = $this->recetteRepository->count(['attente' => true]);

        return $this->render('admin/index.html.twig', [
            'totalUtilisateurs' => $totalUtilisateurs,
            'totalRecettes' => $totalRecettes,
            'totalCommentaires' => $totalCommentaires,
            'dernieresRecettes' => $dernieresRecettes,
            'recettesAttente' => $recettesAttente,
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function manageUsers(): Response
    {
        $utilisateurs = $this->utilisateurRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }
    
    #[Route('/admin/users/supprimer/{id}', name: 'app_admin_users_supprimer')]
    public function supprimerUtilisateur(int $id, UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $utilisateur = $this->utilisateurRepository->find($id);

        if ($utilisateur) {
            $this->entityManager->remove($utilisateur);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Utilisateur non trouvé.');
        }

        return new RedirectResponse($urlGenerator->generate('app_admin_users'));
    }
    
    #[Route('/admin/users/changer-role/{id}', name: 'app_admin_users_changer_role')]
    public function changerRoleUtilisateur(int $id): Response
    {
        // La logique de changement de rôle ira ici
        return new Response("Page de changement de rôle (en construction)");
    }

    #[Route('/admin/users/valider/{id}', name: 'app_admin_users_valider')]
    public function validerUtilisateur(int $id): Response
    {
        // La logique de validation de l'utilisateur ira ici
        return new Response("Page de validation de l'utilisateur (en construction)");
    }

    #[Route('/admin/recettes', name: 'app_admin_recettes')]
    public function manageRecettes(): Response
    {
        $recettes = $this->recetteRepository->findAll();

        return $this->render('admin/recettes.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[Route('/admin/commentaires', name: 'app_admin_commentaires')]
    public function manageCommentaires(): Response
    {
        $commentaires = $this->commentaireRepository->findAll();
        
        return $this->render('admin/commentaires.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/admin/recettes/supprimer/{id}', name: 'app_admin_recette_supprimer')]
public function supprimerRecette(int $id, RecetteRepository $recetteRepository, EntityManagerInterface $em): RedirectResponse
{
    $recette = $recetteRepository->find($id);

    if ($recette) {
        $em->remove($recette);
        $em->flush();
        $this->addFlash('success', 'La recette a été supprimée avec succès.');
    } else {
        $this->addFlash('error', 'Recette non trouvée.');
    }

    return $this->redirectToRoute('app_admin_recettes');
}

#[Route('/admin/recettes/modifier/{id}', name: 'app_admin_recette_modifier')]
public function modifierRecette(int $id, RecetteRepository $recetteRepository): Response
{
    $recette = $recetteRepository->find($id);

    if (!$recette) {
        throw $this->createNotFoundException('Recette non trouvée.');
    }

    // Burada normalde bir FormType kullanarak düzenleme yapılır.
    // İlk etapta basit test için sadece id gösterelim:
    return new Response("Page de modification pour la recette #".$recette->getId());
}

#[Route('/admin/recettes/attente', name: 'app_admin_recettes_attente')]
public function recettesEnAttente(): Response
{
    $recettes = $this->recetteRepository->findBy(['attente' => true]);

    return $this->render('admin/recettes_attente.html.twig', [
        'recettes' => $recettes,
    ]);
}

#[Route('/admin/recette/{id}/valider', name: 'app_admin_valider_recette')]
public function validerRecette(int $id): Response
{
    $recette = $this->recetteRepository->find($id);

    if ($recette) {
        $recette->setAttente(false); // artık onaylandı
        $this->entityManager->flush();
    }

    return $this->redirectToRoute('app_admin_recettes_attente');
}

#[Route('/admin/recette/{id}/refuser', name: 'app_admin_refuser_recette')]
public function refuserRecette(int $id): Response
{
    $recette = $this->recetteRepository->find($id);

    if ($recette) {
        $this->entityManager->remove($recette); // tamamen sil
        $this->entityManager->flush();
    }

    return $this->redirectToRoute('app_admin_recettes_attente');
}


}
