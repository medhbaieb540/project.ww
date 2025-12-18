<?php
// Controller/UserController.php

require_once __DIR__ . '/../Model/User.php';

class UserController
{
    private PDO $pdo;

    // ✅ Inject PDO from config/db.php
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listUsersAdvanced(
        string $search = '',
        string $role = '',
        string $status = '',
        ?string $sortField = null,
        string $sortDir = 'ASC'
    ): array {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (username LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($role !== '') {
            $sql .= " AND user_role = :role";
            $params[':role'] = $role;
        }

        if ($status === 'active') {
            $sql .= " AND (is_banned IS NULL OR is_banned = 0)";
        } elseif ($status === 'banned') {
            $sql .= " AND is_banned = 1";
        }

        $allowedSort = ['username', 'email', 'user_role', 'birth_date'];
        $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        if ($sortField !== null && in_array($sortField, $allowedSort, true)) {
            $sql .= " ORDER BY $sortField $sortDir";
        } else {
            $sql .= " ORDER BY id DESC";
        }

        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    public function getUserById(int $id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id]);

        return $query->fetch();
    }

    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        $query = $this->pdo->prepare($sql);
        $query->execute([':email' => $email]);

        return $query->fetch();
    }

    public function listUsers(): array
    {
        $sql = "SELECT * FROM users WHERE user_role != 'super_admin' ORDER BY id DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function deleteUser(int $id): void
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id]);
    }

    public function addUser(User $user): int
    {
        $sql = "INSERT INTO users
                (username, email, password, user_role, birth_date, address, gender)
                VALUES
                (:username, :email, :password, :user_role, :birth_date, :address, :gender)";

        $query = $this->pdo->prepare($sql);
        $ok = $query->execute([
            ':username'   => $user->getUsername(),
            ':email'      => $user->getEmail(),
            ':password'   => $user->getPassword(),
            ':user_role'  => $user->getUserRole(),
            ':birth_date' => $user->getBirth_date(),
            ':address'    => $user->getAddress(),
            ':gender'     => $user->getGender()
        ]);

        if (!$ok) {
            $info = $query->errorInfo();
            throw new Exception($info[2] ?? 'Insert failed');
        }

        return (int)$this->pdo->lastInsertId();
    }

    public function updateUser(
        int $id,
        string $username,
        string $email,
        string $user_role,
        string $birth_date,
        string $address,
        string $gender
    ): void {
        $sql = "UPDATE users
                SET username = :username,
                    email = :email,
                    user_role = :user_role,
                    birth_date = :birth_date,
                    address = :address,
                    gender = :gender
                WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->execute([
            ':username'   => $username,
            ':email'      => $email,
            ':user_role'  => $user_role,
            ':birth_date' => $birth_date,
            ':address'    => $address,
            ':gender'     => $gender,
            ':id'         => $id
        ]);
    }

    public function banUser(int $id): void
    {
        $sql = "UPDATE users SET is_banned = 1 WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id]);
    }

    public function unbanUser(int $id): void
    {
        $sql = "UPDATE users SET is_banned = 0 WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id]);
    }

    public function updateUserPassword(int $id, string $hashedPassword): void
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            ':password' => $hashedPassword,
            ':id'       => $id
        ]);
    }

    public function getLastInsertedId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }
}
