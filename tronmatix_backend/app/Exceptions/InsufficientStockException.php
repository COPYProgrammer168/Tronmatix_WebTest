<?php

// app/Exceptions/InsufficientStockException.php

namespace App\Exceptions;

use App\Models\Product;

/**
 * Thrown when a stock operation would exceed available stock (e.g. selling or
 * reversing more than the product has on hand). Carries the product and the
 * shortfall so callers can build a precise, user-facing message instead of
 * catching a bare Exception.
 */
class InsufficientStockException extends \Exception
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requestedQty,
        public readonly int $availableQty,
    ) {
        parent::__construct(
            "Insufficient stock for \"{$product->name}\". Requested {$requestedQty}, only {$availableQty} available."
        );
    }

    public function shortfall(): int
    {
        return $this->requestedQty - $this->availableQty;
    }
}
