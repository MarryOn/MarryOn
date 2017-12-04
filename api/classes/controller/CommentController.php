<?php
namespace MarryOn\Api\Controller;

use MarryOn\Api\Database\DatabaseManager;

class CommentController {

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = NULL;

	/**
	 * CommentsController constructor.
	 */
	public function __construct() {
		$this->dbManager = DatabaseManager::Instance();
	}


	/**
	 * @param string $target
	 * @return array
	 */
	public function listAction($target = ''): array {
		$mysqli = $this->dbManager->connect();

		if (is_string($target) && strlen($target) > 0) {
			$sql = "SELECT * FROM marry_on_comment WHERE target =  ?";
			$statement = $mysqli->prepare($sql);
			$statement->bind_param('s', $target);
		} else {
			$sql = "SELECT * FROM marry_on_comment";
			$statement = $mysqli->prepare($sql);
		}

		$statement->execute();

		$result = $statement->get_result();

		$resultArray = [];
		while ($row = $result->fetch_object()) {
			$createdAt = new \DateTime($row->createdAt);
			$row->createdAt = $createdAt->format(DATE_ISO8601);
			$resultArray[] = $row;
		}

		return $resultArray;
	}

	/**
	 * @param $comment
	 * @return array
	 */
	public function addAction($comment): array {
		$result = [
			'success' => false
		];

		if (
			is_string($comment->author) && strlen($comment->author) > 0 &&
			is_string($comment->content) && strlen($comment->content) > 0 &&
			is_string($comment->target) && strlen($comment->target) > 0
		) {
			$dbManager = DatabaseManager::Instance();
			$mysqli = $dbManager->connect();

			$sql = "INSERT INTO marry_on_comment (author, content, target) VALUE (?, ?, ?)";
			$statement = $mysqli->prepare($sql);
			$statement->bind_param('sss', $comment->author, $comment->content, $comment->target);

			$success = $statement->execute();

			if ($success) {
				$now = new \DateTime();
				$comment->createdAt = $now->format(DATE_ISO8601);

				$result['success'] = $success;
				$result['comment'] = $comment;
			}
		}

		return $result;
	}
}