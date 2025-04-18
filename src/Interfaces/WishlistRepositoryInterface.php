<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Interfaces;

interface WishlistRepositoryInterface
{
    public function getUserWishlists();

    public function createWishlist(array $wishlistDetails): void;

    /**
     * Updates a wishlist with the given wishlist ID for the current user
     *
     * @param  array  $wishlistIds  An array of wishlist ID's from which the item will be updated.
     * @param  string  $productId  The product ID, This is to avoid using product slug as that can be changed.
     */
    public function updateWishlist(array $wishlistIds, string $productId): void;

    /**
     * Renames a wishlist with the given wishlist ID for the current user.
     *
     * @param  string  $wishlistId  The ID of the wishlist to be renamed.
     * @param  string  $newName  The new name of the wishlist.
     *
     * @throws \Exception If an error occurs during the renaming process.
     */
    public function renameWishlist(string $wishlistId, string $newName): void;

    /**
     * Deletes a wishlist with the given wishlist ID for the current user.
     *
     * @param  string  $wishlistId  The ID of the wishlist to be deleted.
     *
     * @throws \Exception If an error occurs during the deletion process.
     */
    public function deleteWishlist(string $wishlistId): void;

    /**
     * Removes a wishlist item from the specified wishlist for the current user.
     *
     * @param  array  $wishlistIds  An array of wishlist ID's from which the item will be removed.
     * @param  string  $productId  The ID of the variant to be removed from the wishlist.
     *
     * @throws \Exception If the specified wishlist or variant is not found, or if an error occurs during the removal process.
     */
    public function removeWishlistItem(array $wishlistIds, string $productId): void;

    /**
     * This will replace all instances of a product, within the many wishlist ID's
     * Essentially a diff, New variantIds will be added to the wishlists, and missing ones will be removed.
     *
     * @param  array  $wishlistIds  An array of wishlist ID's from which the item will be removed.
     * @param  string  $productId  The ID of the variant to be removed from the wishlist.
     *
     * @throws \Exception If the specified wishlist or variant is not found, or if an error occurs during the removal process.
     */
    public function manageWishlistItems(array $wishlistIds, string $productId): void;
}
