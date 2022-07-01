<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Vehicule;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 1; $i < 10; $i++) {
            $vehicule = new Vehicule();
            $vehicule->setTitre("vehicule en location $i")
                ->setMarque("marque $i")
                ->setModele("modele $1")
                ->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.")
                ->setPrixJournalier($i * 10)
                ->setDateEnregistrement(new DateTime("now"));

            $manager->persist($vehicule);
        }
        $manager->flush();
    }
}
