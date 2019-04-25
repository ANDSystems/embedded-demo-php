<?php

namespace App\Controllers;

class BaseController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}