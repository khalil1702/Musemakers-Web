<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Oeuvre;
use App\Entity\User;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Security\Core\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

//#[Route('/avis')]
class AvisController extends AbstractController
{

    
    #[Route('/a', name: 'app_avis_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll(),
        ]);
    }

    private $entityManager; // Déclarer une propriété pour stocker l'EntityManager

    // Injecter l'EntityManager dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/new/{idOeuvre}', name: 'app_avis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $idOeuvre): Response
    {
        $avi = new Avis();

        $userId = 1;
        $user = $entityManager->getRepository(User::class)->find($userId);

        // Récupérer l'œuvre associée à l'avis
        $oeuvre = $entityManager->getRepository(Oeuvre::class)->find($idOeuvre);
        
        // Récupérer l'URL de l'image de l'œuvre
        $imageUrl = $this->generateUrl('user_images', ['imageName' => $oeuvre->getImage()]);

        // Récupérer les avis correspondant à l'œuvre
        $avis = $this->entityManager->getRepository(Avis::class)->findBy(['oeuvre' => $oeuvre]);
        
        // Pré-remplir le champ de l'oeuvre avec l'ID de l'oeuvre passé dans l'URL
        $avi->setOeuvre($oeuvre);
        $avi->setUser($user);

    
        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($avi);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_avis_new', ['idOeuvre' => $oeuvre->getIdOeuvre()], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('avis/new.html.twig', [
            'avi' => $avi,
            'form' => $form,
            'imageUrl' => $imageUrl, // Passer l'URL de l'image à la vue
            'avis' => $avis,
            'userId' => $userId,
            'idOeuvre' => $oeuvre->getIdOeuvre(),
            'nomOeuvre' => $oeuvre->getNomOeuvre(),
        ]);
    }
    

    #[Route('/{idAvis}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        return $this->render('avis/show.html.twig', [
            'avi' => $avi,
        ]);
    }

    #[Route('/{idAvis}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

           // Remplacer l'ID de l'utilisateur manuel par l'ID de l'utilisateur réel associé à l'avis
        $userId = $avi->getUser()->getIdUser();

        // Rediriger vers la page user_avis_history avec l'ID de l'utilisateur réel
        return $this->redirectToRoute('app_avis_new', ['idOeuvre' => $avi->getOeuvre()->getIdOeuvre()], Response::HTTP_SEE_OTHER);
    }

        return $this->renderForm('avis/edit.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    #[Route('/{idAvis}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avi->getIdAvis(), $request->request->get('_token'))) {
            $entityManager->remove($avi);
            $entityManager->flush();
        }

        // Remplacer l'ID de l'utilisateur manuel par l'ID de l'utilisateur réel associé à l'avis
        $userId = $avi->getUser()->getIdUser();

        // Rediriger vers la page user_avis_history avec l'ID de l'utilisateur réel
        return $this->redirectToRoute('app_avis_new', ['idOeuvre' => $avi->getOeuvre()->getIdOeuvre()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user-images/{imageName}', name: 'user_images')]
    public function getUserImage(string $imageName): Response
    {
        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    } 

    #[Route('/user/avis/history/{userId}', name: 'user_avis_history', methods: ['GET'])]
    public function userAvisHistory($userId, AvisRepository $avisRepository): Response
    {
        // Récupérer les avis de l'utilisateur avec l'ID $userId depuis le repository
        $userAvisHistory = $avisRepository->findBy(['user' => $userId]);
    
        return $this->render('avis/user_avis_history.html.twig', [
            'avis' => $userAvisHistory,
        ]);
    }

    #[Route('/avis/statistics', name: 'app_avis_statistics', methods: ['GET'])]
public function avisStatistics(AvisRepository $avisRepository): Response
{
    // Récupérer tous les avis depuis le repository
    $avis = $avisRepository->findAll();
    
    // Initialiser les compteurs pour les avis positifs, neutres et négatifs
    $positifCount = 0;
    $neutreCount = 0;
    $negatifCount = 0;
    
    // Parcourir les avis pour compter les avis positifs, neutres et négatifs
    foreach ($avis as $avi) {
        if ($avi->getNote() >= 4 ) {
            $positifCount++;
        } elseif ($avi->getNote() >= 3) {
            $neutreCount++;
        } else {
            $negatifCount++;
        }
    }
    
    // Passer les données à la vue pour affichage
    return $this->render('avis/statistics.html.twig', [
        'positifCount' => $positifCount,
        'neutreCount' => $neutreCount,
        'negatifCount' => $negatifCount,
    ]);
}
  

  
}
