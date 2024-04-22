<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Twilio\Rest\Client;
use Symfony\Component\HttpFoundation\JsonCommentaire;
use App\Repository\ReclamationRepository;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository, PaginatorInterface $paginator): Response
{
    // Récupérer le paramètre de tri depuis la requête
    $tri = $request->query->get('tri');
        $order = $request->query->get('order');
    $defaultUser = $this->getDoctrine()->getRepository(User::class)->find(22);
    $reclamations = $reclamationRepository->findBy(['idu' => $defaultUser]);
    
    
    // Effectuez le tri en fonction du paramètre
    if ($tri === 'date') {
        // Tri par date
        usort($reclamations, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * ($a->getDaterec() <=> $b->getDaterec());
        });
    } elseif ($tri === 'categorie') {
        // Tri par catégorie
        usort($reclamations, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getCategorierec(), $b->getCategorierec());
        });
    } elseif ($tri === 'statut') {
        // Tri par statut
        usort($reclamations, function($a, $b) use ($order) {
            return ($order === 'desc' ? -1 : 1) * strcmp($a->getStatutrec(), $b->getStatutrec());
        });
    }

    // Pagination logic
    $currentPage = $request->query->getInt('page', 1); 
    $perPage = 6; 

    $paginatedReclamations = $paginator->paginate(
        $reclamations,
        $currentPage,
        $perPage
    );
    
    return $this->render('reclamation/index.html.twig', [
        'reclamations' => $paginatedReclamations, // Use paginated reclamations
        'knp_pagination' => $paginatedReclamations,
        
    ]);
}

    
    // Méthode pour calculer les statistiques
    private function calculerStatistiques($reclamations)
    {
        $statistiques = [
            'statutrec' => [],
            'categorierec' => [],
        ];
    
        foreach ($reclamations as $reclamation) {
            $statutrec = $reclamation->getStatutrec();
            $categorierec = $reclamation->getCategorierec();
    
            if (!isset($statistiques['statutrec'][$statutrec])) {
                $statistiques['statutrec'][$statutrec] = 0;
            }
            $statistiques['statutrec'][$statutrec]++;
    
            if (!isset($statistiques['categorierec'][$categorierec])) {
                $statistiques['categorierec'][$categorierec] = 0;
            }
            $statistiques['categorierec'][$categorierec]++;
        }
    
        return $statistiques;
    }

    #[Route('/back', name: 'app_reclamation_indexBack', methods: ['GET'])]
    public function indexBack(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Récupérer le paramètre de triBack et order depuis la requête
        $triBack = $request->query->get('triBack');
        $order = $request->query->get('order');
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();
    
        // Effectuez le triBack en fonction du paramètre
        if ($triBack === 'date') {
            // Tri par date
            usort($reclamations, function($a, $b) use ($order) {
                return ($order === 'desc' ? -1 : 1) * ($a->getDaterec() <=> $b->getDaterec());
            });
        } elseif ($triBack === 'categorie') {
            // Tri par catégorie
            usort($reclamations, function($a, $b) use ($order) {
                return ($order === 'desc' ? -1 : 1) * strcmp($a->getCategorierec(), $b->getCategorierec());
            });
        } elseif ($triBack === 'statut') {
            // Tri par statut
            usort($reclamations, function($a, $b) use ($order) {
                return ($order === 'desc' ? -1 : 1) * strcmp($a->getStatutrec(), $b->getStatutrec());
            });
        }
    
        // Calcul des statistiques
        $statistiques = $this->calculerStatistiques($reclamations);
    
        // Pagination logic
        $currentPage = $request->query->getInt('page', 1); 
        $perPage = 6; 
    
        $paginatedReclamations = $paginator->paginate(
            $reclamations,
            $currentPage,
            $perPage
        );
    
        return $this->render('reclamation/indexBack.html.twig', [
            'reclamations' => $reclamations,
            'statistiques' => $statistiques, // Passage des statistiques au template
            'reclamations' => $paginatedReclamations, // Use paginated reclamations
            'knp_pagination' => $paginatedReclamations,
        ]);
    }
    
   
    

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReclamationRepository $reclamationRepository,EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $defaultUser = $this->getDoctrine()->getRepository(User::class)->find(22);
    $reclamations = $reclamationRepository->findBy(['idu' => $defaultUser]);
    $reclamation->setIdu($defaultUser);
        $reclamation->setStatutrec('En Cours');
        $reclamation->setDaterec(new DateTime());
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filtrer les mots inappropriés dans la description
            $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
            $descrirecFiltered = $this->filterInappropriateWords($reclamation->getDescriRec(), $inappropriateWords);
            $reclamation->setDescriRec($descrirecFiltered);

            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

        return $this->renderForm('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
    

    #[Route('/{idrec}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
    #[Route('/{idrec}/Back', name: 'app_reclamation_showBack', methods: ['GET'])]
    public function showBack(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/showBack.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idrec}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filtrer les mots inappropriés dans la description
            $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; 
            $descrirecFiltered = $this->filterInappropriateWords($reclamation->getDescriRec(), $inappropriateWords);
            $reclamation->setDescriRec($descrirecFiltered);

            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
    #[Route('/{idrec}/editBack', name: 'app_reclamation_editBack', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
        require_once __DIR__.'/../../vendor/autoload.php';



        // Remplacez SID et AuthToken par vos informations Twilio
        
      //  $sid    = "key";
        //$token  = "key";
          
        $twilio = new Client($sid, $token);

        $user = $reclamation->getIdu();

         // Récupérer le nom et le prénom de l'utilisateur
        $nomUser = $user->getNomUser();
        $prenomUser = $user->getPrenomUser();
        $descrirec = $reclamation->getDescriRec();
        $statutrec = $reclamation->getStatutRec();
        $numTel = $user->getNumTel();
    


        // Envoi du SMS
        $message = $twilio->messages
            ->create( "+216$numTel", // destinataire
                array(
                    "from" => "+18154733136",
                    "body" => "Cher(e) $nomUser $prenomUser\nLe statut de ta réclamation : $descrirec est : $statutrec"
                )
            );

        // Print SID pour vérification
        print($message->sid);
            // Redirection après envoi du SMS
            return $this->redirectToRoute('app_reclamation_indexBack', [], Response::HTTP_SEE_OTHER);
        }
    
        // Rendre la vue Twig pour l'édition du formulaire
        return $this->render('reclamation/editBack.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }
      
    
    #[Route('/{idrec}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getIdrec(), $request->request->get('_token'))) {
            
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{idrec}/Back', name: 'app_reclamation_deleteBack', methods: ['POST'])]
    public function deleteBack(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getIdrec(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_indexBack', [], Response::HTTP_SEE_OTHER);
    }
    private function filterInappropriateWords($text, $inappropriateWords) {
        foreach ($inappropriateWords as $word) {
            $text = preg_replace("/\b$word\b/i", str_repeat('*', mb_strlen($word)), $text);
        }
        return $text;
    }
    
   
}