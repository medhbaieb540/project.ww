<?php
require_once __DIR__ . '/../model/ReviewModel.php';
require_once __DIR__ . '/../model/GameModel.php';

class ReviewController {
    private $reviewModel;
    private $gameModel;

    
  
    public function __construct($pdo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->reviewModel = new ReviewModel($pdo);
        $this->gameModel = new GameModel($pdo);
    }

    /**
     * List all reviews
     */
    public function index() {
        $reviews = $this->reviewModel->getAllReviews();
        require_once __DIR__ . '/../View/FrontOffice/review/list.php';
    }

    /**
     * Show create form
     */
    public function create() {
        $game_id = $_GET['game_id'] ?? null;
        if (!$game_id) {
            $_SESSION['error'] = 'Game ID is required';
            header('Location: list.php');
            exit;
        }
        
        $game = $this->gameModel->getGameById($game_id);
        if (!$game) {
            $_SESSION['error'] = 'Game not found';
            header('Location: list.php');
            exit;
        }
        
        // Check if user already reviewed this game
        $user_id = $_SESSION['user_id'] ?? 1;
        $existingReview = $this->reviewModel->reviewExists($game_id, $user_id);
        
        require_once __DIR__ . '/../View/FrontOffice/review/create.php';
    }

    /**
     * Store a new review
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'game_id' => $_POST['game_id'] ?? null,
                'user_id' => $_SESSION['user_id'] ?? 1, // Should come from session
                'rating' => $_POST['rating'] ?? null,
                'comment' => $_POST['comment'] ?? ''
            ];

            // Validation
            $errors = $this->validateReview($data);
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                header('Location: review.php?action=create&game_id=' . $data['game_id']);
                exit;
            }

            try {
                $result = $this->reviewModel->createReview($data);
                
                if ($result) {
                    $_SESSION['success'] = 'Review submitted successfully';
                    // Redirect to list page
                    header('Location: list.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Failed to create review';
                    header('Location: review.php?action=create&game_id=' . $data['game_id']);
                    exit;
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $_SESSION['success'] = 'You have already reviewed this game. Your review has been updated.';
                    // Redirect to list page
                    header('Location: list.php');
                } else {
                    $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
                    header('Location: review.php?action=create&game_id=' . $data['game_id']);
                }
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
            $_SESSION['error'] = 'Invalid review ID';
            header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            exit;
        }

        $review = $this->reviewModel->getReviewById($id);
        if (!$review) {
            $_SESSION['error'] = 'Review not found';
            header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            exit;
        }

        require_once __DIR__ . '/../View/FrontOffice/review/edit.php';
    }

    /**
     * Update an existing review
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid review ID';
                header('Location: index.php?controller=review&action=index');
                exit;
            }

            $data = [
                'rating' => $_POST['rating'] ?? null,
                'comment' => $_POST['comment'] ?? ''
            ];

            // Validation
            $errors = $this->validateReview($data, false);
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                header('Location: ' . BASE_URL . '/index.php?controller=review&action=edit&id=' . $id);
                exit;
            }

            $result = $this->reviewModel->updateReview($id, $data);
            
            if ($result) {
                $review = $this->reviewModel->getReviewById($id);
                $_SESSION['success'] = 'Review updated successfully';
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=show&id=' . $review['game_id']);
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update review';
                header('Location: ' . BASE_URL . '/index.php?controller=review&action=edit&id=' . $id);
                exit;
            }
        }
    }

    /**
     * Show a single review
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid review ID';
            header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            exit;
        }

        $review = $this->reviewModel->getReviewById($id);
        if (!$review) {
            $_SESSION['error'] = 'Review not found';
            header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            exit;
        }

        require_once __DIR__ . '/../View/FrontOffice/review/show.php';
    }

    /**
     * Delete a review
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid review ID';
            header('Location: index.php?controller=review&action=index');
            exit;
        }

        $review = $this->reviewModel->getReviewById($id);
        $game_id = $review ? $review['game_id'] : null;

        $result = $this->reviewModel->deleteReview($id);
        
        if ($result) {
            $_SESSION['success'] = 'Review deleted successfully';
            if ($game_id) {
                header('Location: ' . BASE_URL . '/index.php?controller=game&action=show&id=' . $game_id);
            } else {
                header('Location: ' . BASE_URL . '/index.php?controller=review&action=index');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Failed to delete review';
            header('Location: index.php?controller=review&action=index');
            exit;
        }
    }

    /**
     * Search reviews
     */
    public function search() {
        $searchTerm = $_GET['q'] ?? '';
        $reviews = $this->reviewModel->searchReviews($searchTerm);
        require_once __DIR__ . '/../View/FrontOffice/review/list.php';
    }

    /**
     * Filter reviews by rating
     */
    public function filter() {
        $minRating = $_GET['rating'] ?? 1;
        $reviews = $this->reviewModel->getReviewsByRating($minRating);
        require_once __DIR__ . '/../View/FrontOffice/review/list.php';
    }

    /**
     * Get review statistics
     */
    public function statistics() {
        $stats = $this->reviewModel->getReviewStatistics();
        require_once __DIR__ . '/../View/FrontOffice/review/statistics.php';
    }

    /**
     * Validate review data
     */
    private function validateReview($data, $requireGameId = true) {
        $errors = [];

        if ($requireGameId && empty($data['game_id'])) {
            $errors[] = 'Game ID is required';
        }

        if (empty($data['rating']) || !is_numeric($data['rating'])) {
            $errors[] = 'Rating is required and must be a number';
        } elseif ($data['rating'] < 1 || $data['rating'] > 5) {
            $errors[] = 'Rating must be between 1 and 5';
        }

        if (empty($data['comment'])) {
            $errors[] = 'Comment is required';
        } elseif (strlen($data['comment']) < 10) {
            $errors[] = 'Comment must be at least 10 characters long';
        } elseif (strlen($data['comment']) > 1000) {
            $errors[] = 'Comment must not exceed 1000 characters';
        }

        return $errors;
    }
}
?>

