<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Article;
use App\Form\ClientType;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BoutiquierController extends AbstractController
{

    #[Route('/boutiquier', name: 'boutiquier_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $totalDettes = $entityManager->getRepository(Dette::class)->getTotalDettes();
        $nombreClients = $entityManager->getRepository(Client::class)->count([]);
        $nombreArticlesStock = $entityManager->getRepository(Article::class)->countInStock();
        $nombreDemandes = $entityManager->getRepository(Dette::class)->countPendingRequests();
        $clients = $entityManager->getRepository(Client::class)->findBy([], null, 2); 
        $articlesEnRupture = $entityManager->getRepository(Article::class)->findRuptureStock(2, 0);
        return $this->render('boutiquier/dashboard/dashboard.html.twig', [
            'totalDettes' => $totalDettes,
            'nombreClients' => $nombreClients,
            'nombreArticlesStock' => $nombreArticlesStock,
            'nombreDemandes' => $nombreDemandes,
            'clients' => $clients,
            'articlesEnRupture' => $articlesEnRupture,
        ]);
    }


    #[Route('/boutiquier/clients', name: 'client_index')]
    public function listClients(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAll();
        return $this->render('boutiquier/clients/list.html.twig', [
            'clients' => $clients,
        ]);
    }


    #[Route('/boutiquier/client/add', name: 'client_add')]
    public function add(Request $request, ClientRepository $clientRepository): Response
    {
        $client = new Client();
        $surname = $request->get('surname');
        $telephone = $request->get('telephone');
        $adresse = $request->get('adresse');
        $client->setSurname($surname);
        $client->setTelephone($telephone);
        $client->setAdresse($adresse);
        $clientRepository->save($client, true);
        return $this->redirectToRoute('client_index'); 
    }


    #[Route('/api/clients/{telephone}', name: 'search_client', methods: ['GET'])]
    public function searchClient(string $telephone, ClientRepository $clientRepository): Response
    {
        $client = $clientRepository->findOneBy(['telephone' => $telephone]);

        if ($client) {
            return $this->json([
                'surname' => $client->getSurname(),
                'telephone' => $client->getTelephone(),
                'adresse' => $client->getAdresse()
            ]);
        }

        return $this->json(['error' => 'Client introuvable.'], Response::HTTP_NOT_FOUND);
    }
}
