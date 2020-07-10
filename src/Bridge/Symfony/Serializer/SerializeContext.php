<?php

declare(strict_types=1);

namespace Damax\Common\Bridge\Symfony\Serializer;

final class SerializeContext extends Context
{
    private $context;

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    protected function context(): array
    {
        return $this->context;
    }
}
