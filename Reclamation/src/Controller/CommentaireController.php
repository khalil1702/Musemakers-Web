<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(Request $request,EntityManagerInterface $entityManager,PaginatorInterface $paginator): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();
             // Pagination logic
    $currentPage = $request->query->getInt('page', 1); 
    $perPage = 6; 

    $paginatedCommentaires = $paginator->paginate(
        $commentaires,
        $currentPage,
        $perPage
    );

        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaires,
            'commentaires' => $paginatedCommentaires, // Use paginated reclamations
        'knp_pagination' => $paginatedCommentaires,
        ]);
    }
    #[Route('/back', name: 'app_commentaire_indexBack', methods: ['GET'])]
    public function indexBack(Request $request,EntityManagerInterface $entityManager,PaginatorInterface $paginator): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();
                   // Pagination logic
    $currentPage = $request->query->getInt('page', 1); 
    $perPage = 6; 

    $paginatedCommentaires = $paginator->paginate(
        $commentaires,
        $currentPage,
        $perPage
    );

        return $this->render('commentaire/indexBack.html.twig', [
            'commentaires' => $paginatedCommentaires, // Use paginated reclamations
            'knp_pagination' => $paginatedCommentaires,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setDatecom(new DateTime()); // Définition de la date par défaut
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             
             $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
             $contenucomFiltered = $this->filterInappropriateWords($commentaire->getContenucom(), $inappropriateWords);
             $commentaire->setContenucom($contenucomFiltered);
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }
    #[Route('/newBack', name: 'app_commentaire_newBack', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setDatecom(new DateTime()); // Définition de la date par défaut
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             
             $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
             $contenucomFiltered = $this->filterInappropriateWords($commentaire->getContenucom(), $inappropriateWords);
             $commentaire->setContenucom($contenucomFiltered);
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_indexBack', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/newBack.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{idcom}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }
    #[Route('/{idcom}/Back', name: 'app_commentaire_showBack', methods: ['GET'])]
    public function showBack(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/showBack.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{idcom}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
             $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
             $contenucomFiltered = $this->filterInappropriateWords($commentaire->getContenucom(), $inappropriateWords);
             $commentaire->setContenucom($contenucomFiltered);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }
    #[Route('/{idcom}/editBack', name: 'app_commentaire_editBack', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
             $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
             $contenucomFiltered = $this->filterInappropriateWords($commentaire->getContenucom(), $inappropriateWords);
             $commentaire->setContenucom($contenucomFiltered);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_indexBack', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/editBack.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{idcom}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getIdcom(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{idcom}/Back', name: 'app_commentaire_deleteBack', methods: ['POST'])]
    public function deleteBack(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getIdcom(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_indexBack', [], Response::HTTP_SEE_OTHER);
    }
    private function filterInappropriateWords($text, $inappropriateWords) {
        foreach ($inappropriateWords as $word) {
            $text = preg_replace("/\b$word\b/i", str_repeat('*', mb_strlen($word)), $text);
        }
        return $text;
    }
    
}