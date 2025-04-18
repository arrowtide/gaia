<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Controllers;

use Arrowtide\Gaia\Interfaces\WishlistRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;

class WishListController extends Controller
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $wishlists = $this->wishlistRepository->getUserWishlists();
        if (! empty($wishlists)) {
            return response()->json($wishlists);
        } else {
            return response()->json([
                'success' => false,
                'reason' => 'no_wishlist_data',
                'message' => 'No wishlists available',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $wishlistDetails = $request->only(['name', 'id', 'product_id']);

            $this->wishlistRepository->createWishlist($wishlistDetails);

            return response()->json(['message' => 'Wishlist created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create wishlist'], 500);
        }
    }

    /**
     * Rename the specified resource in storage.
     */
    public function rename(Request $request, $id): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        try {
            $name = $request->input('name');

            $this->wishlistRepository->renameWishlist($id, $name);

            return response()->json(['message' => 'Wishlist item renamed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error Renaming Wishlist'], 500);
        }
    }

    /**
     * Updates a wishlist to add an item, based on the wishlist IDs for the current logged in user.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|string',
            'wishlist_ids' => 'required|array',
        ]);

        try {
            $productId = $request->input('product_id');
            $wishlistIds = $request->input('wishlist_ids');

            $this->wishlistRepository->updateWishlist($wishlistIds, $productId);

            return response()->json(['message' => 'Item added to wishlist successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error adding item to the wishlist'], 500);
        }
    }

    /**
     * Removes a wishlist item for the given wishlist in the wishlist ID Array for the current user.
     */
    public function removeItem(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|string',
            'wishlist_ids' => 'required|array',
        ]);

        try {
            $productId = $request->input('product_id');
            $wishlistIds = $request->input('wishlist_ids');

            $this->wishlistRepository->removeWishlistItem($wishlistIds, $productId);

            return response()->json(['message' => 'Item removed from the wishlist successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting item from the wishlist'], 500);
        }
    }

    /**
     * Deletes a wishlist with the given wishlist ID for the current user.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->wishlistRepository->deleteWishlist($id);

            return response()->json(['message' => 'Wishlist deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function manageWishlistItems(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|string',
            'wishlist_ids' => 'array',
        ]);

        try {
            $productId = $request->input('product_id');
            $wishlistIds = $request->input('wishlist_ids');

            $this->wishlistRepository->manageWishlistItems($wishlistIds, $productId);

            return response()->json(['message' => 'Wishlist successfully updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating the wishlist'], 500);
        }
    }
}
