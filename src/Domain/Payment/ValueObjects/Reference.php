<?php

namespace Src\Domain\Payment\ValueObjects;

use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;

class Reference
{
    protected UuidInterface $reference;

    public function __construct()
    {
        $this->reference = Str::uuid();
    }

    public static function create(): self
    {
        return new self();
    }

    public function value(): string
    {
        return $this->reference;
    }
}
