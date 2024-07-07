<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AddressFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $address = new Address();
        $address
            ->setFullAddress('улица пушкина дом колотушкина')
            ->setKladrId(12345);
        $manager->persist($address);
        $manager->flush();
    }
}
