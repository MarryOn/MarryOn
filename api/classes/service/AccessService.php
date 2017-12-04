<?php
namespace MarryOn\Api\Service;

use Firebase\JWT\JWT;

class AccessService {

	protected $privateKey = "example_key"; // TODO get key from configuration
	protected $accessPeriod = 3600;

	/**
	 * @param string $username
	 * @param string $password
	 * @return string
	 */
	public function getAccessToken(string $username, string $password) {
		// TODO check user credentials with database

		$now = new \DateTime();

		$token = array(
			"domain"    => "marry-on.de",
			"user"      => $username,
			"createdAt" => $now->format(DATE_ISO8601)
		);

		/**
		 * IMPORTANT:
		 * You must specify supported algorithms for your application. See
		 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
		 * for a list of spec-compliant algorithms.
		 */
		return JWT::encode($token, $this->privateKey);
	}

	/**
	 * @param string $token
	 * @return bool
	 */
	public function hasAccess(string $token) {
		try {
			$decoded = JWT::decode($token, $this->privateKey, array('HS256'));
		} catch (\Exception $e) {
			return FALSE;
		}

		if(isset($decoded->createdAt)) {
			$createdAt = new \DateTime($decoded->createdAt);
			$now = new \DateTime();
			$diff = $now->getTimestamp() - $createdAt->getTimestamp();

			return $diff < $this->accessPeriod;
		}

		return FALSE;
	}
}