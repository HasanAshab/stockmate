<?php

namespace App\Enums;

enum SearchContext: string
{
    case Global = 'global';
    case Scoped = 'scoped';
}
