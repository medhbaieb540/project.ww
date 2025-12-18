<?php


class GameModel {

    private $table = "games";

    
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all games with category and developer info
     */
    public function getAllGames() {
        if (!$this->pdo) {
            error_log("GameModel::getAllGames() - PDO not initialized");
            return [];
        }
        
        try {
            // Get all games without deleted filter
            $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                      FROM " . $this->table . " g
                      LEFT JOIN categories c ON g.category_id = c.category_id
                      LEFT JOIN users u ON g.developer_id = u.id
                      ORDER BY g.created_at DESC";
            
            error_log("GameModel::getAllGames() executing query");
            $stmt = $this->pdo->prepare($query);
            
            if (!$stmt) {
                error_log("GameModel::getAllGames() - prepare() returned false");
                return [];
            }
            
            $executed = $stmt->execute();
            error_log("GameModel::getAllGames() - execute returned: " . ($executed ? "true" : "false"));
            
            if (!$executed) {
                error_log("GameModel::getAllGames() - execute() failed");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return [];
            }
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("GameModel::getAllGames() returned " . count($result) . " games");
            
            return $result;
        } catch (Exception $e) {
            error_log("GameModel::getAllGames() Exception: " . $e->getMessage());
            error_log("Stack: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get a single game by ID (including deleted)
     */
    public function getGameById($id, $includeDeleted = false) {
        $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                  FROM " . $this->table . " g
                  LEFT JOIN categories c ON g.category_id = c.category_id
                  LEFT JOIN users u ON g.developer_id = u.id
                  WHERE g.game_id = :id";
        
        if (!$includeDeleted) {
            $query .= " AND (g.deleted_at IS NULL OR g.deleted_at = '')";
        }
        
        $query .= " LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new game
     */
    public function createGame($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (title, description, developer_id, category_id, image_path, file_path) 
                      VALUES (:title, :description, :developer_id, :category_id, :image_path, :file_path)";
            
            $stmt = $this->pdo->prepare($query);
            
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":developer_id", $data['developer_id']);
            $stmt->bindParam(":category_id", $data['category_id']);
            $stmt->bindParam(":image_path", $data['image_path']);
            $stmt->bindParam(":file_path", $data['file_path']);
            
            if ($stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Database Error in createGame: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing game
     */
    public function updateGame($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, 
                      description = :description, 
                      category_id = :category_id,
                      image_path = :image_path,
                      file_path = :file_path
                  WHERE game_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":category_id", $data['category_id']);
        $stmt->bindParam(":image_path", $data['image_path']);
        $stmt->bindParam(":file_path", $data['file_path']);
        
        return $stmt->execute();
    }

    /**
     * Soft delete a game (move to trash)
     */
    public function deleteGame($id) {
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NOW() 
                  WHERE game_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    /**
     * Get all deleted games (trash)
     */
    public function getDeletedGames() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                      FROM " . $this->table . " g
                      LEFT JOIN categories c ON g.category_id = c.category_id
                      LEFT JOIN users u ON g.developer_id = u.id
                      WHERE g.deleted_at IS NOT NULL AND g.deleted_at != ''
                      ORDER BY g.deleted_at DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Restore a deleted game
     */
    public function restoreGame($id) {
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NULL 
                  WHERE game_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    /**
     * Permanently delete a game from trash
     */
    public function permanentlyDeleteGame($id) {
        $query = "DELETE FROM " . $this->table . " WHERE game_id = :id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    /**
     * Get count of deleted games
     */
    public function getDeletedGamesCount() {
        try {
            $query = "SELECT COUNT(*) as count 
                      FROM " . $this->table . " 
                      WHERE deleted_at IS NOT NULL AND deleted_at != ''";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get games by category
     */
    public function getGamesByCategory($category_id) {
        $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                  FROM " . $this->table . " g
                  LEFT JOIN categories c ON g.category_id = c.category_id
                  LEFT JOIN users u ON g.developer_id = u.id
                  WHERE g.category_id = :category_id
                  AND (g.deleted_at IS NULL OR g.deleted_at = '')
                  ORDER BY g.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search games by title or description
     */
    public function searchGames($searchTerm) {
        if (empty(trim($searchTerm))) {
            return $this->getAllGames();
        }
        
        $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                  FROM " . $this->table . " g
                  LEFT JOIN categories c ON g.category_id = c.category_id
                  LEFT JOIN users u ON g.developer_id = u.id
                  WHERE (g.title LIKE :search OR g.description LIKE :search)
                  AND (g.deleted_at IS NULL OR g.deleted_at = '')
                  ORDER BY g.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $search = '%' . trim($searchTerm) . '%';
        $stmt->bindParam(":search", $search);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Filter games by category and/or minimum rating
     */
    public function filterGames($category_id = null, $minRating = null) {
        $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                  FROM " . $this->table . " g
                  LEFT JOIN categories c ON g.category_id = c.category_id
                  LEFT JOIN users u ON g.developer_id = u.id
                  WHERE (g.deleted_at IS NULL OR g.deleted_at = '')";
        
        $params = [];
        
        if ($category_id !== null) {
            $query .= " AND g.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        if ($minRating !== null) {
            $query .= " AND g.average_rating >= :min_rating";
            $params[':min_rating'] = $minRating;
        }
        
        $query .= " ORDER BY g.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sort games by various criteria
     */
    public function sortGames($sortBy = 'created_at', $order = 'DESC') {
        $allowedSorts = ['title', 'created_at', 'average_rating', 'category_name'];
        $allowedOrders = ['ASC', 'DESC'];
        
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $order = in_array($order, $allowedOrders) ? $order : 'DESC';
        
        $query = "SELECT g.*, c.name as category_name, u.username as developer_name 
                  FROM " . $this->table . " g
                  LEFT JOIN categories c ON g.category_id = c.category_id
                  LEFT JOIN users u ON g.developer_id = u.id
                  WHERE (g.deleted_at IS NULL OR g.deleted_at = '')
                  ORDER BY ";
        
        if ($sortBy === 'category_name') {
            $query .= "c.name " . $order;
        } else {
            $query .= "g." . $sortBy . " " . $order;
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get game statistics
     */
    public function getGameStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_games,
                    AVG(average_rating) as avg_rating,
                    COUNT(DISTINCT category_id) as total_categories,
                    COUNT(DISTINCT developer_id) as total_developers
                  FROM " . $this->table . "
                  WHERE (deleted_at IS NULL OR deleted_at = '')";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>


