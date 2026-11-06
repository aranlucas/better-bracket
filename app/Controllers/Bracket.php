<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BracketModel;
use App\Models\GroupModel;
use App\Services\BracketService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use InvalidArgumentException;

class Bracket extends BaseController
{
    public function index(): string|ResponseInterface
    {
        $userId = current_user_id();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        $groupModel = new GroupModel();

        $teams = (new BracketModel())->bracketTeams();

        return view('bracket/index', [
            'title'  => 'Make your picks',
            'groups' => $groupModel->forUser($userId),
            'teams'  => array_column($teams, null, 'id'),
            'definition' => BracketService::definition($teams),
        ]);
    }

    public function save(): ResponseInterface
    {
        $userId = current_user_id();
        if ($userId === null) {
            return $this->json(['ok' => false, 'error' => 'Please sign in again.'], 401);
        }

        try {
            $payload = $this->request->is('json')
                ? $this->request->getJSON(true)
                : $this->request->getPost();
        } catch (HTTPException) {
            return $this->json(['ok' => false, 'error' => 'Send a valid JSON object.'], 422);
        }
        if (! is_array($payload)) {
            return $this->json(['ok' => false, 'error' => 'Send a valid picks object.'], 422);
        }

        $groupId = (int) ($payload['group_id'] ?? 0);
        $picks = $payload['picks'] ?? [];
        if (is_string($picks)) {
            $picks = json_decode($picks, true);
        }

        if ($groupId < 1 || ! is_array($picks)) {
            return $this->json(['ok' => false, 'error' => 'Choose a group and at least one valid pick.'], 422);
        }

        $groupModel = new GroupModel();
        if (! $groupModel->isMember($groupId, $userId)) {
            return $this->json(['ok' => false, 'error' => 'You must be a member of that group.'], 403);
        }

        $bracketModel = new BracketModel();
        try {
            $normalized = BracketService::validatePicks($picks, $bracketModel->bracketTeams());
        } catch (InvalidArgumentException $exception) {
            return $this->json(['ok' => false, 'error' => $exception->getMessage()], 422);
        }

        if (! $bracketModel->savePicks($groupId, $userId, $normalized)) {
            return $this->json(['ok' => false, 'error' => 'Your picks could not be saved.'], 500);
        }

        return $this->json([
            'ok'       => true,
            'message'  => 'Picks saved.',
        ]);
    }

    /** @param array<string, mixed> $data */
    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data + ['csrfHash' => csrf_hash()]);
    }
}
