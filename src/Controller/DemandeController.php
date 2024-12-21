<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Repository\DetteRepository;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
    #[Route('/demande/{id}', name: 'demande_detail', requirements: ['id' => '\d+'])]
    public function afficherDetailDemande(int $id, DetteRepository $detteRepository): Response
    {
        $demande = $detteRepository->find($id);
        if (!$demande) {
            throw $this->createNotFoundException('Demande de dette non trouvée');
        }
        $articles = $demande->getArticles();
        $montantTotal = 0;
        foreach ($articles as $article) {
            $montantTotal += $article->getPrix() * $article->getQteStock(); 
        }
        $montantVerser = $demande->getMontantVerser();
        $montantRestant = $montantTotal - $montantVerser;

        return $this->render('boutiquier/demandes/detail.html.twig', [
            'demande' => $demande, 
            'articles' => $articles,
            'montantTotal' => $montantTotal, 
            'montantVerser' => $montantVerser,
            'montantRestant' => $montantRestant, 
        ]);
    }

    #[Route('/dette/client/{id}', name: 'dette_detail_client')]
    public function afficherDetailDemandeClient(int $id, DetteRepository $detteRepository): Response
    {
        $dette = $detteRepository->find($id);
        if (!$dette) {
            throw $this->createNotFoundException('Demande de dette non trouvée');
        }
        $articles = $dette->getArticles();
        $paiements = $dette->getPaiements();

        return $this->render('boutiquier/dettes/detail_dette_client.html.twig', [
            'dette' => $dette,
            'articles' => $articles,
            'paiements' => $paiements,
        ]);
    }

    #[Route('/demandes', name: 'demande_liste')]
    public function afficherDemandes(DetteRepository $detteRepository, Request $request): Response
    {
        $statutFilter = $request->query->get('statut', '');
        if ($statutFilter) {
            $dettes = $detteRepository->findByStatut($statutFilter); 
        } else {
            $dettes = $detteRepository->findAll();
        }

        return $this->render('boutiquier/demandes/list.html.twig', [
            'dettes' => $dettes,
            'statutFilter' => $statutFilter,
        ]);
    }

    #[Route('/demande/update', name: 'update_demande_statut', methods: ['POST'])]
    public function updateStatutDemande(Request $request, DetteRepository $detteRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $detteId = $data['id'];
        $newStatut = $data['statut'];
        if (empty($detteId) || empty($newStatut)) {
            return $this->json(['success' => false, 'message' => 'ID ou statut manquant']);
        }
        $dette = $detteRepository->find($detteId);

        if (!$dette) {
            return $this->json(['success' => false, 'message' => 'Dette non trouvée']);
        }
        $dette->setStatutDemande($newStatut);
        $detteRepository->save($dette); 

        return $this->json(['success' => true, 'message' => 'Statut mis à jour']);
    }


    // #[Route('/demandes', name: 'demande_liste')]
    // public function afficherDemandes(DetteRepository $detteRepository): Response
    // {
    //     // Récupérer toutes les dettes
    //     $dettes = $detteRepository->findAll();

    //     return $this->render('boutiquier/demandes/list.html.twig', [
    //         'dettes' => $dettes, // Passe toutes les dettes à la vue
    //     ]);
    // }
}
