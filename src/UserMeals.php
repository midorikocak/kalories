<?php

namespace MidoriKocak;


class UserMeals extends Meals
{
    private $email;

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function insert(Meal $meal) : int
    {
        // Todo, should check at some constructor level without breaking liskov.
        if ($this->email == null) return false;
        $mealId = parent::insert($meal);
        try {
            $insertUserMeal = $this->db->prepare("INSERT INTO meals_users (meals_id, users_email) VALUES (:meals_id, :users_email)");
            $insertUserMeal->bindParam(':meals_id', $mealId);
            $insertUserMeal->bindParam(':users_email', $this->email);
            if ($insertUserMeal->execute()) {
                return $mealId;
            }
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
            return -1;
        }
    }

    public function delete($id) : bool
    {
        if (empty($this->find($id))) return false;
        $delete = parent::delete($id);
        return $delete;
    }

    public function find($id)
    {
        if ($this->email == null) return false;
        try {
            $select = $this->db->prepare('
SELECT id, name, date, time, calories FROM meals
INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email AND meals.id = :id');
            $select->bindParam(':id', $id, \PDO::PARAM_INT);
            $select->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $select->execute();
            if (!empty($mealData = $select->fetch())) {
                return new Meal($mealData);
            }
            return [];
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findAll():array
    {
        if ($this->email == null) return [];
        try {
            $select = $this->db->query('SELECT id, name, date, time, calories from meals INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email ORDER BY date DESC, time DESC');
            $select->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $select->setFetchMode(\PDO::FETCH_ASSOC);
            $select->execute();
            return $select->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findMealsOfDay($date)
    {
        if ($this->email == null) return false;
        try {
            $selectDate = $this->db->prepare('SELECT id, name, date, time, calories from meals INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email AND meals.date = :date');
            $selectDate->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $selectDate->bindParam(':date', $date);

            $selectDate->setFetchMode(\PDO::FETCH_ASSOC);
            $selectDate->execute();

            return $selectDate->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function findCaloriesOfMonth($month, $year)
    {
        $start = $year . '-' . $month . '-01';
        $end = $year . '-' . $month . '-31';
        if ($this->email == null) return false;
        try {
            $selectDate = $this->db->prepare('SELECT date, sum(calories) as total from meals INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email AND meals.date BETWEEN :start AND :end GROUP BY date ORDER BY date ASC');
            $selectDate->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $selectDate->bindParam(':start', $start);
            $selectDate->bindParam(':end', $end);

            $selectDate->setFetchMode(\PDO::FETCH_ASSOC);
            $selectDate->execute();
            $result = $selectDate->fetchAll();

            if (empty($result)) {
                $result = [['date' => '', 'total' => '', 'status' => '']];
            }

            return $result;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function findDailyCaloriesSum($date)
    {

        if ($this->email == null) return false;
        try {
            $selectDate = $this->db->prepare('SELECT SUM(calories) as total from meals INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email AND meals.date = :date');
            $selectDate->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $selectDate->bindParam(':date', $date);

            $selectDate->setFetchMode(\PDO::FETCH_ASSOC);
            $selectDate->execute();
            $result = $selectDate->fetch();
            $result['total'] = $result['total'] ?? 0;
            return $result;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }

    public function findBetweenDates($start, $end)
    {
        if ($this->email == null) return false;
        try {
            $selectDate = $this->db->prepare('SELECT id, name, date, time, calories from meals INNER JOIN meals_users
ON meals.id = meals_users.meals_id
WHERE meals_users.users_email = :email AND meals.date BETWEEN :start AND :end  ORDER BY date DESC, time DESC');
            $selectDate->bindParam(':email', $this->email, \PDO::PARAM_STR);
            $selectDate->bindParam(':start', $start);
            $selectDate->bindParam(':end', $end);

            $selectDate->setFetchMode(\PDO::FETCH_ASSOC);
            $selectDate->execute();
            $result = $selectDate->fetchAll();

            if (empty($result)) {
                $result = [['id' => '', 'name' => '', 'date' => '', 'time' => '', 'calories' => '']];
            }

            return $result;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }
}