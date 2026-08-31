<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Profile extends BaseController
{
    public function index(): string|ResponseInterface
    {
        $userId = current_user_id();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        $profile = (new UserModel())->profileFor($userId);
        if ($profile === null) {
            session()->destroy();

            return redirect()->to('/login');
        }

        return view('profile/index', [
            'title'   => 'Your profile',
            'profile' => $profile,
        ]);
    }
}
