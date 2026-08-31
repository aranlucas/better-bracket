<?php

namespace App\Controllers;

use App\Models\GroupModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = current_user_id();
        if ($userId === null || (new UserModel())->find($userId) === null) {
            session()->destroy();

            return redirect()->to('/login');
        }

        $groups = (new GroupModel())->forUser($userId);

        return view('dashboard', [
            'title'  => 'Dashboard',
            'groups' => $groups,
        ]);
    }
}
