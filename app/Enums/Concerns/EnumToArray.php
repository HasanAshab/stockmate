<?php

namespace App\Enums\Concerns;

trait EnumToArray
{
    public function toArray(): array
    {
        return [
            'id'   => $this->value,
            'name' => $this->name,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function allCasesArray(): array
    {
        return array_map(fn(self $case) => $case->toArray(), self::cases());
    }
}
