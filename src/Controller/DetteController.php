<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Article;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use App\Repository\PaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DetteController extends AbstractController
{

    private $entityManager;
    private $clientRepository;
    private $articleRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository,
        ArticleRepository $articleRepository
    ) {
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
        $this->articleRepository = $articleRepository;
    }

    #[Route('/dettes', name: 'dette_index')]
    public function index(DetteRepository $detteRepository): Response
    {
        $dettes = $detteRepository->findAll();

        return $this->render('boutiquier/dettes/dette.html.twig', [
            'dettes' => $dettes,
        ]);
    }

    #[Route('/nouvelleDette', name: 'dette_new')]
    public function newDette(Request $request, EntityManagerInterface $entityManager): Response
    {
        $telephone = $request->query->get('telephone');
        $client = null;

        if ($telephone) {
            $client = $entityManager->getRepository(Client::class)->findOneBy(['telephone' => $telephone]);

            if (!$client) {
                $this->addFlash('error', 'Client introuvable pour le numéro : ' . $telephone);
                return $this->redirectToRoute('dette_new');
            }
        }

        $articles = $entityManager->getRepository(Article::class)->findAll();
        if (empty($articles)) {
            throw $this->createNotFoundException('Aucun article trouvé');
        }

        return $this->render('boutiquier/dettes/nouvelle_dette.html.twig', [
            'client' => $client,
            'articles' => $articles,
        ]);
    }

    #[Route('/ajouterDette', name: 'dette_add')]
    public function addDette(Request $request): Response
    {
        try {
            $clientId = $request->request->get('client_id');
            $articlesJson = $request->request->get('articles');
            if (!$clientId || !$articlesJson) {
                return new Response('Client ID et articles requis', Response::HTTP_BAD_REQUEST);
            }
            $articles = json_decode($articlesJson, true);
            if (!is_array($articles)) {
                return new Response('Format des articles invalide', Response::HTTP_BAD_REQUEST);
            }
            $client = $this->clientRepository->find($clientId);
            if (!$client) {
                return new Response('Client non trouvé', Response::HTTP_NOT_FOUND);
            }
    
            $articlesEntities = [];
            foreach ($articles as $articleData) {
                $article = $this->articleRepository->find($articleData['articleId']);
                if (!$article) {
                    return new Response('Article introuvable', Response::HTTP_NOT_FOUND);
                }
                $quantity = $articleData['quantity'];
                if ($quantity <= 0) {
                    return new Response('Quantité invalide pour l\'article', Response::HTTP_BAD_REQUEST);
                }
        
                $articlesEntities[] = [
                    'article' => $article,
                    'quantity' => $quantity
                ];
            }
    
            $dette = new Dette();
            $dette->setClient($client);
            foreach ($articlesEntities as $articleData) {
                $dette->addArticle($articleData['article'], $articleData['quantity']);
            }
            $this->entityManager->persist($dette);
            $this->entityManager->flush();
        
            return new Response('Dette enregistrée avec succès', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    #[Route('/listDettesClient', name: 'dette_list_client')]
    public function listDettesClient(Request $request, EntityManagerInterface $entityManager): Response
    {
        $telephone = $request->query->get('telephone');
        $client = $entityManager->getRepository(Client::class)->findOneBy(['telephone' => $telephone]);
        if (!$client) {
            $this->addFlash('error', 'Client introuvable.');
            return $this->redirectToRoute('dette_index');
        }
        $dettes = $entityManager->getRepository(Dette::class)->findBy(['client' => $client]);
        foreach ($dettes as $dette) {
            if ($dette->getMontantRestant() <= 0 || $dette->getMontantVerser() >= $dette->getMontant()) {
                $dette->setStatutDette("Soldée");
            } else {
                $dette->setStatutDette("Non Soldée");
            }
        }
        $entityManager->flush();
        return $this->render('boutiquier/dettes/dette_client.html.twig', [
            'client' => $client,
            'dettes' => $dettes,
        ]);
    }

    


    #[Route('/dette/{id}', name: 'dette_detail', methods: ['GET'])]
    public function detail(DetteRepository $detteRepository, int $id): Response
    {
        $dette = $detteRepository->find($id);

        if (!$dette) {
            throw $this->createNotFoundException("Dette non trouvée.");
        }

        return $this->render('boutiquier/dettes/detail_dette_client.html.twig', [
            'dette' => $dette,
        ]);
    }

}
