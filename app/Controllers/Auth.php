<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function loginForm(): string|ResponseInterface
    {
        if (is_authenticated()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', ['title' => 'Sign in']);
    }

    public function login(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $user = (new UserModel())->authenticate($email, $password);

        if ($user === null) {
            session()->setFlashdata('error', 'The email or password is incorrect.');

            return redirect()->back()->withInput();
        }

        session()->regenerate(true);
        session()->set(['user_id' => $user['id'], 'email' => $user['email']]);

        return redirect()->to('/dashboard');
    }

    public function register(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }

        $data = [
            'first'    => trim((string) $this->request->getPost('first')),
            'last'     => trim((string) $this->request->getPost('last')),
            'email'    => strtolower(trim((string) $this->request->getPost('email'))),
            'password' => (string) $this->request->getPost('password'),
        ];

        if (! $this->validateData($data, [
            'first'    => 'required|min_length[2]|max_length[125]',
            'last'     => 'required|min_length[2]|max_length[125]',
            'email'    => 'required|valid_email|max_length[125]',
            'password' => 'required|min_length[8]|max_length[72]',
        ])) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->back()->withInput();
        }

        $result = (new UserModel())->createAccount(
            $data['first'],
            $data['last'],
            $data['email'],
            $data['password'],
        );

        if (isset($result['error'])) {
            session()->setFlashdata('error', $result['error']);

            return redirect()->back()->withInput();
        }

        session()->regenerate(true);
        session()->set(['user_id' => $result['id'], 'email' => $result['email']]);
        session()->setFlashdata('success', 'Your account is ready. Welcome to Better Bracket.');

        return redirect()->to('/dashboard');
    }

    public function logout(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }

        session()->destroy();

        return redirect()->to('/login');
    }
}
