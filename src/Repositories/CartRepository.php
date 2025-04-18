<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Repositories;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Storefront;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;

class CartRepository implements CartRepositoryInterface
{
    public const SHOPIFY_CART_VARIANT_URL = 'gid://shopify/ProductVariant/';
    public const SHOPIFY_CART_LINE_URL = 'gid://shopify/CartLine/';
    public const SHOPIFY_CART_URL = 'gid://shopify/Cart/';

    public function getCartId(): ?string
    {
        $cartIdentifier = $this->getCartKey();

        // Check if the user is logged in
        $user = auth()->user();
        if ($user) {
            // If the user is logged in, create a user-specific cart identifier
            return $user->get($cartIdentifier);
        }

        // Check if the cart identifier exists in the Blink cache
        if (Blink::has($cartIdentifier)) {
            return Blink::get($cartIdentifier);
        }

        // Fallback to retrieving from the cookie
        return Cookie::get($cartIdentifier);
    }

    public function getCartKey(): string
    {
        $baseKey = Config::get('gaia.cart.storage_key', 'cart');
        $siteIdentifier = Site::current()->handle();

        return $baseKey.'-'.$siteIdentifier;
    }

    public function createEmptyCart()
    {
        $query = <<<'QUERY'
        mutation createCart($cartInput: CartInput) {
          cartCreate(input: $cartInput) {
            cart {
              id
              createdAt
              updatedAt
              checkoutUrl
              lines(first: 50) {
                edges {
                  node {
                    id
                    merchandise {
                      ... on ProductVariant {
                        id
                      }
                    }
                  }
                }
              }
              attributes {
                key
                value
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [];

        try {
            $response = $this->executeGraphQLQuery($query, $variables);
            $this->handleGraphQLErrors($response['cartCreate']['userErrors']);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to create cart: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function cartCreate(string $productId, int $quantity)
    {
        $query = <<<'QUERY'
        mutation createCart($cartInput: CartInput) {
          cartCreate(input: $cartInput) {
            cart {
              id
              createdAt
              updatedAt
              checkoutUrl
              lines(first: 50) {
                edges {
                  node {
                    id
                    merchandise {
                      ... on ProductVariant {
                        id
                      }
                    }
                  }
                }
              }
              attributes {
                key
                value
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'cartInput' => [
                'lines' => [
                    [
                        'quantity' => $quantity,
                        'merchandiseId' => self::SHOPIFY_CART_VARIANT_URL.$productId,
                    ],
                ],
            ],
        ];

        try {
            $response = $this->executeGraphQLQuery($query, $variables);
            $this->handleGraphQLErrors($response['cartCreate']['userErrors']);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to create cart: '.$e->getMessage());
            throw $e;
        }
    }

    public function cart(string $cartId)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        query cartQuery($cartId: ID!) {
          cart(id: $cartId) {
            id
            createdAt
            updatedAt
            checkoutUrl
            totalQuantity
            lines(first: 50) {
              edges {
                node {
                  id
                  quantity
                  merchandise {
                    ... on ProductVariant {
                      id
                    }
                  }
                  attributes {
                    key
                    value
                  }
                  discountAllocations {
                    discountedAmount {
                      amount
                      currencyCode
                    }
                    ... on CartAutomaticDiscountAllocation {
                      __typename
                      discountedAmount {
                        amount
                        currencyCode
                      }
                      title
                    }
                    ... on CartCustomDiscountAllocation {
                      __typename
                      discountedAmount {
                        amount
                        currencyCode
                      }
                    }
                  }
                  ... on ComponentizableCartLine {
                    id
                  }
                  estimatedCost {
                    totalAmount {
                      amount
                      currencyCode
                    }
                    subtotalAmount {
                      amount
                      currencyCode
                    }
                    compareAtAmount {
                      amount
                      currencyCode
                    }
                    amount {
                      amount
                      currencyCode
                    }
                  }
                }
              }
            }
            attributes {
              key
              value
            }
            cost {
              totalAmount {
                amount
                currencyCode
              }
              subtotalAmount {
                amount
                currencyCode
              }
              totalTaxAmount {
                amount
                currencyCode
              }
              totalDutyAmount {
                amount
                currencyCode
              }
            }
            buyerIdentity {
              email
              phone
              customer {
                id
              }
              countryCode
            }
            discountCodes {
              code
            }
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function cartLinesUpdate(string $cartId, string $id, int $quantity)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation updateCartLines($cartId: ID!, $lines: [CartLineUpdateInput!]!) {
          cartLinesUpdate(cartId: $cartId, lines: $lines) {
            cart {
              id
              lines(first: 50) {
                edges {
                  node {
                    id
                    quantity
                    merchandise {
                      ... on ProductVariant {
                        id
                      }
                    }
                  }
                }
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
            'lines' => [
                'id' => self::SHOPIFY_CART_LINE_URL.$id,
                'quantity' => $quantity,
            ],
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function updateCartBuyerIdentity(string $cartId, string $buyerIdentity)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation updateCartBuyerIdentity($buyerIdentity: CartBuyerIdentityInput!, $cartId: ID!) {
          cartBuyerIdentityUpdate(buyerIdentity: $buyerIdentity, cartId: $cartId) {
            cart {
              id
              buyerIdentity {
                email
                phone
                deliveryAddressPreferences {
                  ... on MailingAddress {
                    address1
                    city
                    country
                    firstName
                    lastName
                  }
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'buyerIdentity' => [
                'email' => $buyerIdentity,
            ],
            'cartId' => $cartId,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function checkoutURL(string $cartId)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        query checkoutURL($cartId: ID!) {
          cart(id: $cartId) {
            checkoutUrl
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function updateCartDiscountCodes(string $cartId, array $discountCodes)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation updateCartDiscountCodes($cartId: ID!, $discountCodes: [String!]) {
          cartDiscountCodesUpdate(cartId: $cartId, discountCodes: $discountCodes) {
            cart {
              id
              discountCodes {
                code
                applicable
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
            'discountCodes' => $discountCodes,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function updateCartAttributes(string $cartId, array $attributes)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation updateCartAttributes($attributes: [AttributeInput!]!, $cartId: ID!) {
          cartAttributesUpdate(attributes: $attributes, cartId: $cartId) {
            cart {
              id
              attributes {
                key
                value
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'attributes' => $attributes,
            'cartId' => $cartId,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function updateCartNote(string $cartId, string $note)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation updateCartNote($cartId: ID!) {
          cartNoteUpdate(cartId: $cartId) {
            cart {
              id
              note
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
            'attributes' => $note,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function removeCartLines(string $cartId, array $lineIds)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation removeCartLines($cartId: ID!, $lineIds: [ID!]!) {
          cartLinesRemove(cartId: $cartId, lineIds: $lineIds) {
            cart {
              id
              lines(first: 50) {
                edges {
                  node {
                    quantity
                    merchandise {
                      ... on ProductVariant {
                        id
                      }
                    }
                  }
                }
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $prefixedLineIds = $this->mapCartLineIds(self::SHOPIFY_CART_LINE_URL, $lineIds);

        $variables = [
            'cartId' => $cartId,
            'lineIds' => $prefixedLineIds,
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function cartLinesAdd(string $cartId, string $productId, int $quantity)
    {
        $cartId = self::SHOPIFY_CART_URL.$cartId;
        $query = <<<'QUERY'
        mutation addCartLines($cartId: ID!, $lines: [CartLineInput!]!) {
          cartLinesAdd(cartId: $cartId, lines: $lines) {
            cart {
              id
              lines(first: 50) {
                edges {
                  node {
                    quantity
                    merchandise {
                      ... on ProductVariant {
                        id
                      }
                    }
                  }
                }
              }
              cost {
                totalAmount {
                  amount
                  currencyCode
                }
                subtotalAmount {
                  amount
                  currencyCode
                }
                totalTaxAmount {
                  amount
                  currencyCode
                }
                totalDutyAmount {
                  amount
                  currencyCode
                }
              }
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $variables = [
            'cartId' => $cartId,
            'lines' => [
                'merchandiseId' => self::SHOPIFY_CART_VARIANT_URL.$productId,
                'quantity' => $quantity,
            ],
        ];

        return $this->executeGraphQLQuery($query, $variables);
    }

    public function deleteCart(): void
    {
        $cartKey = $this->getCartKey();

        if (Cookie::has($cartKey) || Blink::has($cartKey)) {
            Cookie::queue(Cookie::forget($cartKey));
        }
    }

    /**
     * Executes a GraphQL query and returns the result.
     *
     * @throws \Exception
     */
    private function executeGraphQLQuery(string $query, array $payload = []): array
    {
        $response = app(Storefront::class)->query(data: ['query' => $query, 'variables' => $payload]);
        $decoded = json_decode($response->getBody()->getContents(), true);

        if (! empty($decoded['errors'])) {
            throw new \Exception('GraphQL query failed: '.json_encode($decoded['errors']));
        }

        return $decoded['data'] ?? [];
    }

    /**
     * @throws \Exception
     */
    private function handleGraphQLErrors(array $userErrors): void
    {
        if (! empty($userErrors)) {
            $errorMessages = array_column($userErrors, 'message');
            throw new \Exception(implode(', ', $errorMessages));
        }
    }

    /**
     * Prepends a prefix to each element in the array of line IDs.
     *
     * @param  string  $prefix  The prefix to prepend.
     * @param  array  $lineIds  An array of line IDs.
     * @return array An array of modified line IDs with the prefix.
     */
    private function mapCartLineIds(string $prefix, array $lineIds): array
    {
        // Map each line ID to include the prefix
        return array_map(function ($lineId) use ($prefix) {
            return $prefix.$lineId; // Concatenating the prefix with the line ID
        }, $lineIds);
    }
}
