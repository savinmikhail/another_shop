<?php

namespace App\DataFixtures;

use App\Entity\Measurement;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();



        // Create and persist products
        for ($i = 1; $i <= 10; $i++) {
            $measurement = new Measurement();
            $measurement
                ->setWeight($faker->numberBetween(1, 50))
                ->setHeight($faker->numberBetween(1, 50))
                ->setLength($faker->numberBetween(1, 50))
                ->setWidth($faker->numberBetween(1, 50));
            $manager->persist($measurement);

            $product = new Product();
            $product->setName($faker->word())
                ->setDescription($faker->sentence())
                ->setCost($faker->numberBetween(1, 50))
                ->setTax($faker->numberBetween(1, 50))
                ->setMeasurement($measurement)
                ->setVersion($faker->numberBetween(1, 50));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
