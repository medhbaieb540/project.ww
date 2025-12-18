<?php

class Reaction
{
    private ?int $id;
    private string $target;
    private string $username;
    private string $type;

    public function __construct(
        ?int $id = null,
        string $target = '',
        string $username = '',
        string $type = 'like'
    ) {
        $this->id = $id;
        $this->target = $target;
        $this->username = $username;
        $this->type = $type;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getReactionsByTarget(PDO $db, string $target): array
    {
        $sql = "SELECT type, COUNT(*) AS count FROM reactions WHERE target = :target GROUP BY type";
        $stmt = $db->prepare($sql);
        $stmt->execute([':target' => $target]);
        $rows = $stmt->fetchAll() ?: [];

        $result = ['like' => 0, 'dislike' => 0];
        foreach ($rows as $row) {
            $result[$row['type']] = (int) $row['count'];
        }
        return $result;
    }

    public function getUserReaction(PDO $db, string $target, string $username): ?array
    {
        $sql = "SELECT * FROM reactions WHERE target = :target AND username = :username LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':target'   => $target,
            ':username' => $username,
        ]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function addReaction(PDO $db): int
    {
        $sql = "INSERT INTO reactions (target, username, type) VALUES (:target, :username, :type)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':target'   => $this->target,
            ':username' => $this->username,
            ':type'     => $this->type,
        ]);

        return (int) $db->lastInsertId();
    }

    public function removeReaction(PDO $db, string $target, string $username): bool
    {
        $sql = "DELETE FROM reactions WHERE target = :target AND username = :username";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':target'   => $target,
            ':username' => $username,
        ]);
    }

    public function toggleReaction(PDO $db, string $target, string $username, string $type): array
    {
        $db->beginTransaction();

        try {
            $existing = $this->getUserReaction($db, $target, $username);

            if ($existing !== null) {
                if ($existing['type'] === $type) {
                    $this->removeReaction($db, $target, $username);
                } else {
                    $sql = "UPDATE reactions SET type = :type WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':type' => $type,
                        ':id'   => $existing['id'],
                    ]);
                }
            } else {
                $this->target = $target;
                $this->username = $username;
                $this->type = $type;
                $this->addReaction($db);
            }

            $counts = $this->getReactionsByTarget($db, $target);
            $db->commit();
            return $counts;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
