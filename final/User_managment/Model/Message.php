<?php

class Message
{
    private ?int $id;
    private int $sender_id;
    private int $receiver_id;
    private string $message;
    private int $is_read;
    private ?string $created_at;
    private ?string $edited_at;

    public function __construct(
        ?int $id = null,
        int $sender_id = 0,
        int $receiver_id = 0,
        string $message = '',
        int $is_read = 0,
        ?string $created_at = null,
        ?string $edited_at = null
    ) {
        $this->id = $id;
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->message = $message;
        $this->is_read = $is_read;
        $this->created_at = $created_at;
        $this->edited_at = $edited_at;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderId(): int
    {
        return $this->sender_id;
    }

    public function setSenderId(int $sender_id): void
    {
        $this->sender_id = $sender_id;
    }

    public function getReceiverId(): int
    {
        return $this->receiver_id;
    }

    public function setReceiverId(int $receiver_id): void
    {
        $this->receiver_id = $receiver_id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function isRead(): int
    {
        return $this->is_read;
    }

    public function setIsRead(int $is_read): void
    {
        $this->is_read = $is_read;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getEditedAt(): ?string
    {
        return $this->edited_at;
    }

    public function setEditedAt(?string $edited_at): void
    {
        $this->edited_at = $edited_at;
    }

    public function getConversation(PDO $db, int $user1_id, int $user2_id): array
    {
        $sql = "SELECT m.*, su.username AS sender_name, ru.username AS receiver_name
                FROM messages m
                LEFT JOIN users su ON su.id = m.sender_id
                LEFT JOIN users ru ON ru.id = m.receiver_id
                WHERE (m.sender_id = :u1 AND m.receiver_id = :u2)
                   OR (m.sender_id = :u2 AND m.receiver_id = :u1)
                ORDER BY m.created_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':u1' => $user1_id,
            ':u2' => $user2_id,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    public function getInbox(PDO $db, int $user_id): array
    {
        $sql = "SELECT * FROM (
                    SELECT m.*, u.username AS other_username
                    FROM messages m
                    JOIN users u ON u.id = CASE
                        WHEN m.sender_id = :uid THEN m.receiver_id
                        ELSE m.sender_id
                    END
                    WHERE m.sender_id = :uid OR m.receiver_id = :uid
                    ORDER BY m.created_at DESC
                ) sub
                GROUP BY GREATEST(sender_id, receiver_id), LEAST(sender_id, receiver_id)
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $user_id]);

        return $stmt->fetchAll() ?: [];
    }

    public function getUnreadCount(PDO $db, int $user_id): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id = :uid AND is_read = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $user_id]);
        $row = $stmt->fetch();

        return (int) ($row['cnt'] ?? 0);
    }

    public function sendMessage(PDO $db): int
    {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, is_read)
                VALUES (:sender_id, :receiver_id, :message, :is_read)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':sender_id'   => $this->sender_id,
            ':receiver_id' => $this->receiver_id,
            ':message'     => $this->message,
            ':is_read'     => $this->is_read,
        ]);

        return (int) $db->lastInsertId();
    }

    public function markAsRead(PDO $db, int $message_id): bool
    {
        $sql = "UPDATE messages SET is_read = 1 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $message_id]);
    }

    public function deleteMessage(PDO $db, int $message_id): bool
    {
        $sql = "DELETE FROM messages WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $message_id]);
    }

    public function editMessage(PDO $db, int $message_id): bool
    {
        $sql = "UPDATE messages SET message = :message, edited_at = :edited_at WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':message'   => $this->message,
            ':edited_at' => $this->edited_at,
            ':id'        => $message_id,
        ]);
    }
}
