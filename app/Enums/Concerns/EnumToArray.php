<?php

namespace App\Enums\Concerns;

trait EnumToArray
{
    public function jsonSerialize(): array
    {
        return [
            'id'   => $this->value,
            'name' => $this->name,
        ];
    }

    public static function allCasesArray(): array
    {
        return array_map(fn(self $case) => $case->jsonSerialize(), self::cases());
    }
}
