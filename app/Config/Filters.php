<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /** @var array<string, class-string> */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
    ];

    /** @var array{before: list<string>, after: list<string>} */
    public array $required = [
        'before' => ['invalidchars'],
        'after'  => [],
    ];

    /** @var array{before: list<string>, after: list<string>} */
    public array $globals = [
        'before' => ['csrf'],
        'after'  => ['secureheaders'],
    ];

    /** @var array<string, list<string>> */
    public array $methods = [];

    /** @var array<string, array<string, list<string>>> */
    public array $filters = [];
}
