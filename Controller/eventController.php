<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/event.php');

class eventController {

    public function listevent() {
        $sql = "SELECT * FROM events ORDER BY created_at DESC, id DESC";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            $list = $query->fetchAll();
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteevent($id) {
        $sql = "DELETE FROM events WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addevent(event $event) {
        $sql = "INSERT INTO events (title, description, organizer_id, eventdate, max_participent) VALUES (:title, :description, :organizer_id, :eventdate, :max_participent)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'organizer_id' => $event->getOrganizerId(),
                'eventdate' => $event->getEventDate() ? $event->getEventDate()->format('Y-m-d') : null,
                'max_participent' => $event->getMaxParticipants()
            ]);
            $insertedId = $db->lastInsertId();
        } catch (Exception $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
        return ['ok' => true, 'msg' => 'Inserted', 'id' => $insertedId];
    }

    public function updatevent(event $event, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE events SET 
                    title = :title,
                    description = :description,
                    organizer_id = :organizer_id,
                    eventdate = :eventdate,
                    max_participent = :max_participent
                WHERE id = :id'
            );
            $params = [
                'id' => $id,
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'organizer_id' => $event->getOrganizerId(),
                'eventdate' => $event->getEventDate() ? $event->getEventDate()->format('Y-m-d') : null,
                'max_participent' => $event->getMaxParticipants()
            ];
            // execute and return structured result
            $query->execute($params);
            return ['ok' => true, 'msg' => 'Updated', 'rows' => $query->rowCount(), 'params' => $params];
        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public function getParticipantCount($eventId) {
        $sql = "SELECT COUNT(*) as count FROM event_participation WHERE event_id = :event_id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':event_id', $eventId, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }


    public function showevent($id) {
        $sql = "SELECT * FROM events WHERE id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $event = $query->fetch();
            return $event;
        } catch(Exception $e) {
            die('Error: '. $e->getMessage());
        }
    }
   
}
?>