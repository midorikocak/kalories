<?php

use MidoriKocak\Users;
use MidoriKocak\User;


class UsersTest extends \PHPUnit_Framework_TestCase
{
    private $user;
    private $users;
    private $userData = ['email' => 'mtkocak@gmail.com', 'password' => '123456789'];
    private $registeredUser;

    private $userDataChangedEmail = ['email' => 'midori@mynameismidori.com', 'password' => '123456789'];
    private $userDataChangedPassword = ['email' => 'mtkocak@gmail.com', 'password' => 'Ax56-tek'];

    private $db;

    public function setup()
    {
        $this->db = new \PDO("sqlite:kalories/db/database.db");
        $this->users = new Users($this->db);
        $this->user = new User($this->userData['email'], $this->userData['password']);

        $this->db->query('DELETE from users;');
        $this->db->query('delete from sqlite_sequence where name=\'users\';');
    }

    public function testRegister()
    {
        $this->users->register($this->user);
        $this->registeredUser = $this->users->find($this->userData['email']);
        $this->assertEquals($this->user->toArray(), $this->registeredUser->toArray());
    }

    public function testLogin()
    {
        $this->testRegister();
        $this->assertFalse($this->users->login('mtkocak@hasan.com', '123456789'));
        $this->assertFalse($this->users->login('mtkocak@gmail.com', '12345678910'));
        $this->assertTrue($this->users->login('mtkocak@gmail.com', '123456789'));
    }

    public function testChangePassword()
    {
        $this->testLogin();
        $updatedUser = new User($this->userDataChangedPassword['email'], $this->userDataChangedPassword['password']);
        $this->assertTrue($this->users->changePassword($updatedUser));
    }

    public function testChangeEmail()
    {
        $this->testLogin();
        $this->assertTrue($this->users->changeEmail($this->userData['email'] , $this->userDataChangedEmail['email']));
    }
}