<?php
require_once __DIR__ . '/../model/CategoryModel.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * List all categories
     */
    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/category/list.php';
    }

    /**
     * Show create form
     */
    public function create() {
        require_once __DIR__ . '/../view/front office/category/create.php';
    }

    /**
     * Store a new category
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? null;

            if (empty($name)) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=create&error=Category name is required');
                exit;
            }

            $result = $this->categoryModel->createCategory($name, $description);
            
            if ($result) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&success=Category created successfully');
                exit;
            } else {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=create&error=Failed to create category');
                exit;
            }
        }
    }

    /**
     * Show edit form
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Invalid category ID');
            exit;
        }

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Category not found');
            exit;
        }

        require_once __DIR__ . '/../view/front office/category/edit.php';
    }

    /**
     * Update an existing category
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Invalid category ID');
                exit;
            }

            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Category not found');
                exit;
            }

            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? null;

            if (empty($name)) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=edit&id=' . $id . '&error=Category name is required');
                exit;
            }

            $result = $this->categoryModel->updateCategory($id, $name, $description);
            
            if ($result) {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&success=Category updated successfully');
                exit;
            } else {
                header('Location: ' . BASE_URL . '/index.php?controller=category&action=edit&id=' . $id . '&error=Failed to update category');
                exit;
            }
        }
    }

    /**
     * Show a single category
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Invalid category ID');
            exit;
        }

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Category not found');
            exit;
        }

        require_once __DIR__ . '/../view/front office/category/show.php';
    }

    /**
     * Delete a category
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?controller=category&action=index&error=Invalid category ID');
            exit;
        }

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header('Location: index.php?controller=category&action=index&error=Category not found');
            exit;
        }

        $result = $this->categoryModel->deleteCategory($id);
        
        if ($result) {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&success=Category deleted successfully');
            exit;
        } else {
            header('Location: ' . BASE_URL . '/index.php?controller=category&action=index&error=Failed to delete category');
            exit;
        }
    }

    /**
     * Search categories
     */
    public function search() {
        $searchTerm = $_GET['q'] ?? '';
        $categories = $this->categoryModel->searchCategories($searchTerm);
        require_once __DIR__ . '/../view/front office/category/list.php';
    }

    /**
     * Filter categories
     */
    public function filter() {
        $filters = $_GET ?? [];
        $categories = $this->categoryModel->filterCategories($filters);
        require_once __DIR__ . '/../view/front office/category/list.php';
    }

    /**
     * Sort categories
     */
    public function sort() {
        $sortBy = $_GET['sort_by'] ?? 'name';
        $order = $_GET['order'] ?? 'ASC';
        $categories = $this->categoryModel->sortCategories($sortBy, $order);
        require_once __DIR__ . '/../view/front office/category/list.php';
    }

    /**
     * Get category statistics
     */
    public function statistics() {
        $stats = $this->categoryModel->getCategoryStatistics();
        require_once __DIR__ . '/../view/front office/category/statistics.php';
    }
}



