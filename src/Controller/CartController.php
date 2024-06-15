<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use function json_decode;

final class CartController extends AbstractController
{
    #[Route('/api/cart', name: 'add_item', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $product = $em->find(Product::class, $data['productId']);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if (! $user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $user->getCart();
        if (! $cart) {
            $cart = new Cart();
            $cart->setOwner($user);
            $user->setCart($cart);
            $em->persist($cart);
        }

        // Check if the product is already in the cart
        $cartItem = $cart->getCartItem()->filter(function (CartItem $item) use ($product) {
            return $item->getProduct()->getId() === $product->getId();
        })->first();

        if ($cartItem) {
            // Increment quantity if the item already exists
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            // Add a new CartItem if it doesn't exist
            $cartItem = new CartItem();
            $cartItem
                ->setCost($product->getCost())
                ->setProduct($product)
                ->setQuantity(1);
            $cart->addCartItem($cartItem);
            $em->persist($cartItem);
        }

        $em->flush();

        $jsonCart = $serializer->serialize($cart, 'json', ['groups' => 'cart:read']);

        return new JsonResponse(json_decode($jsonCart), Response::HTTP_CREATED);
    }
}
