<?php
namespace MarryOn\Api\Service;

use Klein\Request;

class RequestService {

	/** @var string */
	private $defaultActionName = 'list';

	/**
	 * @param Request $request
	 * @param string  $controllerName
	 * @param string  $actionName
	 * @return string
	 */
	public function callControllerAction(Request $request, string $controllerName, ?string $actionName) {
		$controllerName = 'MarryOn\\Api\\Controller\\' . ucfirst($controllerName) . 'Controller';
		$actionName = ($actionName ? $actionName : $this->defaultActionName) . 'Action';

		$controller = new $controllerName; // TODO use singleton and/or object manager

		$paramNames = $this->getMethodArgumentNames($controllerName, $actionName);
		$params = $this->getRequestArguments($request, $paramNames);

		// set access controller headers
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

		// call action in controller with the given parameters
		return json_encode(call_user_func_array(array($controller, $actionName), $params));

	}

	/**
	 * @param Request $request
	 * @param array   $paramNames
	 * @return array
	 */
	private function getRequestArguments(Request $request, array $paramNames): array {
		$params = [];

		$body = json_decode($request->body());
		foreach ($paramNames as $paramName) {
			$params[$paramName] = $request->param($paramName) ? $request->param($paramName) : NULL;
			if(isset($body->{$paramName})) {
				$params[$paramName] = $body->{$paramName};
			}
		}

		return $params;
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @return array
	 */
	private function getMethodArgumentNames(string $class, string $method): array {
		$ReflectionMethod = new \ReflectionMethod($class, $method);
		$params = $ReflectionMethod->getParameters();

		return array_map(function (\ReflectionParameter $item) {
			return $item->getName();
		}, $params);
	}
}