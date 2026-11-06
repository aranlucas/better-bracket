<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;

abstract class BaseController extends Controller
{
    protected $helpers = ['app', 'form', 'url'];
}
