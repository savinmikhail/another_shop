<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Cart\AddToCartDTO;
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
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Doctrine\DBAL\Exception
     */
    public function add(AddToCartDTO $addToCartDTO, User $user): void
    {
        $product = $this->em->find(Product::class, $addToCartDTO->productId);

        if (!$product) {
            throw new Exception('Product not found');
        }
        $this->em->beginTransaction();
        try {
            $cart = $user->getCart();
            if (!$cart) {
                $cart = new Cart();
                $cart->setOwner($user);
                $user->setCart($cart);
                $this->em->persist($cart);
            }

            // Check if the product is already in the cart
            $cartItem = $cart->getCartItem()->filter(function (CartItem $item) use ($product): bool {
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
                $this->em->persist($cartItem);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
