<?php
require_once __DIR__ . '/../config/db.php';
include(__DIR__ . '/../Model/event_participation.php');



class EventParticipationController {

    public function listParticipations() {
        $sql = "SELECT * FROM event_participation";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteParticipation(int $id): void {
        $sql = "DELETE FROM event_participation WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addParticipation(event_participation $participation): void {
        $sql = "INSERT INTO event_participation (event_id, user_id) VALUES (:event_id, :user_id)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'event_id' => $participation->getEventId(),
                'user_id' => $participation->getUserId()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateParticipation(event_participation $participation, int $id): void {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE event_participation SET 
                    event_id = :event_id,
                    user_id = :user_id
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'event_id' => $participation->getEventId(),
                'user_id' => $participation->getUserId()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showParticipation(int $id) {
        $sql = "SELECT * FROM event_participation WHERE id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $participation = $query->fetch();
            return $participation;
        } catch(Exception $e) {
            die('Error: '. $e->getMessage());
        }
    }

}
?>