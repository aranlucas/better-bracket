<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
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
