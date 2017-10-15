<?php

use Klein\Klein;
use Klein\Request;

require_once 'api/classes/vendor/autoload.php';

include_once 'api/classes/database/DatabaseManager.php';

include_once 'api/classes/controller/CommentsController.php';
include_once 'api/classes/controller/AuthController.php';

include_once 'api/classes/service/GalleryService.php';

$dbManager = DatabaseManager::Instance();
$commentsController = new CommentsController($dbManager);
$authController = new AuthController($dbManager);

$klein = new Klein();

$nullCallback = function () {
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	return NULL;
};

/* GALLERIES */
$galleryCallback = function (Request $request) use ($dbManager) {
	$gallery = new GalleryService($request->gallery, $request->sub, $dbManager);
	return json_encode($gallery->getData());
};
$klein->respond('GET', '/api/galleries/[:gallery]/[:sub]', $galleryCallback);
$klein->respond('GET', '/api/galleries/[:gallery]/', $galleryCallback);
$klein->respond('GET', '/api/galleries/[:gallery]', $galleryCallback);

/* COMMENTS - LIST */
$commentsCallback = function (Request $request) use ($commentsController) {
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	return json_encode($commentsController->listAction($request->target));
};
$klein->respond('GET', '/api/comments', $commentsCallback);
$klein->respond('GET', '/api/comments/', $commentsCallback);
$klein->respond('GET', '/api/comments/[:target]', $commentsCallback);

/* COMMENTS - ADD */
$commentsAddCallback = function (Request $request) use ($commentsController) {
	$body = json_decode($request->body());
	$comment = $body->comment;
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	return json_encode($commentsController->addAction($comment));
};
$klein->respond('POST', '/api/comments/add', $commentsAddCallback);
$klein->respond('POST', '/api/comments/add/', $commentsAddCallback);
$klein->respond('OPTIONS', '/api/comments/add/', $nullCallback);
$klein->respond('OPTIONS', '/api/comments/add', $nullCallback);

/* AUTHENTICATION */
$commentsAddCallback = function (Request $request) use ($authController) {
	$body = json_decode($request->body());
	$username = $body->username;
	$password = $body->password;
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	return json_encode($authController->getTokenAction($username, $password));
};
$klein->respond('GET', '/api/comments', $commentsCallback);

/* BACKEND */
$klein->respond('GET', '/backend', function($request, $response, $service) {
	$service->render('backend/index.php');
});

/* DEFAULT */
$klein->respond('GET', '/', function($request, $response, $service) {
	$service->render('main.html');
});

$klein->respond(function($request, $response, $service, $app, $klein, $matched) {
	if (count($matched) === 0) {
		$service->render('main.html');
	}
});

/* ERROR */
//$klein->onHttpError(function ($code, $router) {
//	var_dump(['ERROR', $code]);
//});

$klein->dispatch();

