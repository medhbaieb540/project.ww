<?php

class PostReply
{
    private ?int $id;
    private int $comment_id;
    private int $user_id;
    private string $content;
    private ?string $created_at;
    private ?string $edited_at;
    private int $is_edited;

    public function __construct(
        ?int $id = null,
        int $comment_id = 0,
        int $user_id = 0,
        string $content = '',
        ?string $created_at = null,
        ?string $edited_at = null,
        int $is_edited = 0
    ) {
        $this->id = $id;
        $this->comment_id = $comment_id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->created_at = $created_at;
        $this->edited_at = $edited_at;
        $this->is_edited = $is_edited;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentId(): int
    {
        return $this->comment_id;
    }

    public function setCommentId(int $comment_id): void
    {
        $this->comment_id = $comment_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
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

    public function isEdited(): int
    {
        return $this->is_edited;
    }

    public function setIsEdited(int $is_edited): void
    {
        $this->is_edited = $is_edited;
    }

    public function getRepliesByComment(PDO $db, int $comment_id): array
    {
        $sql = "SELECT r.*, u.username, u.user_role
                FROM post_replies r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.comment_id = :comment_id
                ORDER BY r.created_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':comment_id' => $comment_id]);

        return $stmt->fetchAll() ?: [];
    }

    public function getReplyById(PDO $db, int $id): ?array
    {
        $sql = "SELECT r.*, u.username, u.user_role
                FROM post_replies r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function createReply(PDO $db): int
    {
        $sql = "INSERT INTO post_replies (comment_id, user_id, content, is_edited)
                VALUES (:comment_id, :user_id, :content, :is_edited)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':comment_id' => $this->comment_id,
            ':user_id'    => $this->user_id,
            ':content'    => $this->content,
            ':is_edited'  => $this->is_edited,
        ]);

        return (int) $db->lastInsertId();
    }

    public function updateReply(PDO $db, int $id): bool
    {
        $sql = "UPDATE post_replies
                SET content = :content, edited_at = :edited_at, is_edited = :is_edited
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':content'   => $this->content,
            ':edited_at' => $this->edited_at,
            ':is_edited' => $this->is_edited,
            ':id'        => $id,
        ]);
    }

    public function deleteReply(PDO $db, int $id): bool
    {
        $sql = "DELETE FROM post_replies WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
