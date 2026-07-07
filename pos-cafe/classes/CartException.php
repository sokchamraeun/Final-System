<?php
declare(strict_types=1);

/* ============================================================
   Thrown by the Cart model when a request cannot be fulfilled.
   Carries the HTTP status the api endpoint should respond with
   (400 bad input, 404 unknown product, …) so the caller can map
   a domain failure straight onto a JSON response.
   ============================================================ */

final class CartException extends RuntimeException
{
    public function __construct(string $message, private int $status = 400)
    {
        parent::__construct($message);
    }

    /** HTTP status code that best describes this failure. */
    public function status(): int
    {
        return $this->status;
    }
}
