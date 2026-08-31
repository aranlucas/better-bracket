<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GroupModel;
use CodeIgniter\HTTP\ResponseInterface;

class Games extends BaseController
{
    public function index(): string|ResponseInterface
    {
        if (current_user_id() === null) {
            return redirect()->to('/login');
        }

        return view('games/index', [
            'title' => 'Past games',
            'games' => (new GroupModel())->pastGames(),
        ]);
    }
}
