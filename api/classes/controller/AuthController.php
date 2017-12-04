<?php
namespace MarryOn\Api\Controller;

use MarryOn\Api\Database\DatabaseManager;
use MarryOn\Api\Service\AccessService;

class AuthController {

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = NULL;

	/**
	 * @var AccessService
	 */
	protected $accessService = NULL;

	/**
	 * AuthController constructor.
	 */
	public function __construct() {
		$this->dbManager = DatabaseManager::Instance();
		$this->accessService = new AccessService();
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	public function getTokenAction(string $username, string $password): array {
		$accessService = new AccessService();
		return ['token' => $accessService->getAccessToken($username, $password)];
	}

	/**
	 * @param string $token
	 * @return array
	 */
	public function hasAccessAction(string $token): array {
		return ['hasAccess' => $this->accessService->hasAccess($token)];
	}
}