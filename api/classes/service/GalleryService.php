<?php
namespace MarryOn\Api\Service;

use MarryOn\Api\Database\DatabaseManager;

class GalleryService {
	const SRC_DIR_TYPE_RELATIVE = 0;
	const SRC_DIR_TYPE_ABSOLUTE = 1;
	const SRC_DIR_TYPE_BASEDIR  = 2;

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = NULL;

	protected $src_dir = NULL;
	protected $dst_dir = NULL;

	protected $blackList = ['right', 'large', 'thumbs'];
	protected $baseDir = NULL;

	/**
	 * Gallery constructor.
	 */
	public function __construct() {
		$config = include($_SERVER['DOCUMENT_ROOT'] . '/api/config/config.php');

		$this->baseDir = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === 'localhost') ?
			$this->dbConfig = $config['local']['baseDir'] :
			$this->dbConfig = $config['live']['baseDir'];

		$this->dbManager = DatabaseManager::Instance();
	}

	/**
	 * @param int $type
	 * @return string
	 */
	protected function getSrcDir($type = GalleryService::SRC_DIR_TYPE_RELATIVE): string {
		if($type === GalleryService::SRC_DIR_TYPE_ABSOLUTE) {
			return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->src_dir;
		}
		if($type === GalleryService::SRC_DIR_TYPE_BASEDIR) {
			return $this->baseDir . DIRECTORY_SEPARATOR . $this->src_dir;
		}
		return $this->src_dir;
	}

	/**
	 * @param string $src_dir
	 */
	protected function setSrcDir(string $src_dir) {
		$this->src_dir = $src_dir;
	}

	/**
	 * @return array
	 */
	protected function getSrcImages() {
		return array_diff(scandir($this->getSrcDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE)), array('.', '..'));
	}

	/**
	 * @return array
	 * @internal param int $type
	 */
	protected function getDstImages() {
		$path = $this->getDstDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE) . DIRECTORY_SEPARATOR . 'large';
		return is_dir($path) ? array_diff(scandir($path), array('.', '..')) : [];
	}

	/**
	 * @param int $type
	 * @return string
	 */
	protected function getDstDir($type = GalleryService::SRC_DIR_TYPE_RELATIVE): string {
		if($type === GalleryService::SRC_DIR_TYPE_ABSOLUTE) {
			return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->dst_dir;
		}
		if($type === GalleryService::SRC_DIR_TYPE_BASEDIR) {
			return $this->baseDir . DIRECTORY_SEPARATOR . $this->dst_dir;
		}
		return $this->dst_dir;
	}

	/**
	 * @param string $dst_dir
	 */
	protected function setDstDir(string $dst_dir) {
		$this->dst_dir = $dst_dir;
	}

	/**
	 *
	 */
	protected function prepareDistribution() {
		$baseSrcDir = $this->getSrcDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE);
		$baseDstDir = $this->getDstDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE);

		if (file_exists($baseDstDir) === FALSE) {
			mkdir($baseDstDir);
		}

		if (is_dir($baseSrcDir)) {
			foreach ($this->getSrcImages() as $image) {
				$thumbs_dir = $baseDstDir . DIRECTORY_SEPARATOR . 'thumbs';
				$large_dir = $baseDstDir . DIRECTORY_SEPARATOR . 'large';

				if (file_exists($thumbs_dir) === FALSE) {
					mkdir($thumbs_dir);
				}

				if (file_exists($large_dir) === FALSE) {
					mkdir($large_dir);
				}

				$thumbImagePath = $thumbs_dir . DIRECTORY_SEPARATOR . $this->sanitizeImagePath($image);
				$largeImagePath = $large_dir . DIRECTORY_SEPARATOR . $this->sanitizeImagePath($image);
				$srcImagePath   = $this->getSrcDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE) . DIRECTORY_SEPARATOR . $image;

				if (
					is_dir($srcImagePath) === FALSE &&
					file_exists($srcImagePath) &&
					exif_imagetype($srcImagePath)
				) {
					// create thumbnail version
					$this->resizeImage($srcImagePath, 320, 320, $thumbImagePath, TRUE);
					// create large image version
					$this->resizeImage($srcImagePath, 1920, 1080, $largeImagePath);
				}
			}
		}
	}

	/**
	 * @param string      $galleryName
	 * @param string|null $sub
	 * @return array
	 */
	public function getData(string $galleryName, string $sub = NULL) {
		$data = [];

		$gallery_dir = 'images' . DIRECTORY_SEPARATOR . $galleryName;

		if($sub !== NULL) {
			$gallery_dir .= DIRECTORY_SEPARATOR . $sub;
		}

		$this->src_dir = $gallery_dir . DIRECTORY_SEPARATOR . 'src';
		$this->dst_dir = $gallery_dir . DIRECTORY_SEPARATOR . 'dst';

		$this->prepareDistribution();

		$baseDstDir = $this->getDstDir(GalleryService::SRC_DIR_TYPE_ABSOLUTE);
		$baseUrlDstDir = $this->getDstDir(GalleryService::SRC_DIR_TYPE_BASEDIR);

		if (is_dir($baseDstDir)) {

			foreach ($this->getDstImages() as $imageFileName) {
				$imageData = [];

				if(in_array($imageFileName, $this->blackList)) {
					break;
				}

				$thumbImagePath = 'thumbs' . DIRECTORY_SEPARATOR . $this->sanitizeImagePath($imageFileName);
				$largeImagePath = 'large' . DIRECTORY_SEPARATOR . $this->sanitizeImagePath($imageFileName);

				$imageData['url_thumbnail'] = $baseUrlDstDir . DIRECTORY_SEPARATOR . $thumbImagePath;
				$imageData['url_large']     = $baseUrlDstDir . DIRECTORY_SEPARATOR . $largeImagePath;

				$imageData['filename']      = $imageFileName;

				list($newWidth, $newHeight) = getimagesize($baseDstDir . DIRECTORY_SEPARATOR . $largeImagePath);
				$imageData['size'] = ['width' => $newWidth, 'height' => $newHeight];

				$imageData['commentCount'] = 0;
				$mysqli = $this->dbManager->connect();

				// comment count
				$target = $imageData['url_large'];
				$sql = "SELECT COUNT(*) FROM marry_on_comment WHERE target =  ?";
				$statement = $mysqli->prepare($sql);
				$statement->bind_param('s', $target);
				$statement->execute();
				$statement->bind_result($count);
				if($statement->fetch()) {
					$imageData['commentCount'] = $count;
				}

				$data[$imageFileName] = $imageData;
			}
		}

		return $data;
	}

	/**
	 * @param string  $srcPath
	 * @param integer $targetWidth
	 * @param integer $targetHeight
	 * @param string  $dstPath
	 * @param bool    $crop
	 * @return array
	 */
	protected function resizeImage($srcPath, $targetWidth, $targetHeight, $dstPath, $crop = FALSE) {
		if (file_exists($dstPath) === FALSE) {
			try {
				$image = new \claviska\SimpleImage();
				$image->fromFile($srcPath)->autoOrient();

				if($image->getWidth() > $image->getHeight()) {
					$image->resize(NULL, $targetHeight);
				} else {
					$image->resize($targetHeight, NULL);
				}

				if($crop) {
					$startX = ($image->getWidth() - $targetWidth) / 2;
					$startY = ($image->getHeight() - $targetHeight) / 2;
					$image = $image->crop($startX, $startY, $startX + $targetWidth, $startY + $targetHeight);
				}

				$image->toFile($dstPath);
			} catch(Exception $err) {
				echo $err->getMessage();
			}
		}

		list($newWidth, $newHeight) = getimagesize($dstPath);
		return ['width' => $newWidth, 'height' => $newHeight];
	}

	/**
	 * @param string $file
	 * @return string
	 */
	protected function sanitizeImagePath($file) {
		// Remove anything which isn't a word, whitespace, number
		// or any of the following caracters -_~,;[]().
		// If you don't need to handle multi-byte characters
		// you can use preg_replace rather than mb_ereg_replace
		// Thanks @Łukasz Rysiak!
		$file = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $file);
		// Remove any runs of periods (thanks falstro!)
		$file = mb_ereg_replace('([\.]{2,})', '', $file);

		$file = str_replace([').', 'ä', 'ö', 'ü'], ['.', 'ae', 'oe', 'ue'], $file);

		// Replace all weird characters with dashes
		$is_filename = TRUE;
		$file = preg_replace('/[^\w\-'. ($is_filename ? '~_\.' : ''). ']+/u', '-', $file);

		// Only allow one dash separator at a time (and make string lowercase)
		$file = mb_strtolower(preg_replace('/--+/u', '-', $file), 'UTF-8');

		return $file;
	}

	/**
	 * @param $value
	 */
	protected function debug($value) {
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
	}
}