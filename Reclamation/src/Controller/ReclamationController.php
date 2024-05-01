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
use Mpociot\ChuckNorrisJokes\JokeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMailer\PHPMailer\PHPMailer;

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
    
    $jokes = new JokeFactory();
    $joke = $jokes->getRandomJoke();
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
        'joke' => $joke,
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
     // Filtrer les réclamations qui ont dépassé 3 jours
     $reclamations = array_filter($reclamations, function($reclamation) {
        $creationDate = $reclamation->getDaterec();
        $currentDate = new \DateTime();
        $difference = $currentDate->diff($creationDate);
        return $difference->days <= 3;
    });
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
    #[Route('/archivees', name: 'app_reclamation_archivees', methods: ['GET'])]
    public function archivees(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Récupérer le paramètre de triBack et order depuis la requête
        $triBack = $request->query->get('triBack');
        $order = $request->query->get('order');
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();
    
        // Filtrer les réclamations qui ont dépassé 3 jours
        $reclamations = array_filter($reclamations, function($reclamation) {
            $creationDate = $reclamation->getDaterec();
            $currentDate = new \DateTime();
            $difference = $currentDate->diff($creationDate);
            return $difference->days > 3;
        });
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
    
        return $this->render('reclamation/archivees.html.twig', [
            'reclamations' => $paginatedReclamations,
            'knp_pagination' => $paginatedReclamations,
            'statistiques' => $statistiques,
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
            $reclamation->setDescriRec($this->convertTextToEmojis($reclamation->getDescriRec()));
            // Filtrer les mots inappropriés dans la description
            $inappropriateWords = ['bad', 'stupid', 'malade','psycho','putin','con','conne']; // Liste de mots inappropriés
            $descrirecFiltered = $this->filterInappropriateWords($reclamation->getDescriRec(), $inappropriateWords);
            $reclamation->setDescriRec($descrirecFiltered);

            $entityManager->persist($reclamation);
            $entityManager->flush();
 // Créer une nouvelle instance de PHPMailer
 $mail = new PHPMailer(true);
 $mail->SMTPDebug = 2; // Activer le débogage SMTP
$mail->Debugoutput = 'error_log'; // Rediriger la sortie de débogage vers le fichier de log



 try {
     // Paramètres du serveur
    $mail->isSMTP();

    $mail->Host = 'smtp-mail.outlook.com';
    $mail->SMTPAuth = true;
     $mail->Username = 'khalil.chekili@esprit.tn';
     $mail->Password = '211JMT6428@';
     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
     $mail->Port = 587;

     // Destinataires
     $mail->setFrom('khalil.chekili@esprit.tn', 'Service de reclamation');
     $mail->addAddress('khalil.chekili2002@gmail.com');

     // Contenu
     $mail->isHTML(true);
     $user = $reclamation->getIdu();
     $nomUser = $user->getNomUser();
     $prenomUser = $user->getPrenomUser();
     $descrirec = $reclamation->getDescriRec();
     $daterec = $reclamation->getDateRec();
     $categorierec = $reclamation->getCategorieRec();
     $numTel = $user->getNumTel();
     $mail->Subject = 'Réclamation';
     $mail->Body = '
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #00274c; /* Bordure bleu marine */
        }
        .header {
            color: #17447a; /* Bleu */
            font-size: 24px;
            font-weight: bold;
            text-align: center; /* Centrage horizontal */
            margin-bottom: 20px;
            border-bottom: 2px solid #17447a; /* Bordure bleue */
            padding-bottom: 10px; /* Espace sous le titre */
        }
        .content {
            line-height: 1.6;
            color: #00274c; /* Bleu marine */
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
            text-align: center; /* Centrage horizontal */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #00274c; /* Bordure bleu marine */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #00274c; /* Bleu marine */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Réclamation</div>
        <div class="content">
            <p>Cher(e) Administrateur,</p>
            <p>Nous vous informons qu\'une réclamation a été soumise via notre système. Veuillez trouver ci-dessous les détails de la réclamation :</p>
            <table>
                <tr>
                    <th>Nom et prénom</th>
                    <td>' . $nomUser . ' ' . $prenomUser . '</td>
                </tr>
                <tr>
                    <th>Description de la réclamation</th>
                    <td>' . $descrirec . '</td>
                </tr>
                <tr>
                    <th>Catégorie de la réclamation</th>
                    <td>' . $categorierec . '</td>
                </tr>
                <tr>
                    <th>Numéro de téléphone</th>
                    <td>' . $numTel . '</td>
                </tr>
                <tr>
                    <th>Date du réclamation</th>
                    <td>' . $daterec->format('Y-m-d') . '</td>
                </tr>
            </table>
            <p>Veuillez prendre les mesures nécessaires pour examiner et traiter cette réclamation dans les plus brefs délais. Nous vous remercions de votre attention et de votre diligence dans ce processus.</p>
        </div>
        <div class="footer">Cordialement,<br>Service de Réclamation</div>
    </div>
</body>
</html>';

     
     
     
     
 
     // Envoyer l'email
     $mail->send();
 } catch (Exception $e) {
     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
 }
 


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
            $reclamation->setDescriRec($this->convertTextToEmojis($reclamation->getDescriRec()));
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
    
    private function convertTextToEmojis(string $text): string
    {
        // Liste de correspondance entre les emojis texte et leurs représentations visuelles
        $emojiMap = [
            ':)' => '😊',
            ':(' => '😢',
            ':D' => '😄',
            '(y)' => '👍', 
             '<3' => '❤️',
            
        ];

        // Remplacez chaque emoji texte par son équivalent visuel dans le texte
        foreach ($emojiMap as $textEmoji => $visualEmoji) {
            $text = str_replace($textEmoji, $visualEmoji, $text);
        }

        return $text;
    }
   
}