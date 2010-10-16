<?php

/**
 * ActiveRecord class for the book table
 */
class Book 
{
	public $id, $title, $author_id;

	/**
	 * Load data from a resultset into the current object
	 * The resultset should be fetched using PDO::FETCH_NUM
	 *
	 * @param array $row numerically indexed array of values
	 */
	public function load($row)
	{
		$this->id = $row[0];
		$this->title = $row[1];
		$this->author_id = $row[2];
	}
	
	/**
	 * Persist the data of the current object to a database
	 *
	 * @param  PDO $con Connection to use
	 */
	public function save(PDO $con)
	{
		if (null === $this->id) {
			return $this->insert($con);
		} else {
			return $this->update($con);
		}
	}
	
	/**
	 * Insert the data of the current object as a new record
	 *
	 * @param  PDO $con Connection to use
	 */
	public function insert(PDO $con)
	{
		$sql = 'INSERT INTO `book` (`title`, `author_id`)
			VALUES (:title, :author_id)';
		$stmt = $con->prepare($sql);
		$this->bindValue($stmt, ':title', $this->title, PDO::PARAM_STR);
		$this->bindValue($stmt, ':author_id', $this->author_id, PDO::PARAM_INT);
		$stmt->execute();
		$this->id = $con->lastInsertId();
	}
	
	/**
	 * Update an existing record with the data from this object
	 *
	 * @param  PDO $con Connection to use
	 */
	public function update(PDO $con)
	{
		if (null === $this->id) {
			throw new Exception('Cannot update an object without a Pk');
		}
		$sql = 'UPDATE `book` 
			SET `title` = :title, `author_id` = :author_id 
			WHERE `id` = :id';
		$stmt = $con->prepare($sql);
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		$this->bindValue($stmt, ':title', $this->title, PDO::PARAM_STR);
		$this->bindValue($stmt, ':author_id', $this->author_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/**
	 * Binds a value to a parameter, handling NULL values the right way
	 *
	 * @param PDOStatement $stmt The statement to bind on
	 * @param integer|string $parameter Parameter indentifier 
	 * @param mixed $value The value to bind to the parameter
	 * @param integer $dataType Explicit data type for the parameter using the PDO::PARAM_* constants.
	 */
	protected function bindValue(PDOStatement $stmt, $parameter, $value = null, $dataType = PDO::PARAM_STR)
	{
		$dataType = (null === $value) ? PDO::PARAM_NULL : $dataType;
		$stmt->bindValue($parameter, $value, $dataType);
	}
	
	/**
	 * Get the related Author object if exists. Uses Lazy-loading.
	 * 
	 * @param  PDO $con Connection to use
	 * @return null|Author
	 * @throws Exception If the author id doesn't exist in the database 
	 */
	public function getAuthor(PDO $con)
	{
		if (null === $this->author_id) {
			return null;
		}
		$sql = 'SELECT author.id, author.first_name, author.last_name
    	FROM author 
    	WHERE author.id = :id
    	LIMIT 1';
    $stmt = $con->prepare($sql);
		$stmt->bindValue(':id', $this->author_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			require_once 'Author.php';
			$author = new Author();
			$author->load($row);
			return $author;
		} else {
			throw new Exception(sprintf(
				'No author for this book\'s author_id, %d',
				$this->author_id
			));
		}
	}
}
