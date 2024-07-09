<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Cart\AddToCartDTO;
use App\DTO\Cart\ShowCartDTO;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;

final readonly class CartService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function add(AddToCartDTO $addToCartDTO, User $user): void
    {
        $product = $this->entityManager->find(Product::class, $addToCartDTO->productId);

        if (! $product) {
            throw new Exception('Product not found');
        }
        $this->entityManager->beginTransaction();
        try {
            $cart = $user->getCart();
            if (!$cart) {
                $cart = new Cart();
                $cart->setOwner($user);
                $user->setCart($cart);
                $this->entityManager->persist($cart);
            }

            // Check if the product is already in the cart
            $cartItem = $cart->getCartItems()->filter(function (CartItem $item) use ($product): bool {
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
                $this->entityManager->persist($cartItem);
            }

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function show(User $user): ShowCartDTO
    {
        $cart = $user->getCart();
        if (! $cart) {
            $cart = new Cart();
            $cart->setOwner($user);
            $user->setCart($cart);
            $this->entityManager->persist($cart);
        }
        $items = $cart->getCartItems();
        $total = 0;
        $itemsForResponse = [];
        foreach ($items as $item) {
            $total += $item->getCost() * $item->getQuantity();
            $itemsForResponse[] = [
                'cost' => $item->getCost(),
                'quantity' => $item->getQuantity(),
                'product' => $item->getProduct()->getName()
            ];
        }
        return new ShowCartDTO($itemsForResponse, $total);
    }
}
