<?php


class User {

        private ?int $id;
        private string $username;
        private string $email;
        private string $password;
        private string $user_role;
        private string $birth_date;
        private string $address;
        private string $gender;
        

        public function __construct(?int $id, string $username, string $email, string $password,string $user_role,string $birth_date,string $address,string $gender){
            $this->id=$id;
            $this->username=$username;
            $this->email=$email;
            $this->password=$password;
            $this->user_role=$user_role;
            $this->birth_date=$birth_date;
            $this->address=$address;
            $this->gender=$gender;
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
         * Get the value of username
         */ 
        public function getUsername()
        {
                return $this->username;
        }

        /**
         * Set the value of username
         *
         * @return  self
         */ 
        public function setUsername($username)
        {
                $this->username = $username;

                return $this;
        }

        /**
         * Get the value of password
         */ 
        public function getPassword()
        {
                return $this->password;
        }

        /**
         * Set the value of password
         *
         * @return  self
         */ 
        public function setPassword($password)
        {
                $this->password = $password;

                return $this;
        }

        /**
         * Get the value of user_role
         */
        public function getUserRole()
        {
                return $this->user_role;
        }               
        /**
         * Set the value of user_role
         *
         * @return  self
         */     
        public function setUserRole($user_role)
        {
                $this->user_role = $user_role;

                return $this;
        }

        /**
         * Get the value of email
         */ 
        public function getEmail()
        {
                return $this->email;
        }

        /**
         * Set the value of email
         *
         * @return  self
         */ 
        public function setEmail($email)
        {
                $this->email = $email;

                return $this;
        }

            /**
         * Get the value of birth_date
         */ 
        public function getBirth_date()
        {
                return $this->birth_date;
        }

        /**
         * Set the value of birth_date
         *
         * @return  self
         */ 
        public function setBirth_date($birth_date)
        {
                $this->birth_date = $birth_date;

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

        /**
         * Get the value of gender
         */ 
        public function getGender()
        {
                return $this->gender;
        }

        /**
         * Set the value of gender
         *
         * @return  self
         */ 
        public function setGender($gender)
        {
                $this->gender = $gender;

                return $this;
        }

        

    
}


?>