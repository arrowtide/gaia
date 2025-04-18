<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Graphql;
use Statamic\Facades\User;

class CustomerTags extends SubTag
{
    private const SHOPIFY_CUSTOMER_PREFIX = 'gid://shopify/Customer/';

    public function order()
    {
        $userId = $this->getCurrentUserId();
        $orderId = $this->params->get('order_id');

        if (! $userId || ! $orderId) {
            return $this->parseNoResults();
        }

        try {
            $data = $this->fetchOrderData($orderId);

            if (empty($data)) {
                return $this->parseNoResults();
            }

            $graphqlCustomerId = $data['customer']['id'];
            $extractedCustomerId = explode(self::SHOPIFY_CUSTOMER_PREFIX, $graphqlCustomerId)[1];

            return ((int) $extractedCustomerId === $userId)
                ? $data
                : $this->parseNoResults();
        } catch (\Exception $e) {
            Log::error("Error fetching order for user $userId: {$e->getMessage()}");

            return $this->parseNoResults();
        }
    }

    private function fetchOrderData($orderId)
    {
        $query = <<<QUERY
            query GetOrder {
              order(id: "gid://shopify/Order/{$orderId}") {
                id
                name
                createdAt
                confirmationNumber
                totalPriceSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                subtotalPriceSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                totalDiscountsSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                totalTaxSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                totalShippingPriceSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                totalRefundedSet{
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                currentTotalPriceSet {
                  shopMoney {
                    amount
                    currencyCode
                  }
                }
                customer {
                  id
                  displayName
                  email
                  phone
                }
                cancelReason
                displayFinancialStatus
                displayFulfillmentStatus
                fulfillments {
                  status
                  trackingInfo {
                    company
                    number
                    url
                  }
                }
                shippingAddress {
                  firstName
                  lastName
                  address1
                  address2
                  city
                  province
                  provinceCode
                  zip
                  country
                  phone
                  company
                }
                billingAddress {
                  firstName
                  lastName
                  address1
                  address2
                  city
                  province
                  provinceCode
                  zip
                  country
                  phone
                  company
                }
                lineItems(first: 100) {
                  edges {
                    node {
                      id
                      title
                      quantity
                      sku
                      product {
                        id
                      }
                      variant {
                        id
                        title
                        sku
                        price
                      }
                      originalUnitPriceSet {
                        shopMoney {
                          amount
                          currencyCode
                        }
                      }
                      discountedUnitPriceSet {
                        shopMoney {
                          amount
                          currencyCode
                        }
                      }
                      totalDiscountSet {
                        shopMoney {
                          amount
                          currencyCode
                        }
                      }
                    }
                  }
                }
              }
            }
        QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('GraphQL query failed with status: '.$response->getStatusCode());
        }

        $data = $response->getDecodedBody();

        if (empty($data['data']['order'])) {
            return $data;
        }

        $order = Arr::get($data, 'data.order', []);
        $lineItems = $this->mapLineItems($order['lineItems']['edges'] ?? []);

        return array_merge($order, ['lineItems' => $lineItems]);
    }

    public function orders()
    {
        $userId = $this->getCurrentUserId();

        if (! $userId) {
            return $this->parseNoResults();
        }

        try {
            $data = $this->fetchOrdersDataByCustomer($userId);

            return empty($data) ? $this->parseNoResults() : $data;
        } catch (\Exception $e) {
            Log::error("Error fetching orders for user $userId: {$e->getMessage()}");

            return $this->parseNoResults();
        }
    }

    private function fetchOrdersDataByCustomer($userId)
    {
        $query = <<<QUERY
            query GetOrdersByCustomer {
              orders(first: 100, query: "customer_id:{$userId}", sortKey: CREATED_AT, reverse: true) {
                edges {
                  node {
                    id
                    name
                    createdAt
                    cancelReason
                    displayFulfillmentStatus
                    totalPriceSet {
                      shopMoney {
                        amount
                        currencyCode
                      }
                    }
                    lineItems(first: 100) {
                      edges {
                        node {
                          id
                          quantity
                          product {
                            id
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
        QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('GraphQL query failed with status: '.$response->getStatusCode());
        }

        $data = $response->getDecodedBody();

        if (empty($data['data']['orders'])) {
            return $data;
        }

        $orders = Arr::get($data, 'data.orders.edges', []);

        return array_map(function ($orderEdge) {
            $orderNode = $orderEdge['node'];
            $lineItems = $this->mapLineItems($orderNode['lineItems']['edges'] ?? []);

            return array_merge($orderNode, ['lineItems' => $lineItems]);
        }, $orders);
    }

    private function mapLineItems(array $lineItemsEdges): array
    {
        return array_map(function ($lineItemEdge) {
            return $lineItemEdge['node'];
        }, $lineItemsEdges);
    }

    private function getCurrentUserId(): ?int
    {
        return User::current()?->get('shopify_id');
    }
}
