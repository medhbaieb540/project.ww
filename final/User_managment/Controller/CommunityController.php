<?php

require_once __DIR__ . '/../Model/Post.php';
require_once __DIR__ . '/../Model/Comment.php';
require_once __DIR__ . '/../Model/PostReply.php';
require_once __DIR__ . '/../Model/Message.php';
require_once __DIR__ . '/../Model/Reaction.php';

class CommunityController
{
    private PDO $db;
    private Post $postModel;
    private Comment $commentModel;
    private PostReply $replyModel;
    private Message $messageModel;
    private Reaction $reactionModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->postModel = new Post();
        $this->commentModel = new Comment();
        $this->replyModel = new PostReply();
        $this->messageModel = new Message();
        $this->reactionModel = new Reaction();
    }

    public function getAllPosts(bool $include_archived = false): array
    {
        $posts = $this->postModel->getAllPosts($this->db, $include_archived);
        return $this->hydratePosts($posts);
    }

    public function getPostsByCategory(string $category): array
    {
        $posts = $this->postModel->getPostsByCategory($this->db, $category);
        return $this->hydratePosts($posts);
    }

    public function getPostWithDetails(int $post_id): ?array
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        if ($post === null) {
            return null;
        }

        $post['comments'] = $this->getCommentsWithReplies($post_id);
        $post['reactions'] = $this->reactionModel->getReactionsByTarget($this->db, 'post' . $post_id);
        return $post;
    }

    public function getUserPosts(int $user_id): array
    {
        $posts = $this->postModel->getPostsByUser($this->db, $user_id);
        return $this->hydratePosts($posts);
    }

    public function getArchivedPosts(int $user_id): array
    {
        $posts = $this->postModel->getArchivedPosts($this->db, $user_id);
        return $this->hydratePosts($posts, includeArchivedMeta: true);
    }

    public function createPost(int $user_id, string $content, string $category, ?string $image): int
    {
        $content = $this->sanitize($content);
        $category = $this->sanitize($category);

        if ($content === '' && ($image === null || $image === '')) {
            throw new InvalidArgumentException('Post content or image is required.');
        }

        if (strlen($content) > 1000) {
            throw new InvalidArgumentException('Post content must be under 1000 characters.');
        }

        $post = new Post(null, $user_id, $content, $image, null, null, 0, $category, 0);
        return $post->createPost($this->db);
    }

    public function updatePost(int $post_id, int $user_id, string $content, string $category, string $role): bool
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        $this->assertOwnershipOrAdmin($post, $user_id, $role);

        $content = $this->sanitize($content);
        $category = $this->sanitize($category);

        if ($content === '') {
            throw new InvalidArgumentException('Content cannot be empty.');
        }

        $model = new Post(
            $post_id,
            $post['user_id'],
            $content,
            $post['image'] ?? null,
            $post['created_at'] ?? null,
            date('Y-m-d H:i:s'),
            1,
            $category,
            (int) ($post['is_archived'] ?? 0)
        );

        return $model->updatePost($this->db, $post_id);
    }

    public function updatePostCategory(int $post_id, int $user_id, string $category, string $role): bool
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        $this->assertOwnershipOrAdmin($post, $user_id, $role);

        $category = $this->sanitize($category);

        $model = new Post(
            $post_id,
            $post['user_id'],
            $post['content'] ?? '',
            $post['image'] ?? null,
            $post['created_at'] ?? null,
            date('Y-m-d H:i:s'),
            1,
            $category,
            (int) ($post['is_archived'] ?? 0)
        );

        return $model->updatePost($this->db, $post_id);
    }

    public function deletePost(int $post_id, int $requester_id, string $role): bool
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        $this->assertOwnershipOrAdmin($post, $requester_id, $role);
        return $this->postModel->deletePost($this->db, $post_id);
    }

    public function archivePost(int $post_id, int $requester_id, string $role): bool
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        $this->assertOwnershipOrAdmin($post, $requester_id, $role);
        return $this->postModel->archivePost($this->db, $post_id);
    }

    public function unarchivePost(int $post_id, int $requester_id, string $role): bool
    {
        $post = $this->postModel->getPostById($this->db, $post_id);
        $this->assertOwnershipOrAdmin($post, $requester_id, $role);
        return $this->postModel->unarchivePost($this->db, $post_id);
    }

    public function createComment(int $post_id, int $user_id, string $content): int
    {
        $content = $this->sanitize($content);
        if ($content === '' || strlen($content) > 500) {
            throw new InvalidArgumentException('Comment must be between 1 and 500 characters.');
        }

        $comment = new Comment(null, $post_id, $user_id, $content, null, null, 0);
        return $comment->createComment($this->db);
    }

    public function updateComment(int $comment_id, int $user_id, string $content, string $role): bool
    {
        $comment = $this->commentModel->getCommentById($this->db, $comment_id);
        $this->assertOwnershipOrAdmin($comment, $user_id, $role);

        $content = $this->sanitize($content);
        if ($content === '') {
            throw new InvalidArgumentException('Content cannot be empty.');
        }

        $model = new Comment(
            $comment_id,
            $comment['post_id'],
            $comment['user_id'],
            $content,
            $comment['created_at'] ?? null,
            date('Y-m-d H:i:s'),
            1
        );

        return $model->updateComment($this->db, $comment_id);
    }

    public function deleteComment(int $comment_id, int $requester_id, string $role): bool
    {
        $comment = $this->commentModel->getCommentById($this->db, $comment_id);
        $this->assertOwnershipOrAdmin($comment, $requester_id, $role);
        return $this->commentModel->deleteComment($this->db, $comment_id);
    }

    public function createReply(int $comment_id, int $user_id, string $content): int
    {
        $content = $this->sanitize($content);
        if ($content === '' || strlen($content) > 500) {
            throw new InvalidArgumentException('Reply must be between 1 and 500 characters.');
        }

        $reply = new PostReply(null, $comment_id, $user_id, $content, null, null, 0);
        return $reply->createReply($this->db);
    }

    public function updateReply(int $reply_id, int $user_id, string $content, string $role): bool
    {
        $reply = $this->replyModel->getReplyById($this->db, $reply_id);
        $this->assertOwnershipOrAdmin($reply, $user_id, $role);

        $content = $this->sanitize($content);
        if ($content === '') {
            throw new InvalidArgumentException('Content cannot be empty.');
        }

        $model = new PostReply(
            $reply_id,
            $reply['comment_id'],
            $reply['user_id'],
            $content,
            $reply['created_at'] ?? null,
            date('Y-m-d H:i:s'),
            1
        );

        return $model->updateReply($this->db, $reply_id);
    }

    public function deleteReply(int $reply_id, int $requester_id, string $role): bool
    {
        $reply = $this->replyModel->getReplyById($this->db, $reply_id);
        $this->assertOwnershipOrAdmin($reply, $requester_id, $role);
        return $this->replyModel->deleteReply($this->db, $reply_id);
    }

    public function getConversation(int $user1_id, int $user2_id): array
    {
        return $this->messageModel->getConversation($this->db, $user1_id, $user2_id);
    }

    public function getInbox(int $user_id): array
    {
        return $this->messageModel->getInbox($this->db, $user_id);
    }

    public function getUnreadCount(int $user_id): int
    {
        return $this->messageModel->getUnreadCount($this->db, $user_id);
    }

    public function sendMessage(int $sender_id, int $receiver_id, string $message): int
    {
        $message = $this->sanitize($message);
        if ($message === '' || strlen($message) > 1000) {
            throw new InvalidArgumentException('Message must be between 1 and 1000 characters.');
        }

        if ($sender_id === $receiver_id) {
            throw new InvalidArgumentException('You cannot message yourself.');
        }

        $msg = new Message(null, $sender_id, $receiver_id, $message, 0, null, null);
        return $msg->sendMessage($this->db);
    }

    public function markAsRead(int $message_id, int $receiver_id): bool
    {
        $conversation = $this->messageModel->getConversation($this->db, $receiver_id, $receiver_id);
        // Quick ownership check: only allow if this message is addressed to receiver
        $sql = "SELECT receiver_id FROM messages WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $message_id]);
        $row = $stmt->fetch();
        if (!$row || (int) $row['receiver_id'] !== $receiver_id) {
            throw new RuntimeException('Unauthorized');
        }

        return $this->messageModel->markAsRead($this->db, $message_id);
    }

    public function deleteMessage(int $message_id, int $requester_id): bool
    {
        $sql = "SELECT sender_id, receiver_id FROM messages WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $message_id]);
        $row = $stmt->fetch();

        if (!$row || ($row['sender_id'] != $requester_id && $row['receiver_id'] != $requester_id)) {
            throw new RuntimeException('Unauthorized');
        }

        return $this->messageModel->deleteMessage($this->db, $message_id);
    }

    public function toggleReaction(string $target, string $username, string $type): array
    {
        if (!in_array($type, ['like', 'dislike'], true)) {
            throw new InvalidArgumentException('Invalid reaction type.');
        }

        return $this->reactionModel->toggleReaction($this->db, $target, $username, $type);
    }

    public function getUserReaction(string $target, string $username): ?array
    {
        return $this->reactionModel->getUserReaction($this->db, $target, $username);
    }

    private function hydratePosts(array $posts, bool $includeArchivedMeta = false): array
    {
        foreach ($posts as &$post) {
            $post_id = (int) $post['id'];
            $post['comments'] = $this->getCommentsWithReplies($post_id);
            $post['reactions'] = $this->reactionModel->getReactionsByTarget($this->db, 'post' . $post_id);
            if ($includeArchivedMeta) {
                $post['is_archived'] = (int) ($post['is_archived'] ?? 0);
            }
        }

        return $posts;
    }

    private function getCommentsWithReplies(int $post_id): array
    {
        $comments = $this->commentModel->getCommentsByPost($this->db, $post_id);
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->replyModel->getRepliesByComment($this->db, (int) $comment['id']);
        }
        return $comments;
    }

    private function assertOwnershipOrAdmin(?array $resource, int $user_id, string $role): void
    {
        if ($resource === null) {
            throw new InvalidArgumentException('Resource not found.');
        }

        $isOwner = ((int) ($resource['user_id'] ?? -1)) === $user_id;
        $isAdmin = in_array($role, ['admin'], true);

        if (!$isOwner && !$isAdmin) {
            throw new RuntimeException('Unauthorized');
        }
    }

    private function sanitize(string $value): string
    {
        return trim($value);
    }
}
