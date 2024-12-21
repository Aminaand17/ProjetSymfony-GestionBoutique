<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'articles_list', methods: ['GET'])]
    public function list(ArticleRepository $articleRepository, Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');
        $search = $request->query->get('search', '');
        $articles = $articleRepository->findFilteredArticles($filter, $search);

        return $this->render('boutiquier/articles/list.html.twig', [
            'articles' => $articles,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    #[Route('/articles/new', name: 'article_add', methods: ['POST'])]
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
}
