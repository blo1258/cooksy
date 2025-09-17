<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $categories = ['Plats principaux', 'Desserts', 'Petit-déjeuner', 'Salades', 'Soupes'];
        
        foreach ($categories as $categoryName) {
            $categorie = new Categorie();
            $categorie->setNom($categoryName);
            $manager->persist($categorie);
        }

        $manager->flush();
    }
}