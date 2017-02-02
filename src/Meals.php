<?php

namespace MidoriKocak;

/**
 * Class Meals
 *
 * Acts as a Repository and Data Access Layer for Meals
 *
 * @package MidoriKocak
 */

Class Meals
{
    /**
     * Dependency injection variable for database
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Meals constructor.
     *
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Todo: A Meal should be already validated when constructed.
     * Todo: A json schema could be good for validation. However is another dependency.
     *
     * @param Meal $meal
     * @return int
     */
    public function insert(Meal $meal) : int
    {
        if (empty($meal)) return -1;
        try {
            $insert = $this->db->prepare("INSERT INTO meals (name, date, time, calories) VALUES (:name, :date, :time, :calories)");
            $insert->execute([
                $meal->getName(),
                $meal->getDate(),
                $meal->getTime(),
                $meal->getCalories()
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
            return -1;
        }

    }

    /**
     * @param $id
     * @param Meal $meal
     * @return bool
     */
    public function update($id, Meal $meal) : bool
    {
        try {
            $fields = "";
            $keys = array_keys($meal->toArray());

            for ($i = 0; $i < sizeof($keys); $i++) {
                $fields .= $keys[$i] . " = " . ":" . $keys[$i];
                if ($i != sizeof($keys) - 1) $fields .= ", ";
            }

            $update = $this->db->prepare("UPDATE meals SET " . $fields . " WHERE id = :id");
            $array = $meal->toArray();
            array_walk($array, function (&$item, $key) use ($update) {
                $update->bindParam(':' . $key, $item);
            });

            $update->bindParam(':id', $id, \PDO::PARAM_INT);
            $update->execute();
            return true;
        } catch (\PDOException $e) {
            // TODO: don't echo in class
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id) : bool
    {
        try {
            $update = $this->db->prepare("DELETE FROM meals WHERE id = :id");
            $update->bindParam(':id', $id, \PDO::PARAM_INT);
            $update->execute();
            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Todo: Should return a Meal object.
     *
     * @param $id
     * @return array|Meal
     */
    public function find($id)
    {
        try {
            $select = $this->db->prepare('SELECT * FROM meals WHERE id = :id LIMIT 1');
            $select->bindParam(':id', $id, \PDO::PARAM_INT);
            $select->execute();
            if(!empty($mealData = $select->fetch())){
                return new Meal($mealData);
            }
            return [];
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Todo: Should return a Meal Collection.
     *
     * @return array
     */
    public function findAll():array
    {
        try {
            $select = $this->db->query('SELECT * from meals ORDER BY date DESC, time DESC');
            $select->setFetchMode(\PDO::FETCH_ASSOC);
            $select->execute();
            return $select->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Todo: Should return a Meal Collection.
     *
     * @param $start
     * @param $end
     * @return array
     */
    public function findBetweenDates($start, $end)
    {
        try {
            $selectDate = $this->db->prepare('SELECT * from meals WHERE date BETWEEN :start AND :end  ORDER BY date DESC, time DESC');
            $selectDate->bindParam(':start', $start);
            $selectDate->bindParam(':end', $end);

            $selectDate->setFetchMode(\PDO::FETCH_ASSOC);
            $selectDate->execute();

            $result = $selectDate->fetchAll();

            if(empty($selectDate->fetchAll())){
                $result = ['id'=>'', 'name'=>'', 'date'=>'', 'time'=>'', 'calories'=>''];
            }

            return $result;

        } catch (\PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Todo: Should return a Meal Collection.
     *
     * @return array
     */
    public function findLastWeek()
    {
        $start = date('Y-m-d', strtotime('-1 week'));
        $end = date('Y-m-d', time());
        return $this->findBetweenDates($start, $end);
    }
}