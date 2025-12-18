<?php
// controllers/AdminTournamentController.php



require_once __DIR__ . '/../Model/Tournament.php';
require_once __DIR__ . '/../Model/Reward.php';



class AdminTournamentController
{
    private PDO $pdo;
    private Tournament $tournamentModel;
    private Reward $rewardModel;

    private array $errors = [];
    private ?string $success = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->tournamentModel = new Tournament($pdo);
        $this->rewardModel     = new Reward($pdo);
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? '';

        // CREATE
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            $this->redirectBack();
            return;
        }

        // UPDATE
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate();
            $this->redirectBack();
            return;
        }

        // DELETE
        if ($action === 'delete' && isset($_GET['id'])) {
            $this->handleDelete((int)$_GET['id']);
            $this->redirectBack();
            return;
        }

        // LIST
        $this->renderList();
    }

    private function handleCreate(): void
    {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate   = $_POST['start_date'] ?? null;
        $endDate     = $_POST['end_date'] ?? null;
        $rewardId    = (int)($_POST['reward_id'] ?? 0);

        if ($name === '') $this->errors[] = "Title is required.";
        if ($rewardId <= 0) $this->errors[] = "Reward is required.";
        if (empty($startDate)) $this->errors[] = "Start time is required.";
        if (empty($endDate)) $this->errors[] = "End time is required.";

        // time range check
        if (!empty($startDate) && !empty($endDate)) {
            if (strtotime($endDate) <= strtotime($startDate)) {
                $this->errors[] = "End time must be AFTER start time.";
            }
        }

        if (!empty($this->errors)) return;

        // upload image (optional)
        $imagePath = $this->uploadTournamentImage('image');

        $data = [
            'name'        => $name,
            'description' => $description,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'reward_id'   => $rewardId,
            'max_players' => 16,
        ];

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        $ok = $this->tournamentModel->create($data);

        if ($ok) $this->success = "Tournament created.";
        else     $this->errors[] = "Failed to create tournament.";
    }

    private function handleUpdate(): void
    {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate   = $_POST['start_date'] ?? null;
        $endDate     = $_POST['end_date'] ?? null;
        $rewardId    = (int)($_POST['reward_id'] ?? 0);

        if ($id <= 0) $this->errors[] = "Invalid tournament id.";
        if ($name === '') $this->errors[] = "Title is required.";
        if ($rewardId <= 0) $this->errors[] = "Reward is required.";
        if (empty($startDate)) $this->errors[] = "Start time is required.";
        if (empty($endDate)) $this->errors[] = "End time is required.";

        // time range check
        if (!empty($startDate) && !empty($endDate)) {
            if (strtotime($endDate) <= strtotime($startDate)) {
                $this->errors[] = "End time must be AFTER start time.";
            }
        }

        if (!empty($this->errors)) return;

        // upload new image if user chose one
        $imagePath = $this->uploadTournamentImage('image');

        $data = [
            'name'        => $name,
            'description' => $description,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'reward_id'   => $rewardId,
            'max_players' => 16,
        ];

        // ✅ only update DB image_path if new image uploaded
        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        $ok = $this->tournamentModel->update($id, $data);

        if ($ok) $this->success = "Tournament updated.";
        else     $this->errors[] = "Failed to update tournament.";
    }

    private function handleDelete(int $id): void
    {
        if ($id <= 0) {
            $this->errors[] = "Invalid tournament id.";
            return;
        }

        $ok = $this->tournamentModel->delete($id);

        if ($ok) $this->success = "Tournament deleted.";
        else     $this->errors[] = "Failed to delete tournament.";
    }

    private function uploadTournamentImage(string $fieldName): ?string
    {
        if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName  = $_FILES[$fieldName]['tmp_name'];
        $origName = basename($_FILES[$fieldName]['name']);

        // folders
        $uploadDirFs  = __DIR__ . '/../assets/images/tournaments/';
        $uploadDirUrl = 'assets/images/tournaments/';

        if (!is_dir($uploadDirFs)) {
            mkdir($uploadDirFs, 0777, true);
        }

        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $origName);
        $targetFs = $uploadDirFs . $safeName;

        if (move_uploaded_file($tmpName, $targetFs)) {
            return $uploadDirUrl . $safeName;
        }

        return null;
    }

    private function renderList(): void
    {
        $tournaments = $this->tournamentModel->getAll();
        $rewards     = $this->rewardModel->getAll();

        // optional (if you want to show messages later)
        $errors  = $this->errors;
        $success = $this->success;

        require __DIR__ . '/../View/BackOffice/admintour.php';

    }

    private function redirectBack(): void
    {
        header("Location: admintour.php");
        exit;
    }
}
