<?php
class Participation
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function countByTournament(int $tournamentId): int
    {
        $sql = "SELECT COUNT(*) AS c FROM participations WHERE tournament_id = :tid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tid' => $tournamentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    public function isUserInTournament(int $tournamentId, int $userId): bool
    {
        $sql = "SELECT 1 FROM participations
                WHERE tournament_id = :tid AND user_id = :uid
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tid' => $tournamentId,
            ':uid' => $userId
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function join(int $tournamentId, int $userId, int $maxPlayers): bool
    {
      
        if ($this->isUserInTournament($tournamentId, $userId)) {
            return true;
        }

       
        $current = $this->countByTournament($tournamentId);
        if ($current >= $maxPlayers) {
            return false; 
        }

        $sql = "INSERT INTO participations (tournament_id, user_id)
                VALUES (:tid, :uid)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':tid' => $tournamentId,
            ':uid' => $userId
        ]);
    }
    public function leave(int $tournamentId, int $userId): bool
    {
        $sql = "DELETE FROM participations
                WHERE tournament_id = :tid AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':tid' => $tournamentId,
            ':uid' => $userId
        ]);
    }
}
