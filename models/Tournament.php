<?php

class Tournament
{
    private $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    private function addStatus(array $row): array
    {
        $now = new DateTime();
        $status = 'upcoming';

        if (!empty($row['start_date'])) {
            $start = new DateTime($row['start_date']);

            if (!empty($row['end_date'])) {
                $end = new DateTime($row['end_date']);

                if ($now < $start) {
                    $status = 'upcoming';
                } elseif ($now >= $start && $now <= $end) {
                    $status = 'live';
                } else {
                    $status = 'finished';
                }
            } else {
                
                if ($now < $start) {
                    $status = 'upcoming';
                } else {
                    $status = 'live';
                }
            }
        } else {
            $status = 'finished';
        }

        $row['status'] = $status;
        return $row;
    }

    public function getAll(): array
    {
        $sql = "SELECT
                    t.id,
                    t.name,
                    t.description,
                    t.start_date,
                    t.end_date,
                    t.reward_id,
                    t.max_players,
                    r.title AS reward_title,
                    r.value AS reward_value,
                    r.type  AS reward_type
                FROM tournaments t
                LEFT JOIN rewards r ON t.reward_id = r.id
                ORDER BY t.start_date DESC, t.id DESC";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->addStatus($row);
        }

        return $result;
    }

    
    public function getById(int $id): ?array
    {
        $sql = "SELECT
                    t.id,
                    t.name,
                    t.description,
                    t.start_date,
                    t.end_date,
                    t.reward_id,
                    t.max_players,
                    r.title AS reward_title,
                    r.value AS reward_value,
                    r.type  AS reward_type
                FROM tournaments t
                LEFT JOIN rewards r ON t.reward_id = r.id
                WHERE t.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->addStatus($row);
    }

    
    public function create(array $data): bool
    {
        $sql = "INSERT INTO tournaments
                    (name, description, start_date, end_date, reward_id, max_players)
                VALUES
                    (:name, :description, :start_date, :end_date, :reward_id, :max_players)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':name'        => $data['name'] ?? '',
            ':description' => $data['description'] ?? '',
            ':start_date'  => $data['start_date'] ?? null,
            ':end_date'    => $data['end_date'] ?? null,
            ':reward_id'   => $data['reward_id'] ?? null,
            ':max_players' => $data['max_players'] ?? 16,
        ]);
    }

   
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE tournaments
                SET name        = :name,
                    description = :description,
                    start_date  = :start_date,
                    end_date    = :end_date,
                    reward_id   = :reward_id,
                    max_players = :max_players
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':name'        => $data['name'] ?? '',
            ':description' => $data['description'] ?? '',
            ':start_date'  => $data['start_date'] ?? null,
            ':end_date'    => $data['end_date'] ?? null,
            ':reward_id'   => $data['reward_id'] ?? null,
            ':max_players' => $data['max_players'] ?? 16,
            ':id'          => $id,
        ]);
    }

   
    public function delete(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt1 = $this->pdo->prepare(
                "DELETE FROM participations WHERE tournament_id = :id"
            );
            $stmt1->execute([':id' => $id]);

            $stmt2 = $this->pdo->prepare(
                "DELETE FROM tournaments WHERE id = :id"
            );
            $ok = $stmt2->execute([':id' => $id]);

            $this->pdo->commit();
            return $ok;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
