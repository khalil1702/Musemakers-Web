<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Twilio\Rest\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();
            $statistiques = $this->calculerStatistiques($reclamations);
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'statistiques' => $statistiques, // Passage des statistiques au template
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
    public function indexBack(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();
    
        // Calcul des statistiques
        $statistiques = $this->calculerStatistiques($reclamations);
    
        return $this->render('reclamation/indexBack.html.twig', [
            'reclamations' => $reclamations,
            'statistiques' => $statistiques, // Passage des statistiques au template
        ]);
    }
    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $reclamation = new Reclamation();
    $reclamation->setStatutrec('En Cours'); // Utilisation de la méthode setStatutrec
    $reclamation->setDaterec(new DateTime()); // Définition de la date par défaut
    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/{idrec}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        $sid    = "key";
        $token  = "key";
        $twilio = new Client($sid, $token);

        $user = $reclamation->getIdu();

         // Récupérer le nom et le prénom de l'utilisateur
        $nomUser = $user->getNomUser();
        $prenomUser = $user->getPrenomUser();
        $descrirec = $reclamation->getDescriRec();
        $statutrec = $reclamation->getStatutRec();
    


        // Envoi du SMS
        $message = $twilio->messages
            ->create("+21627163524", // destinataire
                array(
                    "from" => "+18154733136",
                    "body" => "Cher(e) $nomUser $prenomUser\nLe statutrec de ta réclamation : $descrirec est : $statutrec"
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
    
}