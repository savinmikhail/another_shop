<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AddressFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $address = Address::create('улица пушкина дом колотушкина', 12345);
        $manager->persist($address);
        $manager->flush();
    }
}
