<?php
// controllers/TournamentController.php

require_once __DIR__ . '/../Model/Tournament.php';
require_once __DIR__ . '/../Model/Reward.php';
require_once __DIR__ . '/../Model/Participation.php';

class TournamentController
{
    private PDO $pdo;
    private Tournament $tournamentModel;
    private Reward $rewardModel;
    private Participation $participationModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->tournamentModel = new Tournament($pdo);
        $this->rewardModel = new Reward($pdo);
        $this->participationModel = new Participation($pdo);
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (isset($_POST['dev_add'])) {
            $this->handleDevAdd();
            $this->redirectBack();
            return;
        }

        if (isset($_POST['action'])) {
            $this->handleJoinLeave();
            $this->redirectBack();
            return;
        }
    }

    private function handleDevAdd(): void
    {
        $title      = trim($_POST['title'] ?? '');
        $start      = $_POST['start'] ?? '';
        $end        = $_POST['end'] ?? '';
        $rewardId   = (int)($_POST['reward_id'] ?? 0);
        $maxPlayers = (int)($_POST['max_players'] ?? 16);

        if ($title === '' || $start === '' || $end === '' || $rewardId <= 0) return;
        if (strtotime($end) <= strtotime($start)) return;

        $imagePath = null;

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['image']['tmp_name'];
            $origName = basename($_FILES['image']['name']);

            $uploadDirFs  = __DIR__ . '/../assets/images/tournaments/';
            $uploadDirUrl = '../../assets/images/tournaments/';

            if (!is_dir($uploadDirFs)) {
                mkdir($uploadDirFs, 0777, true);
            }

            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $origName);
            $targetFs = $uploadDirFs . $safeName;

            if (move_uploaded_file($tmpName, $targetFs)) {
                $imagePath = $uploadDirUrl . $safeName;
            }
        }

        $data = [
            'name'        => $title,
            'description' => '',
            'start_date'  => $start,
            'end_date'    => $end,
            'reward_id'   => $rewardId,
            'max_players' => ($maxPlayers >= 2 ? $maxPlayers : 16),
        ];

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        $this->tournamentModel->create($data);
    }

    private function handleJoinLeave(): void
    {
        $action       = $_POST['action'] ?? '';
        $tournamentId = (int)($_POST['tournament_id'] ?? 0);
        $userId       = $this->getCurrentUserId(); // ✅ NOW uses session

        if ($tournamentId <= 0 || $userId <= 0) return;

        if ($action === 'join') {
            $tournament = $this->tournamentModel->getById($tournamentId);
            if (!$tournament) return;

            $maxPlayers = (int)($tournament['max_players'] ?? 0);

            $this->participationModel->join($tournamentId, $userId, $maxPlayers);
            return;
        }

        if ($action === 'leave') {
            $this->participationModel->leave($tournamentId, $userId);
            return;
        }
    }

    public function getTournaments(): array
    {
        return $this->tournamentModel->getAll();
    }

    public function getRewards(): array
    {
        return $this->rewardModel->getAll();
    }

    public function getParticipationModel(): Participation
    {
        return $this->participationModel;
    }

    public function getCurrentUserId(): int
    {
        // ✅ REAL USER FROM SESSION
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return (int)($_SESSION['user_id'] ?? 0);
    }

    private function redirectBack(): void
    {
        header("Location: tournaments.php");
        exit;
    }
}
