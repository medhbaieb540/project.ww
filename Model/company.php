<?php

class Company
{
    private ?int $id;
    private ?int $owner_id;
    private string $name;
    private string $description;
    private string $status;
    private string $address;
    private string $created_at;

    public function __construct(
        ?int $id,
        ?int $owner_id,
        string $name,
        string $description,
        string $status,
        string $address,
        string $created_at
    ) {
        $this->id          = $id;
        $this->owner_id    = $owner_id;
        $this->name        = $name;
        $this->description = $description;
        $this->status      = $status;
        $this->address     = $address;
        $this->created_at  = $created_at;
    }



    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of owner_id
     */ 
    public function getOwner_id()
    {
        return $this->owner_id;
    }

    /**
     * Set the value of owner_id
     *
     * @return  self
     */ 
    public function setOwner_id($owner_id)
    {
        $this->owner_id = $owner_id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of address
     */ 
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */ 
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }
}

?>