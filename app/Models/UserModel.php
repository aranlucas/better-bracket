<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['email', 'password'];
    protected $useTimestamps = false;

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $user = $this->where('email', strtolower(trim($email)))->first();

        return is_array($user) ? $user : null;
    }

    /** @return array<string, mixed>|null */
    public function profileFor(int $id): ?array
    {
        $profile = $this->db->table('users u')
            ->select('u.id, u.email, u.date_joined, up.first, up.last, up.description, up.caption')
            ->join('users_profile up', 'up.user_id = u.id', 'left')
            ->where('u.id', $id)
            ->get()
            ->getRowArray();

        return is_array($profile) ? $profile : null;
    }

    /**
     * Creates an account while preserving the original app's profile row.
     * The transaction prevents an account without a matching profile.
     */
    /** @return array{id?: int, email?: string, error?: string} */
    public function createAccount(string $first, string $last, string $email, string $password): array
    {
        $email = strtolower(trim($email));

        if ($this->findByEmail($email) !== null) {
            return ['error' => 'That email address is already registered.'];
        }

        try {
            $this->db->transBegin();
            $this->insert([
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $id = (int) $this->getInsertID();
            if ($id < 1) {
                throw new \RuntimeException('The user id was not returned after insert.');
            }

            $this->db->table('users_profile')->insert([
                'user_id' => $id,
                'first'   => $first,
                'last'    => $last,
            ]);

            if (! $this->db->transStatus()) {
                throw new \RuntimeException('The account transaction failed.');
            }

            $this->db->transCommit();

            return ['id' => $id, 'email' => $email];
        } catch (Throwable $exception) {
            $this->db->transRollback();
            log_message('error', 'Account creation failed: ' . $exception->getMessage());

            return ['error' => 'We could not create the account right now. Please try again.'];
        }
    }

    /** @return array{id: int, email: string}|null */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null) {
            return null;
        }

        $valid = password_verify($password, (string) $user['password']);
        $legacyHash = hash('sha256', strtolower(trim($email)) . $password);

        // Existing installs used an unsalted SHA-256 digest. Upgrade it in place
        // after the first successful login so no account reset is required.
        if (! $valid && hash_equals((string) $user['password'], $legacyHash)) {
            $valid = true;
            $this->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        if (! $valid) {
            return null;
        }

        if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
            $this->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        return ['id' => (int) $user['id'], 'email' => (string) $user['email']];
    }
}
