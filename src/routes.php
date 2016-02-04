<?php
// Routes



$app->map(['GET', 'POST'],'/login', function ($request, $response, $args) {
    if ( $request->isPost() ) {
        echo "<pre>";
        print_r($request->getParams());exit;
        //If valid login, set auth cookie and redirect
    }
    $this->renderer->render($response, 'login.phtml');
});

$app->get('/logout', function () {
    //Remove auth cookie and redirect to login page
});



//$app->get('/protected-page', $authenticateForRole('admin'), function () {
//    //Show protected information
//});



$app->get('/', function ($request, $response) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml');
});
