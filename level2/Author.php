<?php

/**
 * ActiveRecord class for the author table
 */
class Author 
{
	public $id, $first_name, $last_name;
	
	/**
	 * Get the Author's name
	 *
	 * @return string
	 */
	public function getName()
	{
		if ($this->first_name && $this->last_name) {
			return $this->first_name . ' ' . $this->last_name;
		} elseif ($this->last_name) {
			return $this->last_name;
		} elseif ($this->first_name) {
			return $this->first_name;
		} else {
			return 'Mr. Nobody';
		}
	}
	
	/**
	 * Load data from a resultset into the current object
	 * The resultset should be fetched using PDO::FETCH_NUM
	 *
	 * @param array $row numerically indexed array of values
	 */
	public function load($row)
	{
		$this->id         = $row[0];
		$this->first_name = $row[1];
		$this->last_name  = $row[2];
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
		$sql = 'INSERT INTO `author` (`first_name`, `last_name`)
			VALUES (:first_name, :last_name)';
		$stmt = $con->prepare($sql);
		$this->bindValue($stmt, ':first_name', $this->first_name, PDO::PARAM_STR);
		$this->bindValue($stmt, ':last_name', $this->last_name, PDO::PARAM_STR);
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
		$sql = 'UPDATE `author` 
			SET `first_name` = :first_name, `last_name` = :last_name 
			WHERE `id` = :id';
		$stmt = $con->prepare($sql);
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		$this->bindValue($stmt, ':first_name', $this->first_name, PDO::PARAM_STR);
		$this->bindValue($stmt, ':last_name', $this->last_name, PDO::PARAM_);
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
}
