<?php
// User_managment/Controller/eventController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/event.php';

class eventController
{
    private PDO $pdo;

    // ✅ constructor gets PDO
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listevent()
    {
        $sql = "SELECT * FROM events ORDER BY created_at DESC, id DESC";
        try {
            $query = $this->pdo->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteevent($id)
    {
        $sql = "DELETE FROM events WHERE id = :id";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id', (int)$id, PDO::PARAM_INT);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addevent(event $event)
    {
        $sql = "INSERT INTO events (title, description, organizer_id, eventdate, max_participants)
                VALUES (:title, :description, :organizer_id, :eventdate, :max_participants)";

        try {
            $query = $this->pdo->prepare($sql);
            $query->execute([
                'title'           => $event->getTitle(),
                'description'     => $event->getDescription(),
                'organizer_id'    => $event->getOrganizerId(),
                'eventdate'       => $event->getEventDate() ? $event->getEventDate()->format('Y-m-d') : null,
                'max_participants' => $event->getMaxParticipants()
            ]);

            $insertedId = (int)$this->pdo->lastInsertId();
            return ['ok' => true, 'msg' => 'Inserted', 'id' => $insertedId];

        } catch (Exception $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public function updatevent(event $event, $id)
    {
        try {
            $query = $this->pdo->prepare(
                "UPDATE events SET
                    title = :title,
                    description = :description,
                    organizer_id = :organizer_id,
                    eventdate = :eventdate,
                    max_participants = :max_participants
                 WHERE id = :id"
            );

            $params = [
                'id'              => (int)$id,
                'title'           => $event->getTitle(),
                'description'     => $event->getDescription(),
                'organizer_id'    => $event->getOrganizerId(),
                'eventdate'       => $event->getEventDate() ? $event->getEventDate()->format('Y-m-d') : null,
                'max_participants' => $event->getMaxParticipants()
            ];

            $query->execute($params);

            return [
                'ok'    => true,
                'msg'   => 'Updated',
                'rows'  => $query->rowCount(),
                'params'=> $params
            ];

        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public function getParticipantCount($eventId)
    {
        $sql = "SELECT COUNT(*) as count FROM event_participation WHERE event_id = :event_id";

        try {
            $query = $this->pdo->prepare($sql);
            $query->bindValue(':event_id', (int)$eventId, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0);

        } catch (Exception $e) {
            return 0;
        }
    }

    public function showevent($id)
    {
        $sql = "SELECT * FROM events WHERE id = :id";
        $query = $this->pdo->prepare($sql);

        try {
            $query->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}