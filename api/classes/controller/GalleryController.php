<?php

namespace MarryOn\Api\Controller;

use MarryOn\Api\Database\DatabaseManager;
use MarryOn\Api\Service\GalleryService;

class GalleryController {

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = NULL;

	/**
	 * @var GalleryService
	 */
	protected $galleryService = NULL;

	/**
	 * CommentsController constructor.
	 */
	public function __construct() {
		$this->dbManager = DatabaseManager::Instance();
		$this->galleryService = new GalleryService();
	}

	public function showAction(string $galleryId, string $subGalleryId = NULL) {
		return $this->galleryService->getData($galleryId, $subGalleryId);
	}
}