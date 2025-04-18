<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Repositories;

use Arrowtide\Gaia\Interfaces\WishlistRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Facades\User;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function getUserWishlists()
    {
        return User::current()->data()['wishlist'] ?? [];
    }

    public function createWishlist(array $wishlistDetails): void
    {
        try {
            $user = User::current();
            $existingData = collect($user->get('wishlist', []));
            $collections = collect($existingData->get('collections', []));

            // Handle maximum collections logic
            $maxCollections = config('gaia.wishlists.max_collections');
            if (count($collections) >= $maxCollections) {
                throw new \Exception('Maximum number of collections reached');
            }

            // Handle single wishlist case
            if (empty($collections->toArray()) && $maxCollections === 1) {
                $this->createSingleWishlist($wishlistDetails, $user, $existingData);
            }

            // Validate the incoming request
            $this->validateWishlistDetails($wishlistDetails);

            // Create new wishlist
            $newWishlist = [
                'id' => $wishlistDetails['id'] ?? Str::random(12),
                'name' => $wishlistDetails['name'],
                'items' => $wishlistDetails['items'] ?? [],
                'created_at' => Carbon::now()->timestamp,
                'updated_at' => Carbon::now()->timestamp,
            ];

            // Add product ID to the wishlist items if provided
            if (isset($wishlistDetails['product_id'])) {
                $newWishlist['items'][] = $wishlistDetails['product_id'];
            }

            $collections->push($newWishlist);

            $existingData['collections'] = $collections->values()->toArray();
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            throw new \Exception('Error creating wishlist: '.$e->getMessage());
        }
    }

    private function createSingleWishlist(array $wishlistDetails, $user, $existingData): void
    {
        $newWishlist = [
            'id' => $wishlistDetails['id'] ?? Str::random(12),
            'name' => $wishlistDetails['name'] ?? 'My Wishlist',
            'items' => $wishlistDetails['items'] ?? [],
            'created_at' => Carbon::now()->timestamp,
            'updated_at' => Carbon::now()->timestamp,
        ];

        // Add product ID to the wishlist items if provided
        if (isset($wishlistDetails['product_id'])) {
            $newWishlist['items'][] = $wishlistDetails['product_id'];
        }

        $existingData['collections'] = [$newWishlist];
        $user->set('wishlist', $existingData->toArray());
        $user->save();
    }

    private function validateWishlistDetails(array $wishlistDetails): void
    {
        // Validate wishlist details here
        // Example validation:
        if (! isset($wishlistDetails['name'])) {
            throw new \Exception('Wishlist name is required.');
        }

        if (strlen($wishlistDetails['name']) > 255) {
            throw new \Exception('Wishlist name exceeds maximum length.');
        }
    }

    public function updateWishlist(array $wishlistIds, string $productId): void
    {
        try {
            if (empty($wishlistIds)) {
                throw new \Exception('No wishlist IDs provided.');
            }

            if (empty($productId)) {
                throw new \Exception('No product ID provided.');
            }

            $user = User::current();
            $existingData = collect($user->get('wishlist'));
            $collections = collect($existingData->get('collections'));

            // Iterate over each wishlist ID provided
            foreach ($wishlistIds as $wishlistId) {
                $wishlist = $collections->firstWhere('id', $wishlistId);

                if (! $wishlist) {
                    throw new \Exception("Wishlist with ID {$wishlistId} not found.");
                }

                $wishlistItems = $wishlist['items'];

                if (in_array($productId, $wishlistItems)) {
                    throw new \Exception('Product already exists in the wishlist.');
                }

                // Add the product ID to wishlist items
                $wishlist['items'][] = $productId;
                $wishlist['updated_at'] = Carbon::now()->timestamp;

                // Update the collections with the modified wishlist
                $collections = $collections->map(function ($item) use ($wishlist) {
                    return $item['id'] === $wishlist['id'] ? $wishlist : $item;
                });
            }

            // Update the user's wishlist data and save
            $existingData->put('collections', $collections->values()->toArray());
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error updating wishlist: '.$e->getMessage());
            throw new \Exception('Error updating wishlist.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renameWishlist(string $wishlistId, string $newName): void
    {
        try {
            $user = User::current();
            $existingData = collect($user->get('wishlist'));
            $collections = collect($existingData->get('collections'));

            $wishlist = $collections->firstWhere('id', $wishlistId);

            if (! $wishlist) {
                throw new \Exception('Wishlist not found.');
            }

            $wishlist['name'] = $newName;
            $wishlist['updated_at'] = Carbon::now()->timestamp;

            $collections = $collections->map(function ($item) use ($wishlist) {
                return $item['id'] === $wishlist['id'] ? $wishlist : $item;
            });

            $existingData->put('collections', $collections->values()->toArray());
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error renaming wishlist: '.$e->getMessage());
            throw new \Exception('Error renaming wishlist.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWishlist(string $wishlistId): void
    {
        try {
            $user = User::current();
            $existingData = collect($user->get('wishlist'));
            $collections = collect($existingData->get('collections'));

            $wishlistIndex = $collections->search(function ($wishlist) use ($wishlistId) {
                return $wishlist['id'] == $wishlistId;
            });

            if ($wishlistIndex === false) {
                throw new \Exception('Wishlist not found.');
            }

            $collections->forget($wishlistIndex);

            $existingData->put('collections', $collections->values()->toArray());
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error deleting wishlist: '.$e->getMessage());
            throw new \Exception('Error deleting wishlist.');
        }
    }

    public function removeWishlistItem(array $wishlistIds, string $productId): void
    {
        try {
            if (empty($wishlistIds)) {
                throw new \Exception('No wishlist IDs provided.');
            }

            if (empty($productId)) {
                throw new \Exception('No product ID provided.');
            }

            $user = User::current();
            $existingData = collect($user->get('wishlist'));
            $collections = collect($existingData->get('collections'));

            foreach ($wishlistIds as $wishlistId) {
                $wishlist = $collections->firstWhere('id', $wishlistId);

                if (! $wishlist) {
                    throw new \Exception("Wishlist with ID {$wishlistId} not found.");
                }

                $wishlistItems = $wishlist['items'];

                if (! in_array($productId, $wishlistItems)) {
                    throw new \Exception('Product does not exist in the wishlist.');
                }

                $wishlist['items'] = array_values(array_diff($wishlistItems, [$productId]));
                $wishlist['updated_at'] = Carbon::now()->timestamp;

                $collections = $collections->map(function ($item) use ($wishlist) {
                    return $item['id'] === $wishlist['id'] ? $wishlist : $item;
                });
            }

            // Update the user's wishlist data and save
            $existingData->put('collections', $collections->toArray());
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error deleting wishlist item: '.$e->getMessage());
            throw new \Exception('Error deleting wishlist item.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function manageWishlistItems(array $wishlistIds, string $productId): void
    {
        try {
            if (empty($productId)) {
                throw new \Exception('No product ID provided.');
            }

            $user = User::current();
            $existingData = collect($user->get('wishlist'));
            $collections = collect($existingData->get('collections'));

            // Remove the product from all wishlists not in the provided wishlistIds
            $collections = $collections->map(function ($wishlist) use ($wishlistIds, $productId) {
                $wishlistItems = $wishlist['items'];
                if (! in_array($wishlist['id'], $wishlistIds) && in_array($productId, $wishlistItems)) {
                    $wishlist['items'] = array_filter($wishlistItems, function ($item) use ($productId) {
                        return $item !== $productId;
                    });
                    $wishlist['updated_at'] = Carbon::now()->timestamp;
                    $wishlist['items'] = array_values($wishlist['items']);
                }

                return $wishlist;
            });

            // If specific wishlist IDs are provided, add the product to those wishlists
            if (! empty($wishlistIds)) {
                foreach ($wishlistIds as $wishlistId) {
                    $wishlist = $collections->firstWhere('id', $wishlistId);

                    if (! $wishlist) {
                        throw new \Exception("Wishlist with ID {$wishlistId} not found.");
                    }

                    $wishlistItems = $wishlist['items'];

                    if (! in_array($productId, $wishlistItems)) {
                        // Add the productId if it does not exist
                        $wishlist['items'][] = $productId;
                    }

                    $wishlist['updated_at'] = Carbon::now()->timestamp;
                    $wishlist['items'] = array_values($wishlist['items']);

                    $collections = $collections->map(function ($item) use ($wishlist) {
                        return $item['id'] === $wishlist['id'] ? $wishlist : $item;
                    });
                }
            }

            $existingData['collections'] = $collections->values()->toArray();
            $user->set('wishlist', $existingData->toArray());
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error managing wishlist items: '.$e->getMessage());
            throw new \Exception('Error managing wishlist items.');
        }
    }
}
