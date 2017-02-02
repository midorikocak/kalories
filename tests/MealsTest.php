<?php

use MidoriKocak\Meals;
use MidoriKocak\Meal;


class MealsTest extends \PHPUnit_Framework_TestCase
{
    private $meal;
    private $mealData = ['name' => 'Strawberry', 'date' => '2017-01-16', 'time' => '09:51', 'calories' => 300];
    private $mealDataUpdated = ['name' => 'Bread', 'date' => '2017-01-16', 'time' => '09:51', 'calories' => 300];
    private $allData = [
        [
            'id' => 1,
            'name' => 'Bread',
            'date' => '2017-01-16',
            'time' => '09:51',
            'calories' => 300
        ],
        [
            'id' => 2,
            'name' => 'Strawberry',
            'date' => '2017-01-16',
            'time' => '09:51',
            'calories' => 300
        ]
    ];


    private $meals;
    private $id;
    private $db;

    public function setup()
    {
        $this->db = new \PDO("sqlite:/var/www/vhosts/mtkocak.net/httpdocs/kalories/db/database.db");
        $this->meal = new Meal($this->mealData);
        $this->meals = new Meals($this->db);

        $this->db->query('DELETE from meals;');
        $this->db->query('delete from sqlite_sequence where name=\'meals\';');
    }

    public function testAdd()
    {
        $this->id = $this->meals->insert($this->meal);
        $this->assertEquals($this->id, 1);
    }

    public function testAddAnother()
    {
        $this->testEdit();
        $this->id = $this->meals->insert($this->meal);
        $this->assertEquals($this->id, 2);
    }

    public function testFind()
    {
        $this->testAdd();
        $oneMeal = $this->meals->find($this->id);
        $this->assertEquals($this->mealData, $oneMeal->toArray());
    }


    public function testEdit()
    {
        $this->testAdd();
        $newMealUdated = new Meal($this->mealDataUpdated);
        $this->meals->update($this->id, $newMealUdated);
        $mealUpdated = $this->meals->find($this->id);
        $this->assertEquals($this->mealDataUpdated, $mealUpdated->toArray());
    }


    public function testFindAll()
    {
        $this->testAddAnother();
        $this->assertEquals($this->allData, $this->meals->findAll());
    }

    public function testDeletel()
    {
        $this->testFindAll();
        $this->meals->delete(2);
        unset($this->allData[1]);
        $this->assertEquals($this->allData, $this->meals->findAll());
        //var_dump(date('Y-m-d', strtotime('-1 week')));
    }

    public function testFindBetweenDates()
    {
        $this->testAddAnother();

        $firstMeal = $this->meals->find(1);
        $firstMeal->setDate(date('Y-m-d', strtotime('10 days ago')));

        $this->meals->update(1, $firstMeal);

        $lastMeal = $this->meals->find($this->id);
        $lastMeal->setDate(date('Y-m-d', strtotime('3 days ago')));

        $this->meals->update($this->id, $lastMeal);
        $start = date('Y-m-d', strtotime('4 days ago'));
        $end = date('Y-m-d', strtotime('2 days ago'));
        $result = $this->meals->findBetweenDates($start, $end);
        $compare = array_merge(['id' => 2], $lastMeal->toArray());
        $this->assertEquals($compare, $result[0]);
    }

    public function testFindLastWeek()
    {
        $this->testAddAnother();
        $lastMeal = $this->meals->find($this->id);
        $lastMeal->setDate(date('Y-m-d', strtotime('8 days ago')));

        $this->meals->update($this->id, $lastMeal);

        $this->meals->findLastWeek();
        $result = $this->meals->findLastWeek();
        $compare = array_merge(['id' => 1], $this->mealDataUpdated);
        $this->assertEquals($compare, $result[0]);
    }
}