<?php

namespace App\Controller;
use Qirolab\Laravel\Reactions\Contracts\Reactable;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\CommentaireRepository;
use App\Repository\ReclamationRepository;
use App\Entity\User;
use App\Entity\Reclamation;
#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    
public function index(Request $request,CommentaireRepository $commentaireRepository, ReclamationRepository $reclamationRepository, PaginatorInterface $paginator): Response
{
    // Récupérer le paramètre de tri depuis la requête
    $tri = $request->query->get('tri');
        $order = $request->query->get('order');
    $defaultUser = $this->getDoctrine()->getRepository(User::class)->find(22);
    $reclamations = $reclamationRepository->findBy(['idu' => $defaultUser]);
    $reclamationIds = array_map(function($reclamation) { return $reclamation->getIdrec(); }, $reclamations);
    $commentaires = $commentaireRepository->findBy(['idrec' => $reclamationIds]);
     
    // Effectuez le tri en fonction du paramètre
    if ($tri === 'date') {
        // Tri par date
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * ($a->getDatecom() <=> $b->getDatecom());
        });
   
    } elseif ($tri === 'description') {
        // Tri par description de la réclamation associée
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getIdrec()->getDescrirec(), $b->getIdrec()->getDescrirec());
        });
    }
     elseif ($tri === 'contenucom') {
        // Tri par statut
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getContenucom(), $b->getContenucom());
        });
    }
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
   public function indexBack(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
{
    $triBack = $request->query->get('triBack');
    $order = $request->query->get('order');

    // Récupération des commentaires depuis la base de données
    $commentaires = $entityManager
        ->getRepository(Commentaire::class)
        ->findAll();

    // Effectuer le tri en fonction du paramètre
    if ($triBack === 'date') {
        // Tri par date
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * ($a->getDatecom() <=> $b->getDatecom());
        });
    } elseif ($triBack === 'description') {
        // Tri par description de la réclamation associée
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getIdrec()->getDescrirec(), $b->getIdrec()->getDescrirec());
        });
    } elseif ($triBack === 'contenucom') {
        // Tri par contenu du commentaire
        usort($commentaires, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getContenucom(), $b->getContenucom());
        });
    }

    // Pagination des commentaires triés
    $currentPage = $request->query->getInt('page', 1); 
    $perPage = 6; 

    $paginatedCommentaires = $paginator->paginate(
        $commentaires,
        $currentPage,
        $perPage
    );

    return $this->render('commentaire/indexBack.html.twig', [
        'commentaires' => $paginatedCommentaires,
        'knp_pagination' => $paginatedCommentaires,
    ]);
}


    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository  ,CommentaireRepository $commentaireRepository, ReclamationRepository $reclamationRepository,EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        // Récupérer l'utilisateur avec l'id 22
    $user = $userRepository->find(22);
    
    // Récupérer la réclamation associée à l'utilisateur
    $reclamation = $reclamationRepository->findOneBy(['idu' => $user]);
    
    // Définir la réclamation pour le commentaire
       $commentaire->setIdrec($reclamation);
       $reclamation->setIdu($user);
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