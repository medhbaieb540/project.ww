<?php

require_once __DIR__ . '/../Model/Feedback.php';

class FeedbackController
{
    private PDO $db;
    private Feedback $model;

    private const ALLOWED_TYPES = ['feedback', 'report'];
    private const ALLOWED_STATUS = ['pending', 'reviewed', 'fixed'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new Feedback();
    }

    public function listFeedback(array $filters = []): array
    {
        $sql = "SELECT f.*, COUNT(r.id) AS reply_count
                FROM feedback f
                LEFT JOIN replies r ON r.feedback_id = f.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['type']) && in_array($filters['type'], self::ALLOWED_TYPES, true)) {
            $sql .= " AND f.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], self::ALLOWED_STATUS, true)) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['game'])) {
            $sql .= " AND f.game LIKE :game";
            $params[':game'] = '%' . $filters['game'] . '%';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (f.game LIKE :search OR f.message LIKE :search OR f.author LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY f.id ORDER BY f.date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return $rows ?: [];
    }

    public function getFeedbackWithReplies(array $filters = []): array
    {
        $items = $this->listFeedback($filters);

        foreach ($items as &$item) {
            $item['replies'] = $this->model->getReplies($this->db, (int) $item['id']);
        }

        return $items;
    }

    public function getFeedback(int $id): ?array
    {
        $row = $this->model->getFeedbackById($this->db, $id);
        if ($row === null) {
            return null;
        }

        $row['replies'] = $this->model->getReplies($this->db, $id);
        return $row;
    }

    public function createFeedback(
        string $game,
        string $type,
        string $message,
        string $author,
        string $role = 'player',
        ?string $requestedStatus = null
    ): int {
        $game = $this->sanitize($game);
        $type = $this->sanitize($type);
        $message = $this->sanitize($message);
        $author = $this->sanitize($author);
        $requestedStatus = $requestedStatus !== null ? $this->sanitize($requestedStatus) : null;

        if ($game === '' || strlen($game) > 100) {
            throw new InvalidArgumentException('Game name is required and must be under 100 characters.');
        }

        if ($message === '' || strlen($message) > 1000) {
            throw new InvalidArgumentException('Message is required and must be under 1000 characters.');
        }

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException('Invalid feedback type.');
        }

        $status = 'pending';
        if (in_array($role, ['developer', 'admin'], true) && $requestedStatus !== null && in_array($requestedStatus, self::ALLOWED_STATUS, true)) {
            $status = $requestedStatus;
        }

        $feedback = new Feedback(null, $game, $type, $message, $author, $status, null);
        return $feedback->createFeedback($this->db);
    }

    public function updateStatus(int $id, string $status, string $role): bool
    {
        if (!in_array($role, ['developer', 'admin'], true)) {
            throw new RuntimeException('Unauthorized');
        }

        $status = $this->sanitize($status);
        if (!in_array($status, self::ALLOWED_STATUS, true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        if ($this->model->getFeedbackById($this->db, $id) === null) {
            throw new InvalidArgumentException('Feedback not found.');
        }

        return $this->model->updateFeedback($this->db, $id, $status);
    }

    public function deleteFeedback(int $id, string $role): bool
    {
        if ($role !== 'admin') {
            throw new RuntimeException('Unauthorized');
        }

        if ($this->model->getFeedbackById($this->db, $id) === null) {
            throw new InvalidArgumentException('Feedback not found.');
        }

        return $this->model->deleteFeedback($this->db, $id);
    }

    public function addReply(int $feedbackId, string $author, string $message): int
    {
        $author = $this->sanitize($author);
        $message = $this->sanitize($message);

        if ($message === '' || strlen($message) > 500) {
            throw new InvalidArgumentException('Reply must be between 1 and 500 characters.');
        }

        if ($this->model->getFeedbackById($this->db, $feedbackId) === null) {
            throw new InvalidArgumentException('Feedback not found.');
        }

        return $this->model->addReply($this->db, $feedbackId, $author, $message);
    }

    public function getStats(): array
    {
        $stats = $this->model->getFeedbackStats($this->db);
        $total = max(1, $stats['total']);

        $avgReplies = $stats['total'] > 0 ? round($stats['total_replies'] / $total, 2) : 0;
        $resolutionRate = ($stats['reports'] > 0)
            ? round(($stats['fixed'] / $stats['reports']) * 100, 1)
            : 0;

        return array_merge($stats, [
            'avg_replies'      => $avgReplies,
            'resolution_rate'  => $resolutionRate,
        ]);
    }

    public function getTopGames(int $limit = 5): array
    {
        $sql = "SELECT game, COUNT(*) AS count
                FROM feedback
                GROUP BY game
                ORDER BY count DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRecentActivity(int $limit = 5): array
    {
        $sql = "SELECT f.*, COUNT(r.id) AS reply_count
                FROM feedback f
                LEFT JOIN replies r ON r.feedback_id = f.id
                GROUP BY f.id
                ORDER BY f.date DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function sanitize(string $value): string
    {
        return trim($value);
    }
}
