<?php
// controllers/AdminRewardController.php

require_once __DIR__ . '/../models/Reward.php';

class AdminRewardController
{
    private PDO $pdo;
    private Reward $rewardModel;
    private array $errors = [];
    private ?string $success = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->rewardModel = new Reward($pdo);
    }

    public function handleRequest(): void
    {
        // ADD
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['action'])
            && $_POST['action'] === 'create') {
            $this->handleCreate();
        }

        // DELETE
        if (isset($_GET['action'])
            && $_GET['action'] === 'delete'
            && isset($_GET['id'])) {

            $this->handleDelete((int)$_GET['id']);
        }

        $this->renderList();
    }

    private function handleCreate(): void
    {
        $title = trim($_POST['title'] ?? '');
        $value = $_POST['value'] ?? '';
        $type  = trim($_POST['type'] ?? '');

        if ($title === '') {
            $this->errors[] = "Title is required.";
        }

        if ($value === '' || !is_numeric($value)) {
            $this->errors[] = "Value must be a number.";
        }

        if ($type === '') {
            $this->errors[] = "Type is required.";
        }

        if (!empty($this->errors)) {
            return;
        }

        $ok = $this->rewardModel->create($title, (float)$value, $type);

        if ($ok) {
            $this->success = "Reward added successfully.";
        } else {
            $this->errors[] = "Failed to add reward.";
        }
    }

    private function handleDelete(int $id): void
    {
        if ($id <= 0) {
            $this->errors[] = "Invalid reward id.";
            return;
        }

        // 🔥 NEW: check if reward is used by any tournament
        if ($this->rewardModel->isUsed($id)) {
            $this->errors[] = "You cannot delete this reward because it is used by one or more tournaments.";
            return;
        }

        // Try to delete
        $ok = $this->rewardModel->delete($id);

        if ($ok) {
            $this->success = "Reward deleted.";
        } else {
            // (e.g. some other DB error)
            $this->errors[] = "Could not delete reward.";
        }
    }

    private function renderList(): void
    {
        $rewards = $this->rewardModel->getAll();
        $errors  = $this->errors;
        $success = $this->success;

        require __DIR__ . '/../views/admin/rewards/index.php';
    }
}
