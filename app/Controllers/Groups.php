<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GroupModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class Groups extends BaseController
{
    public function index(): string|ResponseInterface
    {
        $userId = $this->authenticatedUser();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        return view('groups/index', [
            'title'  => 'My groups',
            'groups' => (new GroupModel())->forUser($userId),
        ]);
    }

    public function all(): string|ResponseInterface
    {
        if ($this->authenticatedUser() === null) {
            return redirect()->to('/login');
        }

        return view('groups/all', [
            'title'  => 'All groups',
            'groups' => (new GroupModel())->all(),
        ]);
    }

    public function createForm(): string|ResponseInterface
    {
        if ($this->authenticatedUser() === null) {
            return redirect()->to('/login');
        }

        return view('groups/create', ['title' => 'Create a group']);
    }

    public function create(): ResponseInterface
    {
        $userId = $this->authenticatedUser();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        $data = [
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'caption'     => trim((string) $this->request->getPost('caption')),
        ];

        if (! $this->validateData($data, [
            'name'        => 'required|min_length[2]|max_length[125]',
            'description' => 'permit_empty|max_length[2000]',
            'caption'     => 'permit_empty|max_length[125]',
        ])) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->back()->withInput();
        }

        $result = (new GroupModel())->createGroup(
            $data['name'],
            $data['description'],
            $data['caption'],
            $userId,
        );

        if (isset($result['error'])) {
            session()->setFlashdata('error', $result['error']);

            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', 'Group created. Invite people when you are ready.');

        return redirect()->to('/groups/' . $result['id']);
    }

    public function show(int $id): string|ResponseInterface
    {
        if ($this->authenticatedUser() === null) {
            return redirect()->to('/login');
        }

        $model = new GroupModel();
        $group = $model->findWithProfile($id);
        if ($group === null) {
            throw PageNotFoundException::forPageNotFound('Group not found.');
        }

        return view('groups/show', [
            'title'   => $group['name'],
            'group'   => $group,
            'members' => $model->members($id),
            'isMember' => $model->isMember($id, current_user_id()),
        ]);
    }

    public function addMember(int $id): ResponseInterface
    {
        $userId = $this->authenticatedUser();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        $groupModel = new GroupModel();
        if ($groupModel->findWithProfile($id) === null) {
            throw PageNotFoundException::forPageNotFound('Group not found.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($email === '') {
            $email = (string) session()->get('email');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('error', 'Enter a valid member email address.');

            return redirect()->to('/groups/' . $id);
        }

        $user = (new UserModel())->findByEmail($email);
        if ($user === null) {
            session()->setFlashdata('error', 'That email is not registered yet.');

            return redirect()->to('/groups/' . $id);
        }

        if ((int) $user['id'] !== $userId && ! $groupModel->isMember($id, $userId)) {
            session()->setFlashdata('error', 'Join the group before inviting another member.');

            return redirect()->to('/groups/' . $id);
        }

        if ($groupModel->isMember($id, (int) $user['id'])) {
            session()->setFlashdata('error', 'That person is already in this group.');

            return redirect()->to('/groups/' . $id);
        }

        if (! $groupModel->addMember($id, (int) $user['id'])) {
            session()->setFlashdata('error', 'We could not add that member right now.');

            return redirect()->to('/groups/' . $id);
        }

        session()->setFlashdata('success', 'Member added to the group.');

        return redirect()->to('/groups/' . $id);
    }

    private function authenticatedUser(): ?int
    {
        $userId = current_user_id();

        return $userId;
    }
}
