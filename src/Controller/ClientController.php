<?php

// src/Controller/ClientController.php

namespace App\Controller;

use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Article;
use App\Entity\Demande;
use App\Form\DemandeType;
use App\Entity\DemandeDette;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use App\Repository\DemandeRepository;
use App\Repository\PaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DemandeDetteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/client/dettes', name: 'client_dettes')]
    public function listerDettes(DetteRepository $detteRepository, DemandeRepository $demandeRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User || !$user->getClient()) {
            throw $this->createAccessDeniedException('Seul un client peut accéder à cette page.');
        }
        $client = $user->getClient();
        $dettesNonSoldees = $detteRepository->findByNonSoldeeClient($client);
        $demandesEnCours = $demandeRepository->count(['statut' => 'En Cours']);
        return $this->render('client/dettes/list.html.twig', [
            'dettes' => $dettesNonSoldees,
            'demandesEnCours' => $demandesEnCours, 
        ]);
    }



    #[Route('/client/demandes', name: 'client_demandes')]
    public function listDemandes(Request $request, DemandeRepository $demandeRepository): Response
    {
        $statut = $request->query->get('statut', '');
        $demandes = $demandeRepository->findAll();
        if ($statut) {
            $demandes = $demandeRepository->findBy(['statut' => $statut]);
        }
        return $this->render('client/demandes/list.html.twig', [
            'demandes' => $demandes,
            'statut' => $statut, 
        ]);
    }




    #[Route('/client/demande/new', name: 'client_new_demande')]
    public function nouvelleDemande(Request $request): Response
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($demande);
            $this->entityManager->flush();
            return $this->redirectToRoute('client_demande_list');
        }

        return $this->render('client/demandes/nouvelle_demande.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }



    #[Route('/client/demande/relance/{id}', name: 'client_demande_relance')]
    public function relancerDemande(int $id, DemandeRepository $demandeRepository, EntityManagerInterface $em): JsonResponse
    {
        $client = $this->getUser();

        if (!$client instanceof \App\Entity\User || !$client->getClient()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
        }

        $demande = $demandeRepository->find($id);

        if (!$demande || $demande->getClient() !== $client->getClient()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Demande introuvable'], 404);
        }

        $demande->setStatut('En Cours');
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }


    #[Route('/dette/{id}/detail', name: 'client_dette_detail')]
    public function detail(
        int $id,
        EntityManagerInterface $em,
        PaiementRepository $paiementRepository
    ): Response {
        $dette = $em->getRepository(Dette::class)->find($id);

        if (!$dette) {
            throw $this->createNotFoundException('Dette introuvable.');
        }
        $articles = $dette->getArticles();
        $paiements = $paiementRepository->findBy(['dette' => $dette]);
        $montantDette = $dette->getMontant();
        $montantVerse = array_reduce($paiements, fn($sum, $paiement) => $sum + $paiement->getMontant(), 0);
        $montantRestant = $montantDette - $montantVerse;

        return $this->render('client/dettes/detail.html.twig', [
            'dette' => $dette,
            'client' => $dette->getClient(),
            'articles' => $articles,
            'paiements' => $paiements,
            'montantDette' => $montantDette,
            'montantVerse' => $montantVerse,
            'montantRestant' => $montantRestant,
        ]);
    }

    // /**
    //  * @Route("/client/demande/{id}/relance", name="client_relance_demande")
    //  */
    // #[Route('//client/dettes', name: 'client_dettes')]
    // public function relancerDemande(DemandeDette $demande): Response
    // {
    //     // Logique pour relancer une demande annulée
    //     $demande->setStatut('En Cours');
    //     $this->getDoctrine()->getManager()->flush();

    //     return $this->redirectToRoute('client_demandes');
    // }
}
