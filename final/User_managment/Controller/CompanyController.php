<?php
require_once __DIR__ . '/../Model/Company.php';

class CompanyController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addCompany(Company $company): void
    {
        $sql = "INSERT INTO company (owner_id, name, description, status, address)
                VALUES (:owner_id, :name, :description, :status, :address)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'owner_id'    => $company->getOwner_id(),
            'name'        => $company->getName(),
            'description' => $company->getDescription(),
            'status'      => $company->getStatus(),
            'address'     => $company->getAddress(),
        ]);
    }

    public function getCompanyById(int $id)
    {
        $sql = "SELECT * FROM company WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getCompanyByOwner(int $owner_id)
    {
        $sql = "SELECT * FROM company WHERE owner_id = :owner_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['owner_id' => $owner_id]);
        return $stmt->fetch();
    }

    public function searchCompany(string $name): array
    {
        $sql = "SELECT * FROM company WHERE name LIKE :name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => "%$name%"]);
        return $stmt->fetchAll();
    }

    // ✅ هذا هو اللي يحتاجه company_search.php
    public function findByName(string $name): array
    {
        $sql = "SELECT id, name, status
                FROM company
                WHERE name LIKE :q
                ORDER BY name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['q' => "%$name%"]);
        return $stmt->fetchAll();
    }

    public function joinCompany(int $companyId, int $userId): void
    {
        $sql = "INSERT INTO company_members (company_id, user_id, role)
                VALUES (:company_id, :user_id, 'developer')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'user_id'    => $userId,
        ]);
    }
}
