<?php

namespace App\Controllers;

use App\Models\GroupModel;

class Games extends BaseController
{
    public function index()
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
