<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Address;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function json_decode;

final class AddressController extends AbstractController
{
    #[Route('/api/address', name: 'create_address', methods: ['POST'])]
    public function createOrder(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $data = json_decode($request->getContent(), true);
        $address = new Address();
        $address
            ->setFullAddress($data['fullAddress'])
            ->setKladrId($data['kladrId']);
        $em->persist($address);
        $em->flush();
        return $this->json('address was saved successfully', Response::HTTP_OK);
    }
}
