<?php
/**
 * Created by PhpStorm.
 * User: kocak
 * Date: 21.01.2017
 * Time: 10:32
 */

namespace MidoriKocak;


class Router
{
    private $request;
    private $allowedRoutes;

    public  function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function addAllowedRoute($route, $data){

    }
}