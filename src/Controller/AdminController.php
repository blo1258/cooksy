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
use App\Entity\Utilisateur;
use App\Entity\Recette; // Recette Entity'nizin yolu
use App\Form\RecetteType; // Mevcut Form Type'ınızın yolu
use Symfony\Component\HttpFoundation\Request; // Form isteğini yakalamak için

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
    
    #[Route('/{id}/changer-role', name: 'app_admin_users_changer_role', methods: ['GET'])]
    public function changerRole(Utilisateur $utilisateur): Response
    {
        // Mevcut rolleri alır
        $roles = $utilisateur->getRoles();
        
        // Basit bir geçiş mantığı uygulayalım: ROLE_ADMIN varsa kaldır, yoksa ekle.
        if (in_array('ROLE_ADMIN', $roles)) {
            $roles = array_diff($roles, ['ROLE_ADMIN']);
            $this->addFlash('success', sprintf('%s Le rôle déditeur de lutilisateur a été SUPPRIMÉ.', $utilisateur->getNom()));
        } else {
            $roles[] = 'ROLE_ADMIN';
            $this->addFlash('success', sprintf('%s Rôle déditeur AJOUTÉ à lutilisateur nommé.', $utilisateur->getNom()));
        }

        $utilisateur->setRoles(array_unique($roles)); // Tekrar eden rollerden kaçınmak için
        $this->entityManager->flush();

        // Kullanıcı listesine geri yönlendir
        return $this->redirectToRoute('app_admin_users');
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

#[Route('/admin/recettes/modifier/{id}', name: 'app_admin_recette_modifier', methods: ['GET', 'POST'])]
    public function modifierRecette(Request $request, Recette $recette): Response
    {
        // Mevcut RecetteType form sınıfını kullanarak formu oluştur
        $form = $this->createForm(RecetteType::class, $recette);
        
        // Gelen isteği forma bağla (GET veya POST)
        $form->handleRequest($request);

        // Form gönderilmiş ve geçerli ise
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Reçete güncellendiği için bekleyen (attente) durumunu kaldır
            // Bu, admin onayı anlamına gelir.
            $recette->setAttente(false); 

            // Değişiklikleri veritabanına kaydet
            $this->entityManager->flush(); 

            $this->addFlash('success', 'La recette a été modifiée et approuvée par l\'administrateur.');

            // Reçete listesine geri dön
            return $this->redirectToRoute('app_admin_recettes');
        }

        // Formu, mevcut şablonunuzu kullanarak render et
        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form->createView(), // Twig için form nesnesini hazırlar
        ]);
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
