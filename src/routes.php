<?php

use Controllers\PastesController;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\JSONRenderer;

return [
    '' => function (): HTTPRenderer {
        return new HTMLRenderer('home');
    },
    'api/pastes' => function (): HTTPRenderer {
        $controller = new PastesController();
        return match ($_SERVER['REQUEST_METHOD']) {
            'GET' => $controller->index(),
            'POST' => $controller->store(),
            default => new JSONRenderer(['error' => 'Method Not Allowed']),
        };
    },
    '[a-zA-Z0-9_-]{8}' => function (string $hash): HTTPRenderer {
        $controller = new PastesController();
        return $controller->show($hash);
    },
];
