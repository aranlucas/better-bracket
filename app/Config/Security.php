<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    public string $csrfProtection = 'session';
    public bool $tokenRandomize = true;
    public string $tokenName = 'better_bracket_csrf';
    public string $headerName = 'X-CSRF-TOKEN';
    public string $cookieName = 'better_bracket_csrf_cookie';
    public int $expires = 7200;
    public bool $regenerate = true;
    public bool $redirect = ENVIRONMENT === 'production';
}
