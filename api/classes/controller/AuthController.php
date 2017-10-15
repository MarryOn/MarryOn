<?php

use Firebase\JWT\JWT;

class AuthController {

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = NULL;

	/**
	 * CommentsController constructor.
	 * @param DatabaseManager $dbManager
	 */
	public function __construct(DatabaseManager $dbManager) {
		$this->dbManager = $dbManager;
	}

	/**
	 * @param $username
	 * @param $password
	 * @return array
	 */
	public function getTokenAction($username, $password): array {
		$privateKey = "example_key"; // TODO get key from configuration

		// TODO check user credentials with database

		$now = new DateTime();

		$token = array(
			"domain"    => "marry-on.de",
			"user"      => "iice", // TODO use username from request
			"timestamp" => $now->format(DATE_ISO8601)
		);

		/**
		 * IMPORTANT:
		 * You must specify supported algorithms for your application. See
		 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
		 * for a list of spec-compliant algorithms.
		 */
		$jwt = JWT::encode($token, $privateKey);

		echo $jwt;

//		$decoded = JWT::decode($jwt, $privateKey, array('HS256'));
//
//		print_r($decoded);
//
//		/*
//		 NOTE: This will now be an object instead of an associative array. To get
//		 an associative array, you will need to cast it as such:
//		*/
//
//		$decoded_array = (array)$decoded;
//
//		/**
//		 * You can add a leeway to account for when there is a clock skew times between
//		 * the signing and verifying servers. It is recommended that this leeway should
//		 * not be bigger than a few minutes.
//		 *
//		 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
//		 */
//		JWT::$leeway = 60; // $leeway in seconds
//		$decoded = JWT::decode($jwt, $privateKey, array('HS256'));
	}

}