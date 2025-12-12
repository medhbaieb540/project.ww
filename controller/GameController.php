<?php
require_once __DIR__ . '/../model/GameModel.php';
require_once __DIR__ . '/../model/CategoryModel.php';
require_once __DIR__ . '/../model/ReviewModel.php';

class GameController {
    private $gameModel;
    private $categoryModel;
    private $reviewModel;
    private $uploadDir = __DIR__ . '/../uploads/';
    private $imageDir = __DIR__ . '/../uploads/images/';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->gameModel = new GameModel();
        $this->categoryModel = new CategoryModel();
        $this->reviewModel = new ReviewModel();
        
        // Create upload directories if they don't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        if (!file_exists($this->imageDir)) {
            mkdir($this->imageDir, 0777, true);
        }
    }

    /**
     * List all games
     */
    public function index() {
        try {
            $games = $this->gameModel->getAllGames();
            $categories = $this->categoryModel->getAllCategories();
        } catch (Exception $e) {
            $games = [];
            $categories = [];
            $_SESSION['error'] = 'Database connection error. Please check your database configuration.';
        }
        
        if (!isset($games)) $games = [];
        if (!isset($categories)) $categories = [];
        
        require_once __DIR__ . '/../view/front office/game/list.php';
    }

    /**
     * Show create form
     */
    public function create() {
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/create.php';
    }

    /**
     * Store a new game
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'developer_id' => $_SESSION['user_id'] ?? 1,
                'category_id' => $_POST['category_id'] ?? null,
                'image_path' => '',
                'file_path' => ''
            ];

            // Validate input
            $errors = $this->validateGame($data);
            
            // Validate image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if ($imageValidation !== true) {
                    $errors[] = $imageValidation;
                }
            } else {
                $errors[] = 'Game image is required';
            }

            // Validate game file upload
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $fileValidation = $this->validateGameFile($_FILES['game_file']);
                if ($fileValidation !== true) {
                    $errors[] = $fileValidation;
                }
            } else {
                $errors[] = 'Game file is required';
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: index.php?controller=game&action=create');
                exit;
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->uploadFile($_FILES['image'], $this->imageDir, ['jpg', 'jpeg', 'png', 'gif']);
                if ($imagePath) {
                    $data['image_path'] = $imagePath;
                } else {
                    $_SESSION['error'] = 'Failed to upload image';
                    header('Location: index.php?controller=game&action=create');
                    exit;
                }
            }

            // Handle game file upload
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $filePath = $this->uploadFile($_FILES['game_file'], $this->uploadDir, ['zip', 'rar', 'exe', 'apk']);
                if ($filePath) {
                    $data['file_path'] = $filePath;
                } else {
                    $_SESSION['error'] = 'Failed to upload game file';
                    header('Location: index.php?controller=game&action=create');
                    exit;
                }
            }

            $result = $this->gameModel->createGame($data);
            
            if ($result) {
                $_SESSION['success'] = 'Game created successfully';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create game';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=create');
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
            $_SESSION['error'] = 'Invalid game ID';
            header('Location: index.php?controller=game&action=index');
            exit;
        }

        $game = $this->gameModel->getGameById($id);
        if (!$game) {
            $_SESSION['error'] = 'Game not found';
            header('Location: index.php?controller=game&action=index');
            exit;
        }

        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/edit.php';
    }

    /**
     * Update an existing game
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid game ID';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
                exit;
            }

            $game = $this->gameModel->getGameById($id);
            if (!$game) {
                $_SESSION['error'] = 'Game not found';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
                exit;
            }

            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category_id' => $_POST['category_id'] ?? null,
                'image_path' => $game['image_path'],
                'file_path' => $game['file_path']
            ];

            // Validate input
            $errors = $this->validateGame($data);
            
            // Validate image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if ($imageValidation !== true) {
                    $errors[] = $imageValidation;
                }
            }

            // Validate game file upload if provided
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $fileValidation = $this->validateGameFile($_FILES['game_file']);
                if ($fileValidation !== true) {
                    $errors[] = $fileValidation;
                }
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: index.php?controller=game&action=edit&id=' . $id);
                exit;
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->uploadFile($_FILES['image'], $this->imageDir, ['jpg', 'jpeg', 'png', 'gif']);
                if ($imagePath) {
                    if (!empty($game['image_path']) && file_exists(__DIR__ . '/../' . $game['image_path'])) {
                        unlink(__DIR__ . '/../' . $game['image_path']);
                    }
                    $data['image_path'] = $imagePath;
                }
            }

            // Handle game file upload
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $filePath = $this->uploadFile($_FILES['game_file'], $this->uploadDir, ['zip', 'rar', 'exe', 'apk']);
                if ($filePath) {
                    if (!empty($game['file_path']) && file_exists(__DIR__ . '/../' . $game['file_path'])) {
                        unlink(__DIR__ . '/../' . $game['file_path']);
                    }
                    $data['file_path'] = $filePath;
                }
            }

            $result = $this->gameModel->updateGame($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Game updated successfully';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update game';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=edit&id=' . $id);
                exit;
            }
        }
    }

    /**
     * Show a single game
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid game ID';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
            exit;
        }

        $game = $this->gameModel->getGameById($id);
        if (!$game) {
            $_SESSION['error'] = 'Game not found';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
            exit;
        }

        $reviews = $this->reviewModel->getReviewsByGame($id);
        require_once __DIR__ . '/../view/front office/game/show.php';
    }

    /**
     * Delete a game (soft delete - move to trash)
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid game ID';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=index');
            exit;
        }

        $result = $this->gameModel->deleteGame($id);
        
        if ($result) {
            $_SESSION['success'] = 'Game moved to trash successfully';
            header('Location: index.php?controller=game&action=index');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to delete game';
            header('Location: index.php?controller=game&action=index');
            exit;
        }
    }

    /**
     * Show trash page with deleted games
     */
    public function trash() {
        $deletedGames = $this->gameModel->getDeletedGames();
        $deletedCount = $this->gameModel->getDeletedGamesCount();
        require_once __DIR__ . '/../view/back office/trash.php';
    }

    /**
     * Restore a game from trash
     */
    public function restore() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid game ID';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        }

        $result = $this->gameModel->restoreGame($id);
        
        if ($result) {
            $_SESSION['success'] = 'Game restored successfully';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to restore game';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        }
    }

    /**
     * Permanently delete a game from trash
     */
    public function permanentDelete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid game ID';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        }

        $game = $this->gameModel->getGameById($id, true);
        if ($game) {
            if (!empty($game['image_path']) && file_exists(__DIR__ . '/../' . $game['image_path'])) {
                unlink(__DIR__ . '/../' . $game['image_path']);
            }
            if (!empty($game['file_path']) && file_exists(__DIR__ . '/../' . $game['file_path'])) {
                unlink(__DIR__ . '/../' . $game['file_path']);
            }
        }

        $result = $this->gameModel->permanentlyDeleteGame($id);
        
        if ($result) {
            $_SESSION['success'] = 'Game permanently deleted';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to permanently delete game';
            header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
            exit;
        }
    }

    /**
     * Empty trash - permanently delete all deleted games
     */
    public function emptyTrash() {
        $deletedGames = $this->gameModel->getDeletedGames();
        $deletedCount = 0;
        
        foreach ($deletedGames as $game) {
            if (!empty($game['image_path']) && file_exists(__DIR__ . '/../' . $game['image_path'])) {
                unlink(__DIR__ . '/../' . $game['image_path']);
            }
            if (!empty($game['file_path']) && file_exists(__DIR__ . '/../' . $game['file_path'])) {
                unlink(__DIR__ . '/../' . $game['file_path']);
            }
            
            if ($this->gameModel->permanentlyDeleteGame($game['game_id'])) {
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $_SESSION['success'] = "Permanently deleted {$deletedCount} game(s) from trash";
        } else {
            $_SESSION['error'] = 'No games to delete or failed to delete';
        }
        
        header('Location: ' . BASE_URL . '/index.php?controller=game&action=trash');
        exit;
    }

    /**
     * Search games
     */
    public function search() {
        $searchTerm = trim($_GET['q'] ?? '');
        
        if (empty($searchTerm)) {
            // If search term is empty, show all games
            $games = $this->gameModel->getAllGames();
        } else {
            $games = $this->gameModel->searchGames($searchTerm);
        }
        
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/list.php';
    }

    /**
     * Filter games
     */
    public function filter() {
        $category_id = $_GET['category_id'] ?? null;
        $minRating = $_GET['min_rating'] ?? null;
        $games = $this->gameModel->filterGames($category_id, $minRating);
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/list.php';
    }

    /**
     * Sort games
     */
    public function sort() {
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $order = $_GET['order'] ?? 'DESC';
        $games = $this->gameModel->sortGames($sortBy, $order);
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/list.php';
    }

    /**
     * Get game statistics
     */
    public function statistics() {
        $stats = $this->gameModel->getGameStatistics();
        $games = $this->gameModel->getAllGames();
        $categories = $this->categoryModel->getAllCategories();
        require_once __DIR__ . '/../view/front office/game/statistics.php';
    }

    /**
     * Export games to CSV
     */
    public function export() {
        $games = $this->gameModel->getAllGames();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="games_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['ID', 'Title', 'Description', 'Category', 'Developer', 'Rating', 'Created Date', 'Image Path', 'File Path']);
        
        // CSV data
        foreach ($games as $game) {
            fputcsv($output, [
                $game['game_id'],
                $game['title'],
                $game['description'],
                $game['category_name'] ?? 'Uncategorized',
                $game['developer_name'] ?? 'Unknown',
                $game['average_rating'] ?? 0,
                $game['created_at'],
                $game['image_path'] ?? '',
                $game['file_path'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Back office - List all games
     */
    public function backOfficeIndex() {
        require_once __DIR__ . '/../view/back office/game/list.php';
    }

    /**
     * Dashboard - Main management dashboard
     */
    public function dashboard() {
        $games = $this->gameModel->getAllGames();
        $categories = $this->categoryModel->getAllCategories();
        $reviews = $this->reviewModel->getAllReviews();
        
        // Get statistics
        $stats = $this->gameModel->getGameStatistics();
        $reviewStats = $this->reviewModel->getReviewStatistics();
        
        require_once __DIR__ . '/../view/back office/dashboard.php';
    }

    /**
     * Games Dashboard - Admin-style games dashboard
     */
    public function gamesDashboard() {
        $games = $this->gameModel->getAllGames();
        $categories = $this->categoryModel->getAllCategories();
        $reviews = $this->reviewModel->getAllReviews();
        
        // Get statistics
        $stats = $this->gameModel->getGameStatistics();
        $reviewStats = $this->reviewModel->getReviewStatistics();
        
        require_once __DIR__ . '/../view/back office/games_dashboard.php';
    }

    /**
     * Validate game data
     */
    private function validateGame($data) {
        $errors = [];

        // Title validation
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } else {
            $titleLength = strlen($data['title']);
            if ($titleLength < 3) {
                $errors[] = 'Title must be at least 3 characters long';
            } elseif ($titleLength > 100) {
                $errors[] = 'Title must not exceed 100 characters';
            }
            
            // Check if title contains only letters, numbers, spaces, and common punctuation
            if (!preg_match('/^[a-zA-Z0-9\s\-_.,!?()]+$/', $data['title'])) {
                $errors[] = 'Title contains invalid characters. Only letters, numbers, spaces, and basic punctuation are allowed';
            }
        }

        // Description validation
        if (empty($data['description'])) {
            $errors[] = 'Description is required';
        } else {
            $descLength = strlen($data['description']);
            if ($descLength < 20) {
                $errors[] = 'Description must be at least 20 characters long';
            } elseif ($descLength > 2000) {
                $errors[] = 'Description must not exceed 2000 characters';
            }
        }

        // Category validation
        if (empty($data['category_id'])) {
            $errors[] = 'Category is required';
        }

        return $errors;
    }

    /**
     * Validate image upload
     */
    private function validateImage($file) {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Image upload error';
        }

        if ($file['size'] > $maxSize) {
            return 'Image size must not exceed 5MB';
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExtensions)) {
            return 'Invalid image format. Only JPG, PNG, and GIF are allowed';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return 'Invalid image type';
        }

        return true;
    }

    /**
     * Validate game file upload
     */
    private function validateGameFile($file) {
        $maxSize = 500 * 1024 * 1024; // 500MB
        $allowedExtensions = ['zip', 'rar', 'exe', 'apk'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Game file upload error';
        }

        if ($file['size'] > $maxSize) {
            return 'Game file size must not exceed 500MB';
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExtensions)) {
            return 'Invalid game file format. Only ZIP, RAR, EXE, and APK are allowed';
        }

        return true;
    }

    /**
     * Handle file upload
     */
    private function uploadFile($file, $targetDir, $allowedExtensions = []) {
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileError = $file['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            return false;
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!empty($allowedExtensions) && !in_array($fileExt, $allowedExtensions)) {
            return false;
        }

        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
        $targetPath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $targetPath)) {
            return 'uploads/' . (strpos($targetPath, 'images') !== false ? 'images/' : '') . $newFileName;
        }

        return false;
    }
}
?>
