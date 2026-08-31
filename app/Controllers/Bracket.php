<?php

namespace App\Controllers;

use App\Models\GroupModel;
use App\Services\BracketService;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

class Bracket extends BaseController
{
    public function index()
    {
        $userId = current_user_id();
        if ($userId === null) {
            return redirect()->to('/login');
        }

        $groupModel = new GroupModel();

        return view('bracket/index', [
            'title'  => 'Make your picks',
            'groups' => $groupModel->forUser($userId),
            'teams'  => BracketService::teamsByRegion($groupModel->bracketTeams()),
        ]);
    }

    public function save(): ResponseInterface
    {
        $userId = current_user_id();
        if ($userId === null) {
            return $this->json(['ok' => false, 'error' => 'Please sign in again.'], 401);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = $this->request->getPost();
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

        try {
            $normalized = BracketService::normalizePicks($picks);
            $validTeamIds = array_map(
                static fn (array $team): int => (int) $team['id'],
                $groupModel->bracketTeams(),
            );

            foreach ($normalized as $pick) {
                if (! in_array($pick['team_id'], $validTeamIds, true)) {
                    throw new InvalidArgumentException('The bracket contains an unknown team.');
                }
            }
        } catch (InvalidArgumentException $exception) {
            return $this->json(['ok' => false, 'error' => $exception->getMessage()], 422);
        }

        if (! $groupModel->savePicks($groupId, $userId, $normalized)) {
            return $this->json(['ok' => false, 'error' => 'Your picks could not be saved.'], 500);
        }

        return $this->json([
            'ok'       => true,
            'message'  => 'Picks saved.',
            'csrfHash' => csrf_hash(),
        ]);
    }

    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
