<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GroupModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index(): string|ResponseInterface
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
