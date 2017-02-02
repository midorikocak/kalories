<?php
/**
 * Created by PhpStorm.
 * User: kocak
 * Date: 18.01.2017
 * Time: 17:29
 */

namespace MidoriKocak;


class User
{
    protected $email;
    private $password;

    public function __construct($email, $password = null)
    {
        $this->setEmail($email);
        if ($password != null) $this->setPassword($this->generateHash($password));
    }

    public function setEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException(
                'email is not valid'
            );
        }
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function generateHash($password)
    {
        if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
            // Todo: salt should be changed by time and saved to persistence. Paranoia.
            // Todo: . substr(md5(uniqid(rand(), true)), 0, 22)
            $salt = 'wdw#$£wew987234^+';
            return crypt(md5($password), $salt);
        }
    }

    /**
     * Returns itself as an array
     *
     * Todo: This is the responsibility of a factory class.
     * Todo: For building such factory, reflection can be used.
     *
     * @param Meal|null $item
     * @return array
     */
    function toArray(User $user = null): array
    {
        $user = $user ?? $this;
        return [
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ];
    }
}