<?php
class event {
    private ?int $id;
    private ?string $title;
    private ?string $description;
    private ?int $organizer_id;
    private ?DateTime $eventdate;
    private ?int $max_participants;
    // Constructor
    public function __construct(?int $id = null, ?string $title = null, ?string $description = null, ?int $organizer_id = null, ?DateTime $eventdate = null, ?int $max_participants = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->organizer_id = $organizer_id;
        $this->eventdate = $eventdate;
        $this->max_participants = $max_participants;
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getOrganizerId(): ?int {
        return $this->organizer_id;
    }

    public function setOrganizerId(?int $organizer_id): void {
        $this->organizer_id = $organizer_id;
    }

    public function getEventDate(): ?DateTime {
        return $this->eventdate;
    }

    public function setEventDate(?DateTime $eventdate): void {
        $this->eventdate = $eventdate;
    }

    public function getMaxParticipants(): ?int {
        return $this->max_participants;
    }

    public function setMaxParticipants(?int $max_participants): void {
        $this->max_participants = $max_participants;
    }

}
?>