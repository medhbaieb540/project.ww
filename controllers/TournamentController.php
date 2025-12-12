<?php

require_once __DIR__ . '/../models/Tournament.php';
require_once __DIR__ . '/../models/Reward.php';
require_once __DIR__ . '/../models/Participation.php';

class TournamentController
{
    private $tModel;
    private $rModel;
    private $pModel;
    private $currentUserId;

    public function __construct(PDO $pdo)
    {
        $this->tModel = new Tournament($pdo);
        $this->rModel = new Reward($pdo);
        $this->pModel = new Participation($pdo);
        $this->currentUserId = 1;
    }


    private function fixDate(?string $v): ?string
    {
        if (!$v) return null;
        $v = str_replace('T', ' ', $v);
        if (strlen($v) === 16) {
            $v .= ':00';
        }
        return $v;
    }

    
    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (isset($_POST['dev_add'])) {
            $data = [
                'name'        => $_POST['title'] ?? '',
                'description' => '',
                'start_date'  => $this->fixDate($_POST['start'] ?? null),
                'end_date'    => null,
                'reward_id'   => !empty($_POST['reward_id']) ? (int)$_POST['reward_id'] : null,
                'max_players' => !empty($_POST['max_players']) ? (int)$_POST['max_players'] : 16,
            ];

            if (trim($data['name']) !== '') {
                $this->tModel->create($data);
            }

            header('Location: tournaments.php');
            exit;
        }

        
        if (isset($_POST['action']) && isset($_POST['tournament_id'])) {
            $tid    = (int)$_POST['tournament_id'];
            $action = $_POST['action'];

            $t = $this->tModel->getById($tid);
            if ($t) {
                $maxPlayers = (int)($t['max_players'] ?? 16);

                if ($action === 'join') {
                    // Only allow join if upcoming
                    if ($t['status'] === 'upcoming') {
                        $this->pModel->join($tid, $this->currentUserId, $maxPlayers);
                    }
                } elseif ($action === 'leave') {
                    $this->pModel->leave($tid, $this->currentUserId);
                }
            }

            header('Location: tournaments.php');
            exit;
        }
    }


    public function getTournaments(): array
    {
        return $this->tModel->getAll();
    }

    public function getRewards(): array
    {
        return $this->rModel->getAll();
    }

    public function getParticipationModel(): Participation
    {
        return $this->pModel;
    }

    public function getCurrentUserId(): int
    {
        return $this->currentUserId;
    }
}
