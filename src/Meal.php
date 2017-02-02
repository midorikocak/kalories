<?php

namespace MidoriKocak;


/**
 * Class Meal
 * Acts as a Data Transfer object dependent from persistence.
 *
 * @package MidoriKocak
 */
class Meal
{
    /**
     * Name of the meal
     *
     * @var string
     */
    private $name;

    /**
     * Date of the meal. Format: (Y-m-d)
     * Todo: Should be an instance of Date Class
     *
     * @var date
     */
    private $date;

    /**
     * Time of the meal Format: (h:s)
     *
     * @var
     */
    private $time;

    /**
     * Calories. Minimum is 0, Maximum is 500;
     * Todo: Validation for values.
     *
     * @var int
     */
    private $calories;

    /**
     * Meal constructor.
     * Todo: Data array should be validated against undefined indexes and right values.
     * Todo: Should return an invalid argument exception.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setCalories($data['calories']);
        $this->setName($data['name']);
        $this->setDate($data['date']);
        $this->setTime($data['time']);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Todo: Should return a date instance
     *
     * @return string
     */
    public function getDate() : string
    {
        return $this->date;
    }

    /**
     * Todo: Should validate using Date instance
     *
     * @param $date
     */
    public function setDate(string $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getCalories() : int
    {
        return $this->calories;
    }

    /**
     * @param int $calories
     */
    public function setCalories(int $calories)
    {
        $this->calories = $calories;
    }

    /**
     * Returns itself as an array
     *
     * Todo: This is the responsibility of a factory class.
     * Todo: For building a generic factory, reflection can be used.
     *
     * @param Meal|null $item
     * @return array
     */
    function toArray(Meal $item = null): array
    {
        $item = $item ?? $this;
        return [
            'name' => $item->getName(),
            'date' => $item->getDate(),
            'time' => $item->getTime(),
            'calories' => $item->getCalories()
        ];
    }
}