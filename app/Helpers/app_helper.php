<?php

if (! function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return is_numeric(session()->get('user_id'));
    }
}

if (! function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $id = session()->get('user_id');

        return is_numeric($id) ? (int) $id : null;
    }
}

if (! function_exists('old_input')) {
    function old_input(string $key, string $default = ''): string
    {
        return e(old($key, $default));
    }
}
