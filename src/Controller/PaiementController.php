<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Repository\DetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaiementController extends AbstractController
{
    #[Route('/paiement/{id}', name: 'paiement_dette', methods: ['GET', 'POST'])]
    public function paiement(
        DetteRepository $detteRepository, 
        EntityManagerInterface $entityManager, 
        Request $request, 
        int $id
    ): Response {
        $dette = $detteRepository->find($id);

        if (!$dette) {
            throw $this->createNotFoundException("La dette demandée n'existe pas.");
        }

        if ($request->isMethod('POST')) {
            $montant = $request->request->get('montant');

            if ($montant > 0 && $montant <= $dette->getMontantRestant()) {
                $dette->setMontantRestant($dette->getMontantRestant() - $montant);
                $entityManager->persist($dette);
                $entityManager->flush();

                $this->addFlash('success', 'Le paiement a été enregistré avec succès.');
                return $this->redirectToRoute('dette_detail', ['id' => $id]);
            }

            $this->addFlash('error', 'Le montant est invalide.');
        }

        return $this->render('boutiquier/dettes/detail_dette_client.html.twig', [
            'dette' => $dette,
        ]);
    }
}
