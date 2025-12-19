<?php

class Feedback
{
    private ?int $id;
    private string $game;
    private string $type;
    private string $message;
    private string $author;
    private string $status;
    private ?string $date;

    public function __construct(
        ?int $id = null,
        string $game = '',
        string $type = 'feedback',
        string $message = '',
        string $author = '',
        string $status = 'pending',
        ?string $date = null
    ) {
        $this->id = $id;
        $this->game = $game;
        $this->type = $type;
        $this->message = $message;
        $this->author = $author;
        $this->status = $status;
        $this->date = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): string
    {
        return $this->game;
    }

    public function setGame(string $game): void
    {
        $this->game = $game;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    public function getAllFeedback(PDO $db): array
    {
        $sql = "SELECT f.*, COUNT(r.id) AS reply_count
                FROM feedback f
                LEFT JOIN replies r ON r.feedback_id = f.id
                GROUP BY f.id
                ORDER BY f.date DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getFeedbackById(PDO $db, int $id): ?array
    {
        $sql = "SELECT * FROM feedback WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function createFeedback(PDO $db, ?int $user_id = null): int
    {
        $sql = "INSERT INTO feedback (game, type, message, author, status, user_id)
                VALUES (:game, :type, :message, :author, :status, :user_id)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':game'   => $this->game,
            ':type'   => $this->type,
            ':message'=> $this->message,
            ':author' => $this->author,
            ':status' => $this->status,
            ':user_id'=> $user_id,
        ]);

        return (int) $db->lastInsertId();
    }

    public function updateFeedback(PDO $db, int $id, string $status): bool
    {
        $sql = "UPDATE feedback SET status = :status WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);
    }

    public function deleteFeedback(PDO $db, int $id): bool
    {
        $db->beginTransaction();

        try {
            $deleteReplies = $db->prepare('DELETE FROM replies WHERE feedback_id = :id');
            $deleteReplies->execute([':id' => $id]);

            $deleteFeedback = $db->prepare('DELETE FROM feedback WHERE id = :id');
            $deleteFeedback->execute([':id' => $id]);

            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getReplies(PDO $db, int $feedbackId): array
    {
        $sql = "SELECT * FROM replies WHERE feedback_id = :id ORDER BY date ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $feedbackId]);

        return $stmt->fetchAll();
    }

    public function addReply(PDO $db, int $feedbackId, string $author, string $message): int
    {
        $sql = "INSERT INTO replies (feedback_id, author, message) VALUES (:feedback_id, :author, :message)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':feedback_id' => $feedbackId,
            ':author'      => $author,
            ':message'     => $message,
        ]);

        return (int) $db->lastInsertId();
    }

    public function getFeedbackStats(PDO $db): array
    {
        $countsSql = "SELECT
                COUNT(*) AS total,
                SUM(type = 'report') AS reports,
                SUM(type = 'feedback') AS feedback,
                SUM(status = 'pending') AS pending,
                SUM(status = 'reviewed') AS reviewed,
                SUM(status = 'fixed') AS fixed
            FROM feedback";

        $countsStmt = $db->prepare($countsSql);
        $countsStmt->execute();
        $counts = $countsStmt->fetch() ?: [];

        $repliesStmt = $db->prepare('SELECT COUNT(*) AS total_replies FROM replies');
        $repliesStmt->execute();
        $replies = $repliesStmt->fetch() ?: ['total_replies' => 0];

        $gamesStmt = $db->prepare('SELECT COUNT(DISTINCT game) AS games_count FROM feedback');
        $gamesStmt->execute();
        $games = $gamesStmt->fetch() ?: ['games_count' => 0];

        return [
            'total'         => (int) ($counts['total'] ?? 0),
            'reports'       => (int) ($counts['reports'] ?? 0),
            'feedback'      => (int) ($counts['feedback'] ?? 0),
            'pending'       => (int) ($counts['pending'] ?? 0),
            'reviewed'      => (int) ($counts['reviewed'] ?? 0),
            'fixed'         => (int) ($counts['fixed'] ?? 0),
            'total_replies' => (int) ($replies['total_replies'] ?? 0),
            'games_count'   => (int) ($games['games_count'] ?? 0),
        ];
    }
}
