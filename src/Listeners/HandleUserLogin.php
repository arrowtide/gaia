<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Listeners;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;
use Statamic\Facades\Blink;
use Statamic\Statamic;

class HandleUserLogin
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! Statamic::isCpRoute()) {
            $this->handleUserCart($user);
        }
    }

    private function handleUserCart($user): void
    {
        $cartKeyIdentifier = $this->cartRepository->getCartKey();

        $guestCartId = $this->getGuestCartId();
        $userCartId = $user->get($cartKeyIdentifier);

        if ($guestCartId) {
            if ($userCartId) {
                // Check if the cart exists in Shopify
                $userCartExists = $this->cartRepository->cart($userCartId);

                if (! empty($userCartExists['cart'])) {
                    $this->mergeCarts($userCartId, $guestCartId);
                } else {
                    // If user's cart doesn't exist in Shopify, update the user with the guest cart ID
                    $user->set($cartKeyIdentifier, $guestCartId);
                    $user->save();
                }
            } else {
                // If user has no cart, just assign the guest cart to the user
                $user->set($cartKeyIdentifier, $guestCartId);
                $user->save();
            }
            $this->cartRepository->deleteCart(); // Remove the guest cart
        }

        // Call the assignCartToUser method to finalize cart assignment
        $this->assignCartToUser($user);
    }

    private function assignCartToUser($user): void
    {
        $cartId = $this->cartRepository->getCartId();

        if ($cartId) {
            $this->cartRepository->updateCartBuyerIdentity($cartId, $user->email);
        } else {
            $response = $this->cartRepository->createEmptyCart();
            $newCartId = explode('gid://shopify/Cart/', $response['cartCreate']['cart']['id'])[1];

            $this->cartRepository->updateCartBuyerIdentity($newCartId, $user->email);
        }
    }

    private function mergeCarts(string $userCartId, string $guestCartId): void
    {
        $userCart = $this->cartRepository->cart($userCartId);
        $guestCart = $this->cartRepository->cart($guestCartId);

        // Collect line items from both carts
        $userCartLines = $userCart['cart']['lines']['edges'] ?? [];
        $guestCartLines = $guestCart['cart']['lines']['edges'] ?? [];

        foreach ($guestCartLines as $guestLine) {
            $found = false;

            foreach ($userCartLines as $userLine) {
                if ($userLine['node']['merchandise']['id'] === $guestLine['node']['merchandise']['id']) {
                    // If the same item exists in both carts, update the quantity in the user cart
                    $newQuantity = $userLine['node']['quantity'] + $guestLine['node']['quantity'];
                    $this->cartRepository->cartLinesUpdate(
                        $userCartId,
                        $this->extractCartLineId($userLine['node']['id']),
                        $newQuantity
                    );
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                // If the item is not in the user cart, add it
                $this->cartRepository->cartLinesAdd(
                    $userCartId,
                    $this->extractVariantId($guestLine['node']['merchandise']['id']),
                    $guestLine['node']['quantity']
                );
            }
        }

        // Remove guest cart cookie
        $this->cartRepository->deleteCart();
    }

    private function getGuestCartId(): ?string
    {
        $cartIdentifier = $this->cartRepository->getCartKey();

        if (Blink::has($cartIdentifier)) {
            return Blink::get($cartIdentifier);
        }

        return Cookie::get($cartIdentifier);
    }

    private function extractVariantId(string $merchandiseId): string
    {
        return str_replace('gid://shopify/ProductVariant/', '', $merchandiseId);
    }

    private function extractCartLineId(string $merchandiseId): string
    {
        return str_replace('gid://shopify/CartLine/', '', $merchandiseId);
    }
}
