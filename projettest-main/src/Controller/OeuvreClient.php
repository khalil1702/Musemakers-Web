<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreType;
use App\Repository\OeuvreRepository;
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
     
    #[Route('/search', name: 'oeuvre_search', methods: ['GET'])]

    public function search(Request $request, OeuvreRepository $oeuvreRepository): Response
    {
        $query = $request->query->get('q');
    
        $oeuvres = $oeuvreRepository->searchByName($query);

        return $this->render('oeuvre/search_results.html.twig', [
            'oeuvres' => $oeuvres,
        ]);
    }
  

    #[Route('/getalloeuvres', name: 'app4_oeuvre_index', methods: ['GET'])]
    public function getall2(OeuvreRepository $oeuvreRepository): Response
    {
        $oeuvres = $oeuvreRepository->findAll();
    
        return $this->render('oeuvre/affichertousoeuvrs.html.twig', [
            'oeuvres' => $oeuvres,
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