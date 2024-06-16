<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Address\CreateAddressDTO;
use App\Service\AddressService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class AddressController extends AbstractController
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {
    }

    #[Route('/api/address', name: 'create_address', methods: ['POST'])]
    public function createOrder(
        #[MapRequestPayload] CreateAddressDTO $createAddressDTO
    ): Response {
        try {
            $this->addressService->create($createAddressDTO);
            return $this->json('Address was saved successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
