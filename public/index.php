<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';


//for phtml files
$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

//init App with requires
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
//for flash messages
session_start();
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});
//names for routing
$router = $app->getRouteCollector()->getRouteParser();

//bd for data
$repo = new App\SchoolRepository();

$app->get("/", function ($request, $response) {
    return $this->get('renderer')->render($response, "index.phtml");
})->setName("main");

$app->get("/schools", function ($request, $response) use ($repo) {
    $schoolData = $repo->read();
    $flash = $this->get('flash')->getMessages();
    $params = [
        'schoolData' => $schoolData,
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, "schools/index.phtml", $params);
})->setName("schools");

$app->get("/schools/new", function ($request, $response) {
    $params = [
        'errors' => [],
        'schoolData' => []
    ];
    return $this->get('renderer')->render($response, "schools/new.phtml", $params);
})->setName("school");

$app->post("/schools", function ($request, $response) use ($router, $repo) {
    $schoolData = $request->getParsedBodyParam('school');
    $validator = new \App\Validator();
    $errors = $validator->validate($schoolData);
    if (count($errors) === 0) {
        $repo->save($schoolData);
        $this->get('flash')->addMessage('success', 'School has been added');
        $url = $router->urlFor("schools");
        return $response->withRedirect($url);
    }

    $params = [
        'errors' => $errors,
        '$schoolData' => $schoolData
    ];
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, "schools/new.phtml", $params);
});

$app->run();


