<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentaireController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/recette/{id}/commentaire/add', name: 'app_commentaire_add', methods: ['POST'])]
    public function add(
        Recette $recette,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $contenu = $request->request->get('contenu');

        if (!empty($contenu)) {
            $commentaire = new Commentaire();
            $commentaire->setContenu($contenu);
            $note = $request->request->get('note');
                if (!empty($note)) {
                    $noteInteger = (int) $note;
                } else {
                    $noteInteger = null;
                }
            $commentaire->setNote($noteInteger);
            $commentaire->setUtilisateur($this->getUser());
            $commentaire->setRecette($recette);
            $commentaire->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');
        } else {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
        }

        return $this->redirectToRoute('app_recette_detail', ['id' => $recette->getId()]);
    }
}
