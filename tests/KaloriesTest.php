<?php

use MidoriKocak\Kalories;

class KaloriesTest extends \PHPUnit_Framework_TestCase
{
    private $kalories;
    private $userData = ['email' => 'mtkocak@msn.com', 'password' => '987654321'];
    private $mealData = ['name' => 'Strawberry', 'date' => '2017-01-16', 'time' => '09:51', 'calories' => 300];
    private $mealDataUpdated = ['name' => 'Bread', 'date' => '2017-01-16', 'time' => '09:51', 'calories' => 300];
    private $mealDataToDelete = ['name' => 'Banana', 'date' => '2017-01-16', 'time' => '09:51', 'calories' => 300];
    private $db;

    public function setup()
    {
        $this->db = new \PDO("sqlite:db/database.db");
        $this->kalories = new Kalories($this->db);
        $this->cleanDatabase();
    }

    private function cleanDatabase()
    {
        $debug = false;
        if ($debug != true) {
            $this->db->query('DELETE from users;');
            $this->db->query('delete from sqlite_sequence where name=\'users\';');

            $this->db->query('DELETE from meals;');
            $this->db->query('delete from sqlite_sequence where name=\'meals\';');

            $this->db->query('DELETE from meals_users;');
            $this->db->query('delete from sqlite_sequence where name=\'meals_users\';');

            $this->db->query('DELETE from users_calories;');
            $this->db->query('delete from sqlite_sequence where name=\'users_calories\';');
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDatabase();
    }

    public function testRegister()
    {
        $this->assertEquals($this->kalories->register($this->userData['email'], $this->userData['password']), ['message' => 'You are registered, please login']);
    }

    public function testLogin()
    {
        $this->testRegister();
        $this->assertEquals($this->kalories->login($this->userData['email'], $this->userData['password']), ['message' => 'You are logged in.']);
    }


    public function testAddMeal()
    {
        $meal = $this->mealData;
        $result = $this->kalories->addMeal($meal['name'], $meal['date'], $meal['time'], $meal['calories']);
        $this->assertEquals($result, ['message' => 'Meal added']);
    }

    public function testAddAnotherMeal()
    {
        $meal = $this->mealDataUpdated;
        $result = $this->kalories->addMeal($meal['name'], $meal['date'], $meal['time'], $meal['calories']);
        $this->assertEquals($result, ['message' => 'Meal added']);
    }

    public function testFindMeal()
    {
        $this->testAddAnotherMeal();

        $firstMeal = $this->kalories->findMeal(1);
        $this->assertTrue(!empty($firstMeal));
    }

    public function testFindAll()
    {
        $this->testAddMeal();
        $this->testAddAnotherMeal();
        $meal1 = array_merge(['id' => 1], $this->mealData);
        $meal2 = array_merge(['id' => 2], $this->mealDataUpdated);
        $compare = [$meal1, $meal2];
        $meals = $this->kalories->findAllMeals();
        $this->assertEquals($meals, $compare);
    }

    public function testDelete()
    {
        $this->testAddMeal();
        $this->testAddAnotherMeal();
        $meal = $this->mealDataToDelete;
        $result = $this->kalories->addMeal($meal['name'], $meal['date'], $meal['time'], $meal['calories']);
        $result = $this->kalories->deleteMeal(3);
        $this->assertEquals($result, ['message' => 'Meal deleted']);
    }

    public function testGetTodayMeals()
    {
        $result = $this->kalories->addMeal('Orange', date('Y-m-d', time()), date('H:i', time()), 200);
        $meal = $this->kalories->getTodaysMeals();
        $this->assertEquals(date('Y-m-d', time()), $meal[0]['date']);
    }

    public function testGetTodayMealsSum()
    {
        $this->kalories->addMeal('Orange', date('Y-m-d', time()), date('H:i', time()), 200);
        $this->kalories->addMeal('BlueBerry', date('Y-m-d', time()), date('H:i', time()), 500);

        $mealsSum = $this->kalories->getTodaysMealsSum();
        $this->assertEquals($mealsSum['total'], 700);
    }

    public function testFindBetweenDate()
    {
        $this->kalories->addMeal('Orange', date('Y-m-d', strtotime('3 days ago')), date('H:i', time()), 200);
        $this->kalories->addMeal('BlueBerry', date('Y-m-d', strtotime('3 days ago')), date('H:i', time()), 500);
        $start = date('Y-m-d', strtotime('4 days ago'));
        $end = date('Y-m-d', strtotime('2 days ago'));
        $meals = $this->kalories->getMealsBetweenDate($start, $end);
        $this->assertTrue(!empty($meals));
    }

    public function testGetDateMealsSum()
    {
        $this->kalories->addMeal('Orange', date('Y-m-d', strtotime('5 days ago')), date('H:i', time()), 300);
        $this->kalories->addMeal('BlueBerry', date('Y-m-d', strtotime('5 days ago')), date('H:i', time()), 200);

        $mealsSum = $this->kalories->getCalorieSumOfDate(date('Y-m-d', strtotime('5 days ago')));
        $this->assertEquals($mealsSum['total'], 500);
    }

    public function testChangeEmail()
    {
        $this->kalories->logout();
        $this->testRegister();
        $this->testLogin();
        $change = $this->kalories->changeEmail('mtkocak@gmail.com');
        $this->assertEquals($change, ['message' => 'Email changed']);
    }

    public function testGetCalorieLimit()
    {
        $this->kalories->logout();
        $this->testRegister();
        $this->testLogin();
        $change = $this->kalories->setDailyCalorieIntake(1500);
        $limit = $this->kalories->getDailyCalorieIntake();
        $this->assertEquals($limit, ['calorie_limit' => 1500]);
    }

    public function testChangePassword()
    {
        $this->kalories->logout();
        $this->testRegister();
        $this->testLogin();
        $change = $this->kalories->changePassword('Ax56-tek');
        $this->assertEquals($change, ['message' => 'Password changed']);
    }

    public function testLogout()
    {
        $this->assertEquals($this->kalories->logout(), ['message' => 'You are logged out']);
    }
}