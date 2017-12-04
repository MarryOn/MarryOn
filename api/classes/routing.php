<?php

namespace MarryOn;

use Klein\Klein;
use Klein\Request;
use Klein\ServiceProvider;
use MarryOn\Api\Service\RequestService;

$klein = new Klein();

/* GENERAL API REQUESTS */
$requestService = new RequestService();
$generalApiRequestHandler = function (Request $request, $response, $service, $app, $klein, $matched) use ($requestService) {
	if($request->method('options')) {
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		return NULL;
	}
	return count($matched) === 0 ? $requestService->callControllerAction(
		$request, $request->controller, isset($request->action) ? $request->action : NULL
	) : NULL;
};
$klein->respond('GET', '/api/[:controller]s', $generalApiRequestHandler);
$klein->respond('GET', '/api/[:controller]/[:action]', $generalApiRequestHandler);
$klein->respond('GET', '/api/[:controller]/[:action]/', $generalApiRequestHandler);
$klein->respond('POST', '/api/[:controller]/[:action]', $generalApiRequestHandler);
$klein->respond('POST', '/api/[:controller]/[:action]/', $generalApiRequestHandler);
$klein->respond('OPTIONS', '/api/[:controller]/[:action]', $generalApiRequestHandler);
$klein->respond('OPTIONS', '/api/[:controller]/[:action]/', $generalApiRequestHandler);

/* BACKEND */
$klein->respond('GET', '/backend', function ($request, $response, ServiceProvider $service) {
	$service->render('backend/index.php');
});

/* DEFAULT */
$generalRequestHandler = function (Request $request, $response, ServiceProvider $service, $app, $klein, $matched) {
	$service->render('main.html');
};
$klein->respond('GET', '/[:any]', $generalRequestHandler);
$klein->respond('GET', '/', $generalRequestHandler);

//$klein->respond(function ($request, $response, ServiceProvider $service, $app, $klein, $matched) {
//	if (count($matched) === 0) {
//		$service->render('main.html');
//	}
//});

/* ERROR */
//$klein->onHttpError(function ($code, $router) {
//	var_dump(['ERROR', $code]);
//});

$klein->dispatch();

