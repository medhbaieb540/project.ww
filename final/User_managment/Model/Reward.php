<?php
// models/Reward.php

class Reward
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all rewards.
     */
    public function getAll(): array
    {
        $sql = "SELECT id, title, value, type
                FROM rewards
                ORDER BY id ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get one reward by id.
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT id, title, value, type
                FROM rewards
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create a new reward.
     */
    public function create(string $title, float $value, string $type): bool
    {
        $sql = "INSERT INTO rewards (title, value, type)
                VALUES (:title, :value, :type)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':value' => $value,
            ':type'  => $type,
        ]);
    }

    /**
     * Delete reward by id.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM rewards WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    public function isUsed(int $id): bool
{
    $sql = "SELECT COUNT(*) AS total
            FROM tournaments
            WHERE reward_id = :id";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($row['total']) && (int)$row['total'] > 0;
}

}
