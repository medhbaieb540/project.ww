<?php
class event_participation {
    private ?int $id;
    private ?int $event_id;
    private ?int $user_id;
    // Constructor
    public function __construct(?int $id = null, ?int $event_id = null, ?int $user_id = null) {
        $this->id = $id;
        $this->event_id = $event_id;
        $this->user_id = $user_id;
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getEventId(): ?int {
        return $this->event_id;
    }

    public function setEventId(?int $event_id): void {
        $this->event_id = $event_id;
    }

    public function getUserId(): ?int {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void {
        $this->user_id = $user_id;
    }
}
?>