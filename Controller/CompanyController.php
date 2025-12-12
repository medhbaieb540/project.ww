<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Company.php';

class CompanyController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // ========================
    // Create New Company
    // ========================
    public function addCompany(Company $company)
    {
        $sql = "INSERT INTO company (owner_id, name, description, status)
                VALUES (:owner_id, :name, :description, :status)";

        try {
            $query = $this->db->prepare($sql);
            $query->execute([
                'owner_id'    => $company->getOwnerId(),
                'name'        => $company->getName(),
                'description' => $company->getDescription(),
                'status'      => $company->getStatus()
            ]);

        } catch (Exception $e) {
            die("Error adding company: " . $e->getMessage());
        }
    }

    // ========================
    // Get Company by ID
    // ========================
    public function getCompanyById(int $id)
    {
        $sql = "SELECT * FROM company WHERE id = :id LIMIT 1";

        try {
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die("Error fetching company: " . $e->getMessage());
        }
    }

    // ========================
    // Get Company by Owner ID
    // (كل مطور يملك شركة وحدة فقط)
    // ========================
    public function getCompanyByOwner(int $owner_id)
    {
        $sql = "SELECT * FROM company WHERE owner_id = :owner_id LIMIT 1";

        try {
            $query = $this->db->prepare($sql);
            $query->execute(['owner_id' => $owner_id]);
            return $query->fetch();
        } catch (Exception $e) {
            die("Error fetching company: " . $e->getMessage());
        }
    }

    // ========================
    // Search Company by Name
    // ========================
    public function searchCompany(string $name)
    {
        $sql = "SELECT * FROM company WHERE name LIKE :name";

        try {
            $query = $this->db->prepare($sql);
            $query->execute(['name' => "%$name%"]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die("Error searching company: " . $e->getMessage());
        }
    }
}

