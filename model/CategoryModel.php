<?php
require_once __DIR__ . '/../config.php';

class CategoryModel {
    private $conn;
    private $table = "categories";

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    /**
     * Get all categories
     */
    public function getAllCategories() {
        if (!$this->conn) {
            return [];
        }
        
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get a single category by ID
     */
    public function getCategoryById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE category_id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new category
     */
    public function createCategory($name, $description = null) {
        $query = "INSERT INTO " . $this->table . " (name, description) VALUES (:name, :description)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    /**
     * Update an existing category
     */
    public function updateCategory($id, $name, $description = null) {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, 
                      description = :description
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }

    /**
     * Delete a category
     */
    public function deleteCategory($id) {
        $query = "DELETE FROM " . $this->table . " WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    /**
     * Search categories by name or description
     */
    public function searchCategories($searchTerm) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE name LIKE :search OR description LIKE :search
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $search = '%' . $searchTerm . '%';
        $stmt->bindParam(":search", $search);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Filter categories (placeholder for future filters)
     */
    public function filterCategories($filters = []) {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
        
        // Add filter conditions here if needed in the future
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sort categories
     */
    public function sortCategories($sortBy = 'name', $order = 'ASC') {
        $allowedSorts = ['name', 'category_id'];
        $allowedOrders = ['ASC', 'DESC'];
        
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'name';
        $order = in_array($order, $allowedOrders) ? $order : 'ASC';
        
        $query = "SELECT * FROM " . $this->table . " ORDER BY " . $sortBy . " " . $order;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category statistics
     */
    public function getCategoryStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_categories,
                    (SELECT COUNT(*) FROM games WHERE category_id IS NOT NULL) as games_with_category
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


