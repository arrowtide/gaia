<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Middleware;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Arrowtide\Gaia\Repositories\CartRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Blink;

class CartManagement
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $cartId = $this->cartRepository->getCartId();

        if ($cartId) {
            $cartData = $this->cartRepository->cart($cartId);

            if (! empty($cartData['cart'])) {
                return $next($request);
            }
        }

        return $this->createCartAndProceed($request, $next);
    }

    /**
     * Create an empty cart and proceed with the request.
     */
    private function createCartAndProceed(Request $request, Closure $next): mixed
    {
        try {
            $cartResponse = $this->cartRepository->createEmptyCart();
            $cartId = $this->extractCartId($cartResponse);

            if (! $cartId) {
                throw new \RuntimeException('Invalid cart ID response');
            }

            $user = auth()->user();

            if ($user) {
                $user->set($this->cartRepository->getCartKey(), $cartId);
                $user->save();

                return $next($request);
            }

            // Store in Blink and set cookie for guests
            Blink::put($this->cartRepository->getCartKey(), $cartId);

            return $next($request)->withCookie(
                cookie($this->cartRepository->getCartKey(), $cartId, 10 * 24 * 60)
            );
        } catch (\Exception $e) {
            Log::error('Unable to create a cart: '.$e->getMessage());

            return response()->json(['error' => 'Unable to create a cart at this time.'], 500);
        }
    }

    /**
     * Extracts cart ID from the response.
     */
    private function extractCartId(array $cartResponse): ?string
    {
        return explode(CartRepository::SHOPIFY_CART_URL, $cartResponse['cartCreate']['cart']['id'])[1]
            ?? null;
    }
}
