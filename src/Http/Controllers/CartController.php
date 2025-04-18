<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Controllers;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class CartController extends Controller
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        try {
            $cartData = $this->cartRepository->cart($cartId);

            return response()->json([$cartData]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve cart information'], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|integer',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        try {
            $this->cartRepository->cartCreate($productId, $quantity);

            return response()->json(['message' => 'Cart created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addToCart(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|integer',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        try {
            $this->cartRepository->cartLinesAdd($cartId, $productId, $quantity);

            return response()->json(['message' => 'Product added to cart successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCartLine(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|integer',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        try {
            $this->cartRepository->cartLinesUpdate($cartId, $productId, $quantity);

            return response()->json(['message' => 'Product added to cart successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkoutURL(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        try {
            $response = $this->cartRepository->checkoutURL($cartId);

            return response()->json($response['cart']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCartDiscountCodes(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        $request->validate([
            'discount_codes' => 'array|max:255',
        ]);

        $discountCodes = $request->input('discount_codes');

        try {
            $this->cartRepository->updateCartDiscountCodes($cartId, $discountCodes);

            return response()->json(['message' => 'Discount codes updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCartAttributes(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        $attributes = [
            'key' => $key,
            'value' => $value,
        ];

        try {
            $this->cartRepository->updateCartAttributes($cartId, $attributes);

            return response()->json(['message' => 'Cart Attributes updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCartNote(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        $request->validate([
            'note' => 'required|string|max:255',
        ]);

        $note = $request->input('note');

        try {
            $this->cartRepository->updateCartNote($cartId, $note);

            return response()->json(['message' => 'Note added to cart successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeFromCart(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        // The input must not contain more than 250 values.
        $request->validate([
            'product_ids' => 'required|array|max:250',
        ]);

        $productIds = $request->input('product_ids');

        try {
            $this->cartRepository->removeCartLines($cartId, $productIds);

            return response()->json(['message' => 'Products removed from the to cart successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteCart(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        try {
            $this->cartRepository->deleteCart();

            return response()->json(['message' => 'Cart has been deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCartBuyerIdentity(Request $request): JsonResponse
    {
        $cartId = $this->cartRepository->getCartId();

        if (! $cartId) {
            return response()->json(['error' => 'You do not have an active cart session'], 404);
        }

        // The input must not contain more than 250 values.
        $request->validate([
            'email' => 'required|array|max:250',
        ]);

        $email = $request->input('email');

        try {
            $this->cartRepository->updateCartBuyerIdentity($cartId, $email);

            return response()->json(['message' => 'Cart Identity has been updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
