<?php
/**
 * Created by PhpStorm.
 * User: kocak
 * Date: 18.01.2017
 * Time: 20:53
 */

namespace MidoriKocak;


class CalorieUsers extends Users
{
    public function setCalorieLimit($calorieLimit, $email)
    {
        try {
            // TODO: There is some  reponsibility clash. This should be the responsibility of the user class.
            if (empty($this->getCalorieLimit($email))) {
                $update = $this->db->prepare("INSERT INTO users_calories (user_email, calorie_limit) values (:user_email, :calorie_limit)");
            } else {
                $update = $this->db->prepare("UPDATE users_calories SET calorie_limit = :calorie_limit WHERE user_email = :user_email");
            }
            $update->bindParam(':calorie_limit', $calorieLimit, \PDO::PARAM_INT);
            $update->bindParam(':user_email', $email, \PDO::PARAM_STR);
            return $update->execute();
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
        }
    }

    public function getCalorieLimit($email)
    {
        try {
            $select = $this->db->query('SELECT calorie_limit from users_calories WHERE user_email = :user_email');
            $select->bindParam(':user_email', $email);
            $select->setFetchMode(\PDO::FETCH_ASSOC);
            $select->execute();
            return $select->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}