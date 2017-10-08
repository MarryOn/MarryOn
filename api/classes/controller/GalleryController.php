<?php

class GalleryController {

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
	 * @param string $target
	 * @return array
	 */
	public function listAction($target = ''): array {
	}

	/**
	 * @param $comment
	 * @return array
	 */
	public function addAction($comment): array {
	}
}