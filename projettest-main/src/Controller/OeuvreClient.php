<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreType;
use App\Repository\OeuvreRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Controller\AvisController;
use App\Entity\Avis;

#[Route('/oeuvreclient')]
class OeuvreClient extends AbstractController
{
     
    
    #[Route('/getalloeuvres', name: 'app4_oeuvre_index', methods: ['GET'])]
    public function getall2(OeuvreRepository $oeuvreRepository,  AvisRepository $avisRepository): Response
    {
        $oeuvres = $oeuvreRepository->findAll();

        // Récupérer toutes les œuvres
    $oeuvres = $oeuvreRepository->findAll();
    
    // Tableau pour stocker les moyennes de notes des œuvres
    $averageRatings = [];
    
    // Parcourir chaque œuvre pour calculer la moyenne des notes
    foreach ($oeuvres as $oeuvre) {
        // Récupérer les avis correspondant à l'œuvre
        $avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);
        
        // Initialiser la somme des notes et le nombre d'avis
        $totalRating = 0;
        $numberOfRatings = count($avis);
        
        // Calculer la somme des notes
        foreach ($avis as $avi) {
            $totalRating += $avi->getNote();
        }
        
        // Calculer la moyenne des notes
        $averageRating = $numberOfRatings > 0 ? $totalRating / $numberOfRatings : 0;
        
        // Ajouter la moyenne des notes à chaque œuvre
        $averageRatings[$oeuvre->getIdOeuvre()] = $averageRating;
    }
    
        return $this->render('oeuvre/affichertousoeuvrs.html.twig', [
            'oeuvres' => $oeuvres,
            'averageRatings' => $averageRatings,
        ]);
    }


    #[Route('/getall', name: 'app1_oeuvre_index', methods: ['GET'])]
    public function getall(OeuvreRepository $oeuvreRepository): Response
    {
        $oeuvres = $oeuvreRepository->findAll();
    
        return $this->render('oeuvre/indexClient.html.twig', [
            'oeuvres' => $oeuvres,
        ]);
    }
  

    #[Route('/search', name: 'oeuvre_search', methods: ['GET'])]

    public function search(Request $request, OeuvreRepository $oeuvreRepository ,  AvisRepository $avisRepository): Response
    {
        $query = $request->query->get('q');
        
    
        $oeuvres = $oeuvreRepository->searchByName($query);

      
    $averageRatings = [];
    
    // Parcourir chaque œuvre pour calculer la moyenne des notes
    foreach ($oeuvres as $oeuvre) {
        // Récupérer les avis correspondant à l'œuvre
        $avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);
        
        // Initialiser la somme des notes et le nombre d'avis
        $totalRating = 0;
        $numberOfRatings = count($avis);
        
        // Calculer la somme des notes
        foreach ($avis as $avi) {
            $totalRating += $avi->getNote();
        }
        
        // Calculer la moyenne des notes
        $averageRating = $numberOfRatings > 0 ? $totalRating / $numberOfRatings : 0;
        
        // Ajouter la moyenne des notes à chaque œuvre
        $averageRatings[$oeuvre->getIdOeuvre()] = $averageRating;
    }

        return $this->render('oeuvre/search_results.html.twig', [
            'oeuvres' => $oeuvres,
            'averageRatings' => $averageRatings,
        ]);
    }

    
    #[Route('/sort', name: 'oeuvre_sort', methods: ['GET'])]
    public function sort(Request $request, OeuvreRepository $oeuvreRepository, AvisRepository $avisRepository): Response
    {
         // Récupérer les critères de tri depuis la requête
         $sortBy = $request->query->get('sort_by');
         $sortOrder = $request->query->get('sort_order');
 
         // Vérifier si les critères de tri sont définis
         if ($sortBy) {
         // Vérifier si sortOrder est également défini, sinon définir par défaut comme croissant
         if ($sortOrder) {
         // Trier les œuvres en fonction des critères sélectionnés
         $criteria = [$sortBy => $sortOrder];
         } else {
         // Définir par défaut l'ordre de tri comme croissant
         $criteria = [$sortBy => 'ASC'];
         }
         $oeuvres = $oeuvreRepository->findBy([], $criteria);
         } else {
         // Par défaut, ne pas appliquer de tri
         $oeuvres = $oeuvreRepository->findAll();
         }

        $averageRatings = [];
    
        // Parcourir chaque œuvre pour calculer la moyenne des notes
        foreach ($oeuvres as $oeuvre) {
            // Récupérer les avis correspondant à l'œuvre
            $avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);
            
            // Initialiser la somme des notes et le nombre d'avis
            $totalRating = 0;
            $numberOfRatings = count($avis);
            
            // Calculer la somme des notes
            foreach ($avis as $avi) {
                $totalRating += $avi->getNote();
            }
            
            // Calculer la moyenne des notes
            $averageRating = $numberOfRatings > 0 ? $totalRating / $numberOfRatings : 0;
            
            // Ajouter la moyenne des notes à chaque œuvre
            $averageRatings[$oeuvre->getIdOeuvre()] = $averageRating;
        }
    

        return $this->render('oeuvre/affichertousoeuvrs.html.twig', [
            'oeuvres' => $oeuvres,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'averageRatings' => $averageRatings,
        ]);
    }

    
    #[Route('/user-images/{imageName}', name: 'user_images1')]
    public function getUserImage(string $imageName): Response
    {
        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    } 
    
    private $entityManager; // Déclarer une propriété pour stocker l'EntityManager

    // Injecter l'EntityManager dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    

    #[Route('/{idOeuvre}', name: 'app_oeuvre_showclient', methods: ['GET'])]
    public function show($idOeuvre): Response
    {
    {
        // Récupérer l'œuvre
        $oeuvre = $this->entityManager->getRepository(Oeuvre::class)->find($idOeuvre);

        // Vérifier si l'œuvre existe
        if (!$oeuvre) {
            throw $this->createNotFoundException('L\'oeuvre n\'existe pas');
        }

        // Récupérer les avis correspondant à l'œuvre
        $avis = $this->entityManager->getRepository(Avis::class)->findBy(['oeuvre' => $oeuvre]);

      


        return $this->render('oeuvre/showclient.html.twig', [
            'oeuvre' => $oeuvre,
            'avis' => $avis,
          
        ]);
    } 

   
   
}
}