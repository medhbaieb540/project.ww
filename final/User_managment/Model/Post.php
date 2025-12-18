<?php

class Post
{
    private ?int $id;
    private int $user_id;
    private string $content;
    private ?string $image;
    private ?string $created_at;
    private ?string $edited_at;
    private int $is_edited;
    private string $category;
    private int $is_archived;

    public function __construct(
        ?int $id = null,
        int $user_id = 0,
        string $content = '',
        ?string $image = null,
        ?string $created_at = null,
        ?string $edited_at = null,
        int $is_edited = 0,
        string $category = 'General',
        int $is_archived = 0
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->image = $image;
        $this->created_at = $created_at;
        $this->edited_at = $edited_at;
        $this->is_edited = $is_edited;
        $this->category = $category;
        $this->is_archived = $is_archived;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
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

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function isArchived(): int
    {
        return $this->is_archived;
    }

    public function setIsArchived(int $is_archived): void
    {
        $this->is_archived = $is_archived;
    }

    public function getAllPosts(PDO $db, bool $include_archived = false): array
    {
        $sql = "SELECT p.*, u.username, u.user_role
                FROM posts p
                LEFT JOIN users u ON u.id = p.user_id";

        if (!$include_archived) {
            $sql .= " WHERE p.is_archived = 0";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function getPostsByCategory(PDO $db, string $category): array
    {
        $sql = "SELECT p.*, u.username, u.user_role
                FROM posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.category = :category AND p.is_archived = 0
                ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':category' => $category]);

        return $stmt->fetchAll() ?: [];
    }

    public function getPostById(PDO $db, int $id): ?array
    {
        $sql = "SELECT p.*, u.username, u.user_role
                FROM posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function getPostsByUser(PDO $db, int $user_id): array
    {
        $sql = "SELECT p.*, u.username, u.user_role
                FROM posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.user_id = :user_id AND p.is_archived = 0
                ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);

        return $stmt->fetchAll() ?: [];
    }

    public function getArchivedPosts(PDO $db, int $user_id): array
    {
        $sql = "SELECT p.*, u.username, u.user_role
                FROM posts p
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.user_id = :user_id AND p.is_archived = 1
                ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);

        return $stmt->fetchAll() ?: [];
    }

    public function createPost(PDO $db): int
    {
        $sql = "INSERT INTO posts (user_id, content, image, category, is_archived, is_edited)
                VALUES (:user_id, :content, :image, :category, :is_archived, :is_edited)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id'     => $this->user_id,
            ':content'     => $this->content,
            ':image'       => $this->image,
            ':category'    => $this->category,
            ':is_archived' => $this->is_archived,
            ':is_edited'   => $this->is_edited,
        ]);

        return (int) $db->lastInsertId();
    }

    public function updatePost(PDO $db, int $id): bool
    {
        $sql = "UPDATE posts
                SET content = :content, category = :category, edited_at = :edited_at, is_edited = :is_edited
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':content'   => $this->content,
            ':category'  => $this->category,
            ':edited_at' => $this->edited_at,
            ':is_edited' => $this->is_edited,
            ':id'        => $id,
        ]);
    }

    public function deletePost(PDO $db, int $id): bool
    {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function archivePost(PDO $db, int $id): bool
    {
        $sql = "UPDATE posts SET is_archived = 1 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function unarchivePost(PDO $db, int $id): bool
    {
        $sql = "UPDATE posts SET is_archived = 0 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getComments(PDO $db, int $post_id): array
    {
        $sql = "SELECT c.*, u.username, u.user_role
                FROM comments c
                LEFT JOIN users u ON u.id = c.user_id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);

        return $stmt->fetchAll() ?: [];
    }

    public function getReactions(PDO $db, int $post_id): array
    {
        $target = 'post' . $post_id;

        $sql = "SELECT type, COUNT(*) AS count
                FROM reactions
                WHERE target = :target
                GROUP BY type";

        $stmt = $db->prepare($sql);
        $stmt->execute([':target' => $target]);
        $rows = $stmt->fetchAll() ?: [];

        $result = ['like' => 0, 'dislike' => 0];
        foreach ($rows as $row) {
            $result[$row['type']] = (int) $row['count'];
        }

        return $result;
    }
}
