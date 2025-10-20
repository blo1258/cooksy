<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\Utilisateur; 
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
// Gerekli importlar:
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/recette')]
class RecetteController extends AbstractController
{
    #[Route('/', name: 'app_recette_index', methods: ['GET'])]
    public function index(Request $request, RecetteRepository $recetteRepository): Response
    {
        $query = $request->query->get('q');
        
        if ($query) {
            $recettes = $recetteRepository->findByQuery($query);
        } else {
            $recettes = $recetteRepository->findAll();
        }

        // --- FIL D'ARIANE (Breadcrumb) AJOUTÉ ---
        $breadcrumb = [
            ['label' => 'Accueil', 'route' => 'app_accueil'],
            ['label' => 'Toutes les Recettes', 'route' => 'app_recette_index'],
        ];

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[Route('/new', name: 'app_recette_ajouter', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette->setAuteur($this->getUser());
            $recette->setCreatedAt(new \DateTimeImmutable());
            
            // Gestion du téléchargement de l'image (new)
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('recettes_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                    return $this->redirectToRoute('app_recette_ajouter');
                }

                $recette->setImage($newFilename);
            }

            $entityManager->persist($recette);
            $entityManager->flush();
            $this->addFlash('success', 'Recette ajoutée avec succès.');

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    // Commentaire yönetimi bu fonskiyonu kullanır
    #[Route('/{id}', name: 'app_recette_detail', methods: ['GET'])]
    public function show(Recette $recette, CommentaireRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findBy(
        ['recette' => $recette],
        ['createdAt' => 'DESC'] 
        );

        return $this->render('recette/show.html.twig', [
            'recette' => $recette,
            'commentaires' => $commentaires,
        ]);
    }

    // SADECE BU KISIMDAKİ LOGİK İSTENMİŞTİ
    #[Route('/{id}/edit', name: 'app_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $oldImageName = $recette->getImage();
        $form = $this->createForm(RecetteType::class, $recette);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            // Gère le téléchargement d'une nouvelle image lors de la modification
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/images/recettes';
                    $imageFile->move(
                        $uploadDirectory,
                        $newFilename
                    );
                    
                    // 2. Entity'deki görsel adını yeni adla güncelle
                    $recette->setImage($newFilename);
                    
                    // 3. Eski görseli silme (Opsiyonel ama tavsiye edilir)
                    if ($oldImageName) {
                        $oldImagePath = $uploadDirectory . '/' . $oldImageName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                } catch (FileException $e) {
                    // Dosya yüklenirken bir hata oluşursa
                    $this->addFlash('error', "Une erreur s'est produite lors du chargement de l'image: " . $e->getMessage());
                    // Hata oluştuğu için Entity'yi kaydetmeyi durdurabiliriz
                    return $this->redirectToRoute('app_recette_edit', ['id' => $recette->getId()]);
                }
            } else {
                $recette->setImage($oldImageName);
            }

             $entityManager->flush();    
            $this->addFlash('success', 'Recette mise à jour avec succès.');


            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
            $this->addFlash('success', 'Recette supprimée avec succès.');
        }

        return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/populaires', name: 'app_recettes_populaires')]
    public function populaires(RecetteRepository $recetteRepository): Response
    {
        $recettes = $recetteRepository->findRecettesPopulaires();

        return $this->render('recette/populaires.html.twig', [
            'recettes' => $recettes,
        ]);
    }


    /**
     * Aime ou retire l'appréciation d'une recette.
     */
    #[Route('/{id}/like', name: 'recette_like', methods: ['POST'])]
    public function like(Recette $recette, EntityManagerInterface $manager): JsonResponse
    {
        // 1. Contrôle de l'utilisateur
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Vous devez vous connecter.'], 403);
        }

        // Sécurité: Assurer que l'objet utilisateur est du type attendu
        if (!($user instanceof Utilisateur)) {
             return $this->json(['message' => 'Type d\'utilisateur non valide.'], 500);
        }

        // 2. Contrôle du statut de l'appréciation (Like)
        if ($recette->getLikes()->contains($user)) {
            // L'utilisateur a déjà aimé -> Retirer le like
            $recette->removeLike($user);
            $isLiked = false;
        } else {
            // L'utilisateur n'a pas aimé -> Ajouter le like
            $recette->addLike($user);
            $isLiked = true;
        }

        // 3. Sauvegarder les changements
        $manager->flush();

        // 4. Réponse JSON réussie
        return $this->json([
            'message' => 'Statut du like mis à jour avec succès.',
            'likesCount' => $recette->getLikes()->count(),
            'isLiked' => $isLiked
        ], 200);
    }

}
