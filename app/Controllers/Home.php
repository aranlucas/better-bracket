<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return redirect()->to(is_authenticated() ? '/dashboard' : '/login');
    }
}
