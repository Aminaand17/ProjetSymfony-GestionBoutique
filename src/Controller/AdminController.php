<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Entity\Article;
use App\Repository\UserRepository;
use App\Repository\DetteRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig');
    }

    #[Route('/admin/clients', name: 'admin_clients')]
    public function listClients(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAll();

        return $this->render('admin/clients/list.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/admin/clients/create-user', name: 'admin_create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        ClientRepository $clientRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $clientId = $request->request->get('client_id');
        $client = $clientRepository->find($clientId);

        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }
        if ($client->getUser()) {
            return new JsonResponse(['error' => 'Ce client a déjà un compte'], 400);
        }
        $user = new User();
        $user->setNom($client->getNom());
        $user->setPrenom($client->getPrenom());
        $user->setLogin(strtolower($client->getNom()) . rand(1000, 9999));
        $user->setPassword(password_hash('defaultPassword', PASSWORD_BCRYPT));
        $user->setRole('client');
        $em->persist($user);
        $client->setUser($user);
        $em->persist($client);
        $em->flush();

        return new JsonResponse(['success' => 'Compte utilisateur créé avec succès']);
    }


    #[Route('/admin/article', name: 'admin_articles_list', methods: ['GET'])]
    public function list(ArticleRepository $articleRepository, Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');
        $search = $request->query->get('search', '');
        $articles = $articleRepository->findFilteredArticles($filter, $search);

        return $this->render('admin/articles/list.html.twig', [
            'articles' => $articles,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    #[Route('/admin/articles/new', name: 'admin_article_add', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $article = new Article();
            $article->setLibelle($request->request->get('libelle'));
            $article->setPrix((float)$request->request->get('prix'));
            $article->setQteStock((int)$request->request->get('quantite'));

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'article' => [
                    'libelle' => $article->getLibelle(),
                    'prix' => $article->getPrix(),
                    'qteStock' => $article->getQteStock(),
                ],
            ]);
        }

        return $this->json(['success' => false], 400);
    }

    #[Route('/admin/dette', name: 'admin_dette_index')]
    public function index(DetteRepository $detteRepository): Response
    {
        $dettes = $detteRepository->findAll();
        return $this->render('admin/dettes/list.html.twig', [
            'dettes' => $dettes,
        ]);
    }

    #[Route('/admin/dette/archives', name: 'admin_dette_archives')]
    public function archives(DetteRepository $detteRepository): Response
    {
        $archives = $detteRepository->findBy(['statutDette' => 'soldée']); 

        return $this->render('admin/dettes/archive.html.twig', [
            'archives' => $archives,
        ]);
    }


    #[Route('/admin/users', name: 'admin_users')]
    public function indexUser(UserRepository $userRepository, Request $request): Response
    {
        $roleFilter = $request->query->get('role', null);
        $users = $roleFilter
            ? $userRepository->findBy(['role' => $roleFilter])
            : $userRepository->findAll();

        return $this->render('admin/users/list.html.twig', [
            'users' => $users
        ]);
    }


    #[Route('/admin/users/new', name: 'admin_users_new', methods: 'POST')]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->json(['success' => true, 'user' => $user]);
        }
        return $this->json(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'utilisateur']);
    }
    

    
}
