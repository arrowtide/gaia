<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Interfaces;

/**
 * Interface for interacting with the Shopify GraphQL Storefront API for cart operations.
 *
 * This interface defines methods necessary for managing the cart lifecycle using Shopify's GraphQL Storefront API.
 * It supports functionalities such as adding items to the cart, updating item quantities, and retrieving cart details.
 *
 * @link https://shopify.dev/docs/storefront-api/getting-started Official documentation for Shopify Storefront API
 */
interface CartRepositoryInterface
{
    /**
     * Retrieves the cart identifier.
     *
     * This method first generates a cart identifier key using the `composeKey` method.
     * It then checks if the cart identifier exists in a fast in-memory store (Blink).
     * If found, it returns the cart identifier from Blink.
     * If the cart identifier is not found in Blink, it retrieves the identifier from a cookie.
     */
    public function getCartId(): ?string;

    /**
     * Compose the cart storage key based on the base key and site identifier.
     *
     * The base key is retrieved from the configuration, and the site identifier
     * is obtained from the current site handle. The final composed key is a
     * combination of these two values separated by a hyphen.
     */
    public function getCartKey();

    /**
     * This mutation creates an empty cart.
     */
    public function createEmptyCart();

    /**
     * This mutation creates a cart and returns information about the cart to ensure it's correct
     * (id, lines, product variant id, etc.) as well as some information about the cart you may want
     */
    public function cartCreate(string $productId, int $quantity);

    /**
     * Query a cart by id and return some of the cart's objects.
     */
    public function cart(string $cartId);

    /**
     * This mutation is used to add a product variant to the cart.
     */
    public function cartLinesUpdate(string $cartId, string $id, int $quantity);

    /**
     * used to associate customer info with a cart and is used to determine international pricing.
     */
    public function updateCartBuyerIdentity(string $cartId, string $buyerIdentity);

    /**
     * Query gets cart by id and returns the cart's checkoutURL.
     */
    public function checkoutURL(string $cartId);

    /**
     * updates the discount codes applied to a given cart and returns the cart id and discountCodes' 'code' and 'applicable' fields
     */
    public function updateCartDiscountCodes(string $cartId, array $discountCodes);

    /**
     * Updates the attributes of a given cart. Cart attributes are used to store info that isn't included in the existing cart fields.
     * The variables for this mutation provide an example of such a use case i.e.
     * "attributes": {
     *      "key": "gift_wrap",
     *      "value": "true"
     * }
     */
    public function updateCartAttributes(string $cartId, array $attributes);

    /**
     * Updates cart note, returns cart id and note.
     * Notes are similar to cart attributes in that they contain additional info about an order.
     */
    public function updateCartNote(string $cartId, string $note);

    /**
     * Remove lines from existing cart.
     */
    public function removeCartLines(string $cartId, array $lineIds);

    /**
     * This mutation adds lines to existing cart, returns the quantity and product id.
     */
    public function cartLinesAdd(string $cartId, string $productId, int $quantity);

    /**
     * Clear the cart by removing the cart identifier from the cookies.
     *
     * This method checks if the cart key exists in either the cookies or Blink
     * cache. If it exists, it queues the cookie to be forgotten.
     */
    public function deleteCart(): void;
}
