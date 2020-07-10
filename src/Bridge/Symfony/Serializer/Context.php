<?php

declare(strict_types=1);

namespace Damax\Common\Bridge\Symfony\Serializer;

abstract class Context
{
    public function merge(array $context): array
    {
        return array_merge($this->context(), $context);
    }

    abstract protected function context(): array;
}
