<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Livewire;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Minicart extends Component
{
    public string $checkoutUrl = '';
    public ?string $discountCode = '';
    public array $cartDiscountCodes = [];
    public array $cartItems = [];
    public array $totals = [];
    protected ?string $cartId = '';

    protected array $rules = [
        'discountCode' => 'required|string|min:3',
    ];

    protected array $messages = [
        'discountCode.required' => 'Please enter a discount code.',
        'discountCode.string' => 'The discount code must be a string.',
    ];

    protected CartRepositoryInterface $cartRepository;

    public function boot(CartRepositoryInterface $cartRepository): void
    {
        $this->cartRepository = $cartRepository;
        $this->initializeCart();
    }

    #[On('update-cart')]
    public function updateCart(): void
    {
        if (empty($this->cartId)) {
            $this->cartItems = ['no_results' => true];
            $this->dispatch('update-minicart-counter', count: 0);

            return;
        }

        $cartData = $this->cartRepository->cart($this->cartId);

        if (empty($cartData) || empty($cartData['cart'])) {
            $this->cartItems = ['no_results' => true];
            $this->dispatch('update-minicart-counter', count: 0);

            return;
        }

        $this->checkoutUrl = $cartData['cart']['checkoutUrl'];
        $this->updateCartItems($cartData);
        $this->updateTotals($cartData);
        $this->updateDiscountCodes($cartData);
    }

    #[On('cart-add-line')]
    public function addToCart(mixed $data): void
    {
        $this->cartRepository->cartLinesAdd($this->cartId, $data['variantId'], $data['qty']);
        $this->updateCart();
    }

    public function removeFromCart(string $lineId): void
    {
        $this->cartRepository->removeCartLines($this->cartId, [$lineId]);
        $this->updateCart();
    }

    #[On('update-cart-item-quantity')]
    public function updateCartItemQuantity(string $lineId, int $quantity): void
    {
        $this->cartRepository->cartLinesUpdate($this->cartId, $lineId, $quantity);
        $this->updateCart();
    }

    public function addDiscountToCart(): void
    {
        $this->validate();

        // Add the discount code if it's not already in the array
        if (! in_array($this->discountCode, $this->cartDiscountCodes)) {
            $this->cartDiscountCodes[] = $this->discountCode;

            // Update the cart repository with the new list of discount codes
            $this->cartRepository->updateCartDiscountCodes($this->cartId, $this->cartDiscountCodes);

            // Refresh the cart to reflect changes
            $this->updateCart();
        }

        // Clear the discount code input
        $this->discountCode = '';
    }

    public function removeDiscountFromCart(string $discountCode): void
    {
        if (empty($discountCode)) {
            throw new \InvalidArgumentException('Discount code is required.');
        }

        // Find the index of the discount code in the array
        $index = array_search($discountCode, $this->cartDiscountCodes);

        // Check if the discount code was found
        if ($index !== false) {
            // Remove the discount code from the array
            unset($this->cartDiscountCodes[$index]);

            // Re-index the array to maintain a consistent order
            $this->cartDiscountCodes = array_values($this->cartDiscountCodes);

            // Update the cart repository with the new list of discount codes
            $this->cartRepository->updateCartDiscountCodes($this->cartId, $this->cartDiscountCodes);

            // Refresh the cart to reflect changes
            $this->updateCart();
        }
    }

    protected function updateDiscountCodes(array $cartData): void
    {
        // Ensure we extract the discount codes correctly from the cart data
        if (isset($cartData['cart']['discountCodes'])) {
            // Map the nested structure to a flat array of discount codes
            $this->cartDiscountCodes = array_map(function ($discount) {
                return $discount['code'];
            }, $cartData['cart']['discountCodes']);
        } else {
            // Initialize as an empty array if no discount codes are present in the cart data
            $this->cartDiscountCodes = [
                'no_results' => true,
            ];
        }
    }

    protected function initializeCart(): void
    {
        $this->cartId = $this->cartRepository->getCartId();
        $this->updateCart();
    }

    protected function updateCartItems(array $cartData): void
    {
        // Extract and map cart items
        $cartItems = collect($cartData['cart']['lines']['edges'] ?? [])
            ->map(function ($edge) {
                $node = $edge['node'];

                return array_merge(
                    [
                        'id' => $this->extractId($node['id'], 'CartLine'),
                        'variant_id' => $this->extractId($node['merchandise']['id'], 'ProductVariant'),
                    ],
                    collect($node)->except(['id', 'merchandise'])->toArray()
                );
            })
            ->toArray();

        // If no items are found, set cartItems to 'no_results' and reset the counter to 0
        if (empty($cartItems)) {
            $this->cartItems = ['no_results' => true];
            $this->dispatch('update-minicart-counter', count: 0);

            return;
        }

        // Update the minicart counter and cart items
        $this->dispatch('update-minicart-counter', count: $cartData['cart']['totalQuantity']);

        $this->cartItems = $cartItems;
    }

    private function extractId(string $fullId, string $prefix): string
    {
        return str_replace("gid://shopify/{$prefix}/", '', $fullId);
    }

    protected function updateTotals(array $cartData): void
    {
        $this->totals = [
            'subtotal' => [
                'amount' => $cartData['cart']['cost']['subtotalAmount']['amount'] ?? 0,
                'currency' => $cartData['cart']['cost']['subtotalAmount']['currencyCode'] ?? 'GBP',
            ],
            'total' => [
                'amount' => $cartData['cart']['cost']['totalAmount']['amount'] ?? 0,
                'currency' => $cartData['cart']['cost']['totalAmount']['currencyCode'] ?? 'GBP',
            ],
            'totalTax' => [
                'amount' => $cartData['cart']['cost']['totalTaxAmount']['amount'] ?? 0,
                'currency' => $cartData['cart']['cost']['totalTaxAmount']['currencyCode'] ?? 'GBP',
            ],
            'totalDuty' => [
                'amount' => $cartData['cart']['cost']['totalDutyAmount']['amount'] ?? 0,
                'currency' => $cartData['cart']['cost']['totalDutyAmount']['currencyCode'] ?? 'GBP',
            ],
        ];
    }

    public function render(): View
    {
        return view('features/minicart/minicart', [
            'lines' => $this->cartItems,
            'totals' => $this->totals,
            'discount_codes' => $this->cartDiscountCodes,
            'cart_empty' => array_key_exists('no_results', $this->cartItems) && $this->cartItems['no_results'] == true,
        ]);
    }
}
