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

    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];


    if ($search !== '') {
        $sql .= " AND (username LIKE :search OR email LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }


    if ($role !== '') {
        $sql .= " AND user_role = :role";
        $params[':role'] = $role;
    }


    if ($status === 'active') {
        $sql .= " AND (is_banned IS NULL OR is_banned = 0)";
    } elseif ($status === 'banned') {
        $sql .= " AND is_banned = 1";
    }


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



       public function getUserById($id)
{
    $sql = "SELECT * FROM users WHERE id = :id";
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
        $sql = "SELECT * FROM `users` WHERE email = :email";
        $db  = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([':email' => $email]);

        return $query->fetch();   // يرجع صف واحد أو false
    } 
    catch (PDOException $e) {
    
        throw new Exception("Database error (getUserByEmail): " . $e->getMessage());
    }
}



    public function listUsers() {
        $sql = "SELECT * FROM `users` WHERE user_role != 'super_admin'";
        $db = config::getConnexion();
        try {
            $list = $db->prepare($sql);
            $list->execute();
            return $list->fetchAll();

        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }


   public function deleteUser(int $id){
        $sql = "DELETE FROM `users` where id= :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try{
           $req->execute();
        }catch(Exception $e){
            die('Error: '.$e->getMessage());
        }
    }

   public function addUser(User $User) : int 
{
    $sql = "INSERT INTO `users` 
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


      return (int)$db->lastInsertId();
}



   public function update_User($id, $username, $email, $user_role)
{
    $sql = "UPDATE users SET username=:username, email=:email, user_role=:user_role WHERE id=:id";
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
    $sql = "UPDATE users 
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
    $sql = "UPDATE `users` SET is_banned = 1 WHERE id = :id";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([':id' => $id]);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}


public function unbanUser($id) {
    $sql = "UPDATE `users` SET is_banned = 0 WHERE id = :id";
    $db = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([':id' => $id]);
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

public function updateUserPassword($id, $hashedPassword)
{
    $sql = "UPDATE users SET password = :password WHERE id = :id";
    $db  = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            ':password' => $hashedPassword,
            ':id'       => $id
        ]);
    } catch (Exception $e) {
        die("Password update error: " . $e->getMessage());
    }
}
               
public function getLastInsertedId()
{
    $db = config::getConnexion();
    return (int) $db->lastInsertId();
}

 


}



























?>