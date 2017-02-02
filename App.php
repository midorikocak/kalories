<?php
require 'vendor/autoload.php';

new class
{
    private $request;

    public function __construct()
    {
        $this->request = new \MidoriKocak\Request();
        $elements = $this->request->urlElements;
        $kalories = new \MidoriKocak\Kalories(new \PDO("sqlite:db/database.db"));
        $parameters = $this->request->parameters;
        if (empty($elements)) {
            echo json_encode(['message' => 'welcome to kalories app api.']);
        } elseif (sizeof($elements) == 1) {
            if ($elements[1] == "meals" && empty($parameters)) {
                $message = $kalories->findAllMeals();
                echo json_encode($message);
            } elseif ($elements[1] == "meals" && isset($parameters['id'])) {
                $message = $kalories->findMeal($parameters['id']);
                echo json_encode($message);
            } elseif (
                $elements[1] == "meals" &&
                (
                    isset($parameters['start-date']) ||
                    isset($parameters['end-date']))
            ) {
                $startDate = !empty($parameters['start-date']) ? $parameters['start-date'] : date('Y-m-d', strtotime('7 days ago'));
                $endDate = !empty($parameters['end-date']) ? $parameters['end-date'] : date('Y-m-d', strtotime('today'));
                $message = $kalories->getMealsBetweenDate($startDate, $endDate);
                echo json_encode($message);
            } elseif ($elements[1] == "meals" && isset($parameters['month']) && isset($parameters['year'])) {
                $message = $kalories->getCaloriesOfMonth($parameters['month'], $parameters['year']);
                echo json_encode($message);
            } elseif ($elements[1] == "meals" && isset($parameters['day'])) {
                // Todo: Should sanitize day input
                $meals = $kalories->getMealsBetweenDate($parameters['day'], $parameters['day']);
                $total = $kalories->getCalorieSumOfDate($parameters['day']);
                $limit = $kalories->getDailyCalorieIntake();
                $status = $total['total'] <= $limit['calorie_limit'] ? 'good' : 'bad';
                $average = floor($total['total'] / sizeof($meals));
                echo json_encode(
                    [
                        'date' => $parameters['day'],
                        'total' => $total['total'],
                        'average' => $average,
                        'status' => $status,
                        'meals' => $meals
                    ]);
            } elseif ($elements[1] == 'user') {
                $message = ['username' => $kalories->getUserName()];
                echo json_encode($message);
            }
        } elseif (sizeof($elements) == 2) {
            if ($elements[1] == "users" && $elements[2] == "login") {
                $message = $kalories->login($parameters['username'], $parameters['password']);
                echo json_encode($message);
            }
            if ($elements[1] == 'user' && $elements[2] == "calorie-limit") {
                echo json_encode($kalories->getDailyCalorieIntake());
            }
            if ($elements[1] == "users" && $elements[2] == "register") {
                $message = $kalories->register($parameters['username'], $parameters['password']);
                echo json_encode($message);
            }
            if ($elements[1] == "users" && $elements[2] == "logout") {
                $message = $kalories->logout();
                echo json_encode($message);
            }
            if ($elements[1] == "users" && $elements[2] == "edit") {
                if (isset($parameters['username']) && $parameters['username'] != '') {
                    $email = filter_var($parameters['username'], FILTER_SANITIZE_EMAIL);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $message = ['message' => 'false input'];
                        if ($email !== $kalories->getUserName()) {
                            $message = $kalories->changeEmail($email);
                        }
                        if (
                            isset($parameters['password1']) &&
                            isset($parameters['password2']) &&
                            ($parameters['password1'] != '') &&
                            ($parameters['password1'] == $parameters['password2'])
                        ) {
                            $message = $kalories->changePassword($parameters['password1']);
                        }
                        echo json_encode($message);
                    } else {
                        $message = ['message' => 'false input'];
                        echo json_encode($message);
                    }
                }
            }
            if ($elements[1] == "meals" && $elements[2] == "delete") {
                $id = file_get_contents("php://input");
                $message = $kalories->deleteMeal($id);
                echo json_encode($message);
            }
            if ($elements[1] == "meals" && $elements[2] == "last-week") {
                $message = $kalories->getLastWeeksMeals();
                echo json_encode($message);
            }
            if ($elements[1] == "meals" && $elements[2] == "add") {
                $message = [];
                if (isset($parameters['id']) && $parameters['id'] == '') {
                    $message = $kalories->addMeal($parameters['name'], $parameters['date'], $parameters['time'], $parameters['calories']);
                } elseif (isset($parameters['id']) && $parameters['id'] != '') {
                    $message = $kalories->updateMeal($parameters['id'], $parameters['name'], $parameters['date'], $parameters['time'], $parameters['calories']);
                }
                echo json_encode($message);
            }
        } elseif (sizeof($elements) == 3) {
            // TODO: All inputs should pass same procedure. Validate & Sanitize
            if ($elements[1] == "users" && $elements[2] == "limit") {
                $limit = filter_var($elements[3], FILTER_SANITIZE_NUMBER_INT);
                if (filter_var($limit, FILTER_VALIDATE_INT)) {
                    $message = $kalories->setDailyCalorieIntake($limit);
                    echo json_encode($message);
                } else {
                    $message = ['message' => 'false input'];
                    echo json_encode($message);
                }
            }
        }
    }

    public function log($msg)
    {
        echo $msg;
    }
};