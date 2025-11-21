<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/User.php');

class UserController {


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



    public function listUsers() {
        $sql = "SELECT * FROM `login`";
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
        $sql = "INSERT INTO `login` (username, email, password, user_role)
                VALUES (:username, :email, :password, :user_role)";

        $db = config::getConnexion();

        $query = $db->prepare($sql);

        $ok = $query->execute([
            ':username'  => $User->getUsername(),
            ':email'     => $User->getEmail(),
            ':password'  => $User->getPassword(),
            ':user_role' => $User->getUserRole()
        ]);

        if (!$ok) {
            $info = $query->errorInfo();
            
            throw new Exception($info[2]);
        }
    }


   public function updateUser($id, $username, $email, $password, $user_role)
{
    $sql = "UPDATE login SET username=:username, email=:email, password=:password, user_role=:user_role WHERE id=:id";
    $db  = config::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'user_role'     => $user_role,
            'id'       => $id
        ]);
    } catch (Exception $e) {
        die("Update error: " . $e->getMessage());
    }
}


 


}



























?>