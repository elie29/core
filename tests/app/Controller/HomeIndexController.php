<?php

namespace App\Controller;

use Elie\Core\Controller\AbstractController;

class HomeIndexController extends AbstractController
{

    public function run(array $params = []): array
    {
        return ['controller' => $this->__toString()];
    }
}
