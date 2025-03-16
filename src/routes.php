<?php

use Controllers\HomeController;
use Controllers\PastesController;
use Exceptions\HttpException;
use Response\HTTPRenderer;

return [
    '' => function (): HTTPRenderer {
        $homeController = new HomeController();

        return $homeController->index();
    },
    'api/pastes' => function (): HTTPRenderer {
        $pastesController = new PastesController();

        return match ($_SERVER['REQUEST_METHOD']) {
            'GET' => $pastesController->index(),
            'POST' => $pastesController->store(),
            default => throw new HttpException(405, 'Must be GET or POST'),
        };
    },
    '[a-zA-Z0-9_-]{8}' => function (string $hash): HTTPRenderer {
        $pastesController = new PastesController();

        return $pastesController->show($hash);
    },
];
