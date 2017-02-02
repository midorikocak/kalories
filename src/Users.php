<?php
/**
 * Created by PhpStorm.
 * User: kocak
 * Date: 18.01.2017
 * Time: 17:37
 */

namespace MidoriKocak;


class Users
{
    protected $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function register(User $user)
    {
        if (empty($user)) return -1;
        try {
            $insert = $this->db->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
            $insert->execute([
                $user->getEmail(),
                $user->getPassword()
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
        }
    }

    public function login($email, $password)
    {
        $user = $this->find($email);
        if (!empty($user)) {
            return $user->getPassword() == $user->generateHash($password);
        }
        return false;
    }

    public function changePassword(User $user)
    {
        try {
            $updatePassword = $this->db->prepare("UPDATE users SET password = :password WHERE email = :email");
            $email = $user->getEmail();
            $password = $user->getPassword();
            $updatePassword->bindParam(':email', $email, \PDO::PARAM_STR);
            $updatePassword->bindParam(':password', $password, \PDO::PARAM_STR);
            return $updatePassword->execute();
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
            return false;
        }
    }

    public function changeEmail($oldEmail, $newEmail)
    {
        try {
            $updatePassword = $this->db->prepare("UPDATE users SET email = :new_email WHERE email = :old_email");
            $updatePassword->bindParam(':new_email', $newEmail, \PDO::PARAM_STR);
            $updatePassword->bindParam(':old_email', $oldEmail, \PDO::PARAM_STR);
            $updatePassword->execute();
            return $updatePassword->execute();
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
        }
    }

    public function find($email)
    {
        try {
            $select = $this->db->prepare('SELECT * FROM users WHERE email = :email');
            $select->bindParam(':email', $email, \PDO::PARAM_STR);
            $select->execute();
            $userData = $select->fetch();
            if (!empty($userData)) {
                $user = new User($userData['email']);
                $user->setPassword($userData['password']);
                return $user;
            }
            return false;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }
}