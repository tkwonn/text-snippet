<?php

namespace Controllers;

use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;

class HomeController
{
    public function index(): HTTPRenderer
    {
        return new HTMLRenderer('home');
    }
}
