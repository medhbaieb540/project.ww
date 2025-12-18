<?php


class ReviewModel {
    
    private $table = "reviews";

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all reviews with game and user info
     */
    public function getAllReviews() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $query = "SELECT r.*, g.title as game_title, u.username as user_name 
                      FROM " . $this->table . " r
                      LEFT JOIN games g ON r.game_id = g.game_id
                      LEFT JOIN users u ON r.user_id = u.id
                      ORDER BY r.created_at DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get reviews for a specific game
     */
    public function getReviewsByGame($game_id) {
        $query = "SELECT r.*, u.username as user_name 
                  FROM " . $this->table . " r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.game_id = :game_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":game_id", $game_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single review by ID
     */
    public function getReviewById($id) {
        $query = "SELECT r.*, g.title as game_title, u.username as user_name 
                  FROM " . $this->table . " r
                  LEFT JOIN games g ON r.game_id = g.game_id
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.review_id = :id
                  LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user already reviewed this game
     */
    public function reviewExists($game_id, $user_id) {
        $query = "SELECT review_id FROM " . $this->table . " 
                  WHERE game_id = :game_id AND user_id = :user_id
                  LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":game_id", $game_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new review
     */
    public function createReview($data) {
        // Check if review already exists
        $existingReview = $this->reviewExists($data['game_id'], $data['user_id']);
        
        if ($existingReview) {
            // Update existing review instead of creating new one
            return $this->updateReview($existingReview['review_id'], [
                'rating' => $data['rating'],
                'comment' => $data['comment']
            ]);
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (game_id, user_id, rating, comment) 
                  VALUES (:game_id, :user_id, :rating, :comment)";
        
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(":game_id", $data['game_id']);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":rating", $data['rating']);
        $stmt->bindParam(":comment", $data['comment']);
        
        try {
            if ($stmt->execute()) {
                // Update game average rating
                $this->updateGameRating($data['game_id']);
                return $this->pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            // If duplicate entry error, try to update instead
            if ($e->getCode() == 23000) {
                $existingReview = $this->reviewExists($data['game_id'], $data['user_id']);
                if ($existingReview) {
                    return $this->updateReview($existingReview['review_id'], [
                        'rating' => $data['rating'],
                        'comment' => $data['comment']
                    ]);
                }
            }
            throw $e;
        }
        
        return false;
    }

    /**
     * Update an existing review
     */
    public function updateReview($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET rating = :rating, 
                      comment = :comment
                  WHERE review_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":rating", $data['rating']);
        $stmt->bindParam(":comment", $data['comment']);
        
        if ($stmt->execute()) {
            // Get game_id to update rating
            $review = $this->getReviewById($id);
            if ($review) {
                $this->updateGameRating($review['game_id']);
            }
            return $id; // Return review ID for consistency
        }
        
        return false;
    }

    /**
     * Delete a review
     */
    public function deleteReview($id) {
        // Get game_id before deleting
        $review = $this->getReviewById($id);
        $game_id = $review ? $review['game_id'] : null;
        
        $query = "DELETE FROM " . $this->table . " WHERE review_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            // Update game average rating
            if ($game_id) {
                $this->updateGameRating($game_id);
            }
            return true;
        }
        
        return false;
    }

    /**
     * Update game average rating
     */
    private function updateGameRating($game_id) {
        $query = "UPDATE games 
                  SET average_rating = (
                      SELECT AVG(rating) 
                      FROM reviews 
                      WHERE game_id = :game_id
                  )
                  WHERE game_id = :game_id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":game_id", $game_id);
        $stmt->execute();
    }

    /**
     * Get reviews by user
     */
    public function getReviewsByUser($user_id) {
        $query = "SELECT r.*, g.title as game_title 
                  FROM " . $this->table . " r
                  LEFT JOIN games g ON r.game_id = g.game_id
                  WHERE r.user_id = :user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search reviews by comment text
     */
    public function searchReviews($searchTerm) {
        $query = "SELECT r.*, g.title as game_title, u.username as user_name 
                  FROM " . $this->table . " r
                  LEFT JOIN games g ON r.game_id = g.game_id
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.comment LIKE :search
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $search = '%' . $searchTerm . '%';
        $stmt->bindParam(":search", $search);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get reviews filtered by rating
     */
    public function getReviewsByRating($minRating) {
        $query = "SELECT r.*, g.title as game_title, u.username as user_name 
                  FROM " . $this->table . " r
                  LEFT JOIN games g ON r.game_id = g.game_id
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.rating >= :min_rating
                  ORDER BY r.rating DESC, r.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":min_rating", $minRating);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get review statistics
     */
    public function getReviewStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    MIN(rating) as min_rating,
                    MAX(rating) as max_rating
                  FROM " . $this->table;
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

