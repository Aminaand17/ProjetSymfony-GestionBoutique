<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createArticles($manager);

        for ($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->setSurname("Surname $i");
            $client->setAdresse("Adresse $i");
            $client->setTelephone(substr('7763683' . str_pad($i, 2, "0", STR_PAD_LEFT), 0, 9));
            if ($i % 2 === 0) {
                $user = new User();
                $user->setLogin("login$i");
                $hashedPassword = $this->passwordHasher->hashPassword($user, "password");
                $user->setPassword($hashedPassword);
                $user->setNom("Nom $i");
                $user->setPrenom("Prenom $i");

                $role = match (rand(0, 2)) {
                    0 => 'ROLE_ADMIN',
                    1 => 'ROLE_BOUTIQUIER',
                    default => 'ROLE_CLIENT',
                };

                $user->setRole($role);

                $client->setCompte($user);
                $manager->persist($user);
            }
            $manager->persist($client);
            $this->createDettes($manager, $client);
            $this->createPaiements($manager, $client);
        }
        $manager->flush();
    }

    private function createArticles(ObjectManager $manager): void
    {
        $articles = [
            'Article 1',
            'Article 2',
            'Article 3',
            'Article 4',
            'Article 5',
        ];

        foreach ($articles as $libelle) {
            $article = new Article();
            $article->setLibelle($libelle);
            $article->setPrix(rand(100, 500));
            $article->setQteStock(rand(10, 50));
            $manager->persist($article);
        }

        $manager->flush();
    }

    private function createDettes(ObjectManager $manager, Client $client): void
    {
        $numberOfDettes = rand(1, 2);

        for ($j = 1; $j <= $numberOfDettes; $j++) {
            $dette = new Dette();
            $montant = rand(100, 300);
            $dette->setMontant($montant);
            $dette->setMontantVerser(0);
            $dette->setMontantRestant($montant);
            $dette->setCreateAt(new \DateTimeImmutable());
            $dette->setStatutDette('Non Soldée');
            $dette->setStatutDemande('En Cours');
            $dette->setClient($client);

            $articles = $manager->getRepository(Article::class)->findAll();
            if (count($articles) > 0) {
                $articlesAssocies = array_rand($articles, rand(1, 2));
                if (!is_array($articlesAssocies)) {
                    $articlesAssocies = [$articlesAssocies];
                }

                foreach ($articlesAssocies as $index) {
                    $article = $articles[$index];
                    $dette->addArticle($article);
                }
            }

            $manager->persist($dette);
        }
    }

    private function createPaiements(ObjectManager $manager, Client $client): void
    {
        foreach ($client->getDettes() as $dette) {
            $numberOfPaiements = rand(1, 2);
            $montantRestant = $dette->getMontantRestant();

            for ($k = 1; $k <= $numberOfPaiements; $k++) {
                $montantPaiement = rand(50, min(100, $montantRestant));

                if ($montantPaiement > $montantRestant) {
                    $montantPaiement = $montantRestant;
                }

                $paiement = new Paiement();
                $paiement->setMontant($montantPaiement);
                $paiement->setDate(new \DateTimeImmutable());
                $paiement->setDette($dette);

                $manager->persist($paiement);

                $dette->setMontantVerser($dette->getMontantVerser() + $montantPaiement);
                $dette->setMontantRestant($dette->getMontantRestant() - $montantPaiement);

                if ($dette->getMontantRestant() == 0) {
                    $dette->setStatutDette('Soldée');
                }

                $manager->persist($dette);
            }
        }
    }
}
