<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Entity\Utilisateur;
use App\Form\ChangePasswordFormType;
use App\Form\EditProfileFormType; // Ajouté pour le nouveau formulaire
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[IsGranted('ROLE_USER')]
final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion.
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les recettes et les commentaires de l'utilisateur.
        // Ce code charge automatiquement les collections de recettes et de commentaires de l'utilisateur.
        $recettes = $user->getRecettes();
        $commentaires = $user->getCommentaires();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'recettes' => $recettes,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/profil/ajouter-recette', name: 'app_ajouter_recette')]
    public function ajouterRecette(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir l'auteur de la recette comme l'utilisateur actuel.
            $recette->setUtilisateur($this->getUser());

            // Gérer le fichier image.
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // Traiter uniquement si un fichier a été téléchargé.
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception en cas d'erreur de téléchargement.
                }
                $recette->setImage($newFilename);
            }

            $em->persist($recette);
            $em->flush();

            $this->addFlash('success', 'Recette ajoutée avec succès !');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/ajouter_recette.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil/changer-mot-de-passe', name: 'app_changer_mot_de_passe')]
    public function changerMotDePasse(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if ($passwordHasher->isPasswordValid($user, $oldPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $em->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                return $this->redirectToRoute('app_profil');
            } else {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
            }
        }

        return $this->render('profil/changer_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil/editer', name: 'app_edit_profile')]
    public function editProfile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Créer un formulaire pour la modification du profil
        $form = $this->createForm(EditProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement d'image de profil si un fichier est présent
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Créer un nom de fichier unique et sécurisé
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // Déplacer le fichier vers le répertoire de destination
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFilename
                    );
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si le déplacement du fichier échoue
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // Enregistrer les modifications dans la base de données
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profil/supprimer', name: 'app_supprimer_compte')]
    public function supprimerCompte(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Trouver et supprimer toutes les recettes de l'utilisateur.
        $recettes = $em->getRepository(Recette::class)->findBy(['utilisateur' => $user]);

        foreach ($recettes as $recette) {
            $em->remove($recette);
        }

        // Enfin, supprimer l'utilisateur.
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('app_accueil');
    }
}
