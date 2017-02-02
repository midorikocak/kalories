<?php
namespace MidoriKocak;

use MidoriKocak\CalorieUsers;
use MidoriKocak\User;
use MidoriKocak\Meal;
use MidoriKocak\UserMeals;

/**
 * Class Kalories
 *
 * Api of the app. Get's raw data as arrays, and returns raw arrays to the view.
 * This class's responsibility is to get request from the user and return reponses.
 *
 * @package MidoriKocak
 */
class Kalories
{
    /**
     * Email
     *
     * @var string
     */
    private $username;

    /**
     * User Manager object
     * @var
     */
    private $users;

    private $mealsManager;

    /**
     * Database object
     *
     * @var \PDO
     */
    private $db;

    /**
     * Kalories constructor.
     *
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->setUsers(new CalorieUsers($this->db));
        if (!isset($_SESSION['username'])) session_start();
    }

    /**
     * Meals Manager Object
     * @param $meals
     */
    public function setMealsManager($meals)
    {
        $this->mealsManager = $meals;
    }

    /**
     * @return mixed
     */
    public function getMealsManager()
    {
        if (!isset($this->mealsManager)) {
            $mealsManager = new UserMeals($this->db);
            $mealsManager->setEmail($this->getUserName());
            $this->setMealsManager($mealsManager);
        }
        return $this->mealsManager;
    }

    /**
     * @param $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        if ($this->users == null) $this->users = $_SESSION['users'];
        return $this->users;
    }

    /**
     * @param $email
     */
    public function setUserName($email)
    {
        $_SESSION['username'] = $email;
        $this->username = $email;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        if ($this->username == null && isset($_SESSION['username']))
            $this->username = $_SESSION['username'];
        return $this->username;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return !empty($this->getUserName());
    }

    /**
     * @param $email
     * @param $password
     * @return array
     */
    public function login($email, $password)
    {
        if (!$this->isLoggedIn() && $this->users->login($email, $password)) {
            $_SESSION['valid'] = true;
            $_SESSION['timeout'] = time();
            $this->setUserName($email);
            $mealsManager = new UserMeals($this->db);
            $mealsManager->setEmail($email);
            $this->setMealsManager($mealsManager);
            return ['message' => 'You are logged in.'];
        } else {
            return ['message' => 'You cannot login'];
        }
    }

    /**
     * @param $email
     * @param $password
     * @return array
     */
    public function register($email, $password)
    {
        if (!$this->isLoggedIn()) {
            $user = new User($email, $password);
            $register = $this->getUsers()->register($user);
            if ($register !== false) {
                return ['message' => 'You are registered, please login'];
            }
        } else {
            return ['message' => 'You cannot be registered'];
        }
    }

    /**
     * @return array
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->setUserName('');
            unset($_SESSION["username"]);
            return ['message' => 'You are logged out'];
        } else {
            return ['message' => 'You are not logged in.'];
        }
    }

    /**
     * @param $name
     * @param $date
     * @param $time
     * @param $calories
     * @return array|bool
     */
    public function addMeal($name, $date, $time, $calories)
    {
        if (!$this->isLoggedIn()) return false;
        $meal = new Meal(['name' => $name, 'date' => $date, 'time' => $time, 'calories' => $calories]);
        if ($this->getMealsManager()->insert($meal) != false) {
            return ['message' => 'Meal added'];
        } else {
            return ['message' => 'Cannot add meal.'];
        }
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function findMeal($id)
    {
        if (!$this->isLoggedIn()) return false;
        $meal = $this->getMealsManager()->find($id);
        if ($meal != false) {
            return $meal;
        } else {
            return ['message' => 'Cannot find meal.'];
        }
    }

    /**
     * @return array|bool
     */
    public function findAllMeals()
    {
        if (!$this->isLoggedIn()) return [];
        $userMeals = $this->getMealsManager()->findAll();
        if (!empty($userMeals)) {
            return $userMeals;
        } else {
            return [];
        }
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function deleteMeal($id)
    {
        if (!$this->isLoggedIn()) return false;
        if ($this->getMealsManager()->delete($id) !== false) {
            return ['message' => 'Meal deleted'];
        } else {
            return ['message' => 'Cannot delete meal.'];
        }
    }

    /**
     * @param $id
     * @param $name
     * @param $date
     * @param $time
     * @param $calories
     * @return array|bool
     */
    public function updateMeal($id, $name, $date, $time, $calories)
    {
        if (!$this->isLoggedIn()) return false;

        $meal = new Meal(['name' => $name, 'date' => $date, 'time' => $time, 'calories' => $calories]);
        if ($this->getMealsManager()->update($id, $meal) !== false) {
            return ['message' => 'Meal updated'];
        } else {
            return ['message' => 'Cannot update meal.'];
        }
    }

    /**
     * @param $password
     * @return array|bool
     */
    public function changePassword($password)
    {
        if (!$this->isLoggedIn()) return false;
        $user = new User($this->getUserName(), $password);
        if ($this->getUsers()->changePassword($user) != false) {
            return ['message' => 'Password changed'];
        } else {
            return ['message' => 'Cannot update password.'];
        }
    }

    /**
     * @param $email
     * @return array|bool
     */
    public function changeEmail($email)
    {
        if (!$this->isLoggedIn()) return false;
        if ($this->getUsers()->changeEmail($this->getUserName(), $email) !== false) {
            $this->setUserName($email);
            return ['message' => 'Email changed'];
        } else {
            return ['message' => 'Cannot update email.'];
        }
    }

    /**
     * @param $calories
     * @return array|bool
     */
    public function setDailyCalorieIntake($calories)
    {
        if (!$this->isLoggedIn()) return false;
        if ($this->getUsers()->setCalorieLimit($calories, $this->getUserName()) !== false) {
            return ['message' => 'Calorie limit changed'];
        } else {
            return ['message' => 'Cannot update calorie limit.'];
        }
    }

    /**
     * @param $calories
     * @return array|bool
     */
    public function getDailyCalorieIntake()
    {
        if (!$this->isLoggedIn()) return false;
        $limit = $this->getUsers()->getCalorieLimit($this->getUserName());
        if ($limit !== false) {
            return $limit;
        } else {
            return ['message' => 'Cannot get calorie limit.'];
        }
    }

    /**
     * @return bool
     */
    public function getTodaysMeals()
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findMealsOfDay(date('Y-m-d', time()));
    }

    /**
     * @return array
     */
    public function getCaloriesOfMonth($month, $year)
    {
        if (!$this->isLoggedIn()) return false;

        $limit = $this->getDailyCalorieIntake();
        $dailyCalories = $this->getMealsManager()->findCaloriesOfMonth($month, $year);

        array_walk($dailyCalories, function (&$item, $key) use ($limit) {
            if ((int)$item['total'] <= $limit['calorie_limit']) {
                $item['status'] = 'good';
            } else {
                $item['status'] = 'bad';
            }
        });

        return $dailyCalories;
    }

    /**
     * @return bool
     */
    public function getTodaysMealsSum()
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findDailyCaloriesSum(date('Y-m-d', time()));
    }

    /**
     * @return bool
     */
    public function getLastWeeksMeals()
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findLastWeek();
    }

    /**
     * @param $date
     * @return bool
     */
    public function getMeals($date)
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findMealsOfDay($date);
    }

    /**
     * @param $date
     * @return bool
     */
    public function getCalorieSumOfDate($date)
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findDailyCaloriesSum($date);
    }

    /**
     * @param $start
     * @param $end
     * @return bool
     */
    public function getMealsBetweenDate($start, $end)
    {
        if (!$this->isLoggedIn()) return false;
        return $this->getMealsManager()->findBetweenDates($start, $end);
    }

}