<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/User.php');

class UserController {



    public function listUsersAdvanced(
    string $search = '',
    string $role = '',
    string $status = '',
    ?string $sortField = null,
    string $sortDir = 'ASC'
) {
    $db = config::getConnexion();

    $sql = "SELECT * FROM login WHERE 1=1";
    $params = [];

    // 🔍 search by username or email
    if ($search !== '') {
        $sql .= " AND (username LIKE :search OR email LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // 🎭 filter by role
    if ($role !== '') {
        $sql .= " AND user_role = :role";
        $params[':role'] = $role;
    }

    // 🚫 filter by status (active / banned)
    if ($status === 'active') {
        $sql .= " AND (is_banned IS NULL OR is_banned = 0)";
    } elseif ($status === 'banned') {
        $sql .= " AND is_banned = 1";
    }

    // 🔽 sorting
    $allowedSort = ['username', 'email', 'user_role', 'birth_date'];
    $sortDir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

    if ($sortField !== null && in_array($sortField, $allowedSort, true)) {
        $sql .= " ORDER BY $sortField $sortDir";
    } else {
        $sql .= " ORDER BY id DESC";
    }

    $query = $db->prepare($sql);
    $query->execute($params);

    return $query->fetchAll(PDO::FETCH_ASSOC);
}


    public function searchUsers($keyword)
{
    $sql = "SELECT * FROM login 
            WHERE username LIKE :key 
               OR email LIKE :key";

    $db = config::getConnexion();
    $query = $db->prepare($sql);

    $searchWord = "%" . $keyword . "%";

    $query->execute([
        ':key' => $searchWord
    ]);

    return $query->fetchAll();
}


public function listUsers($sortField = null, $sortDir = 'ASC')
{
    $db = config::getConnexion();

    // الحقول المسموح نرتّب عليها
    $allowedFields = ['id', 'username', 'email', 'birth_date', 'user_role'];
    $allowedDir    = ['ASC', 'DESC'];

    if (!in_array($sortField, $allowedFields)) {
        $sortField = 'id';   // ديفولت
    }

    $sortDir = strtoupper($sortDir);
    if (!in_array($sortDir, $allowedDir)) {
        $sortDir = 'ASC';
    }

    // انتبه: لازم نتحقق من الأسماء قبل ما نحطهم في الـ SQL
    $sql = "SELECT * FROM login ORDER BY $sortField $sortDir";

    try {
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Error fetching users: " . $e->getMessage());
    }
}

public function filterUsers($role = null, $status = null)
{
    $db = config::getConnexion();

    $sql = "SELECT * FROM login WHERE 1=1";
    $params = [];

    // Filter by Role
    if (!empty($role)) {
        $sql .= " AND user_role = :role";
        $params[':role'] = $role;
    }

    // Filter by status (Active / Banned)
    if ($status === "active") {
        $sql .= " AND (is_banned IS NULL OR is_banned = 0)";
    } elseif ($status === "banned") {
        $sql .= " AND is_banned = 1";
    }

    $sql .= " ORDER BY id DESC";

    try {
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Filter error: " . $e->getMessage());
    }
}



       public function getUserById($id)
{
    $sql = "SELECT * FROM login WHERE id = :id";
    $db  = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute(['id'=>$id]);
        return $query->fetch();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

public function getUserByEmail(string $email)
{
    try {
        $sql = "SELECT * FROM `login` WHERE email = :email";
        $db  = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([':email' => $email]);

        return $query->fetch();   // يرجع صف واحد أو false
    } 
    catch (PDOException $e) {
    
        throw new Exception("Database error (getUserByEmail): " . $e->getMessage());
    }
}



    // public function listUsers() {
    //     $sql = "SELECT * FROM `login` WHERE user_role != 'super_admin'";
    //     $db = config::getConnexion();
    //     try {
    //         $list = $db->prepare($sql);
    //         $list->execute();
    //         return $list->fetchAll();

    //     } catch (Exception $e) {
    //         die('Erreur: ' . $e->getMessage());
    //     }
    // }


   public function deleteUser(int $id){
        $sql = "DELETE FROM `login` where id= :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try{
           $req->execute();
        }catch(Exception $e){
            die('Error: '.$e->getMessage());
        }
    }

   public function addUser(User $User)
{
    $sql = "INSERT INTO `login` 
            (username, email, password, user_role, birth_date, address, gender)
            VALUES 
            (:username, :email, :password, :user_role, :birth_date, :address, :gender)";

    $db = config::getConnexion();
    $query = $db->prepare($sql);

    $ok = $query->execute([
        ':username'  => $User->getUsername(),
        ':email'     => $User->getEmail(),
        ':password'  => $User->getPassword(),
        ':user_role' => $User->getUserRole(),
        ':birth_date' => $User->getBirth_date(), 
        ':address'   => $User->getAddress(),
        ':gender'    => $User->getGender()
    ]);

    if (!$ok) {
        $info = $query->errorInfo();
        throw new Exception($info[2]);
    }
}



   public function update_User($id, $username, $email, $user_role)
{
    $sql = "UPDATE login SET username=:username, email=:email, user_role=:user_role WHERE id=:id";
    $db  = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'username' => $username,
            'email'    => $email,
            'user_role'     => $user_role,
            'id'       => $id
        ]);
    } catch (Exception $e) {
        die("Update error: " . $e->getMessage());
    }
}
public function updateUser($id, $username, $email, $user_role, $birth_date, $address, $gender)
{
    $sql = "UPDATE login 
            SET 
                username = :username, 
                email = :email,
                user_role = :user_role,
                birth_date = :birth_date,
                address = :address,
                gender = :gender
            WHERE id = :id";

    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'username'  => $username,
            'email'     => $email,
            'user_role' => $user_role,
            'gender'    => $gender,
            'birth_date' => $birth_date,
            'address'   => $address,
            'id'        => $id
        ]);

    } catch (Exception $e) {
        die("Update error: " . $e->getMessage());
    }
}



    public function banUser($id) {
    $sql = "UPDATE `login` SET is_banned = 1 WHERE id = :id";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([':id' => $id]);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

public function unbanUser($id) {
    $sql = "UPDATE `login` SET is_banned = 0 WHERE id = :id";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([':id' => $id]);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}


 


}



























?>