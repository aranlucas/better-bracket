<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

final class Health extends BaseController
{
    public function live(): ResponseInterface
    {
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function ready(): ResponseInterface
    {
        try {
            Database::connect()->query('SELECT 1');

            return $this->response->setJSON(['status' => 'ready']);
        } catch (Throwable $exception) {
            log_message('error', 'Readiness check failed: ' . $exception->getMessage());

            return $this->response->setStatusCode(503)->setJSON(['status' => 'unavailable']);
        }
    }
}
