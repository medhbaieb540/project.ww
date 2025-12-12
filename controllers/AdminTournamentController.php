<?php


require_once __DIR__ . '/../models/Tournament.php';
require_once __DIR__ . '/../models/Reward.php';

class AdminTournamentController
{
    private $tModel;
    private $rModel;

    public function __construct(PDO $pdo)
    {
        $this->tModel = new Tournament($pdo);
        $this->rModel = new Reward($pdo);
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
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $this->handleCreate();
                return;
            } elseif ($action === 'update') {
                $this->handleUpdate();
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
            $this->handleDelete();
            return;
        }

        $this->renderList();
    }

    private function handleCreate(): void
    {
        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_date'  => $this->fixDate($_POST['start_date'] ?? null),
            'end_date'    => null,
            'reward_id'   => !empty($_POST['reward_id']) ? (int)$_POST['reward_id'] : null,
            'max_players' => 16, 
        ];

        if (trim($data['name']) !== '') {
            $this->tModel->create($data);
        }

        header('Location: admin_tournaments.php');
        exit;
    }

    private function handleUpdate(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            header('Location: admin_tournaments.php');
            exit;
        }

        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_date'  => $this->fixDate($_POST['start_date'] ?? null),
            'end_date'    => null,
            'reward_id'   => !empty($_POST['reward_id']) ? (int)$_POST['reward_id'] : null,
            'max_players' => 16,
        ];

        $this->tModel->update($id, $data);

        header('Location: admin_tournaments.php');
        exit;
    }

    private function handleDelete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $this->tModel->delete($id);
        }

        header('Location: admin_tournaments.php');
        exit;
    }

    private function renderList(): void
    {
       
        $tournaments = $this->tModel->getAll();
        $rewards     = $this->rModel->getAll();

        require __DIR__ . '/../views/admin/tournaments/index.php';
    }
}
