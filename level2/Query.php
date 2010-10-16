<?php

/**
 * QueryObject for ActiveRecord queries
 */
class Query
{
	protected 
		$resultClass,
		$from, 
		$wheres = array(), 
		$joins = array(),
		$limit;
	
	/**
	 * Query Constructor
	 * Requires an ActiveRecord class name. Beware that the class file is not included.
	 *
	 * @param string $resultClass An ActiveRecord class, e.g. 'Book'
	 * @param string $tableName The name of the table to query for.
	 *               Defaults to the lowercased $resultClass
	 */
	public function __construct($resultClass, $tableName = null)
	{
		$this->resultClass = $resultClass;
		if (null === $tableName) {
			$tableName = strtolower($resultClass);
		}
		$this->from = $tableName;
	}
	
	/**
	 * Adds a WHERE clause.
	 * <code>$query->where('book.title LIKE ?', 'foo%', PDO::PARAM_STR)</code>
	 *
	 * @param string $clause The SQL clause, using question mark as placeholder.
	 * @param scalar $value The value to bind. Does not accept arrays
	 * @param integer $type The PDO type used for binding
	 */
	public function where($clause, $value = null, $type = PDO::PARAM_STR)
	{
		$this->wheres[] = array(
			'clause' => $clause,
			'value'  => $value,
			'type'   => $type
		);
	}
	
	/**
	 * Adds a limit to the query
	 *
	 * @param integer $limit The numerical limit
	 */
	public function limit($limit)
	{
		$this->limit = (int) $limit;
	}
	
	public function join($foreignTable, $clause, $type = 'INNER')
	{
		$this->joins[]= array(
			'foreignTable' => $foreignTable,
			'clause'       => $clause,
			'type'         => $type
		);
	}
	
	/**
	 * Execute the query and returns a list of ActiveRecord objects 
	 * based on the result.
	 *
	 * @param PDO $con Connection to use
	 * @return array List of ActiveRecord objects
	 */
	public function find(PDO $con)
	{
		$class = $this->resultClass;
		$stmt = $this->doExecute($con);
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$record  = new $class();
			$record->load($row);
			$result[]= $record;
		}
		return $result;
	}
	
	/**
	 * Execute the query and returns an ActiveRecord object 
	 * based on the result.
	 *
	 * @param PDO $con Connection to use
	 * @return mixed An ActiveRecord object, or null if no result
	 */
	public function findOne(PDO $con)
	{
		$class = $this->resultClass;
		$stmt = $this->doExecute($con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$record  = new $class();
			$record->load($row);
			return $record;
		} 
	}
	
	/**
	 * Prepares, binds and executes the SQL query
	 *
	 * @param PDO $con
	 * @return PDOStatement
	 */
	protected function doExecute(PDO $con)
	{
		$stmt = $con->prepare($this->getSQL());
		foreach ($this->wheres as $key => $where) {
			if (null === $where['value']) {
				$stmt->bindValue($key + 1, null, PDO::PARAM_NULL);
			} else {
				$stmt->bindValue($key + 1, $where['value'], $where['type']);
			}
		}
		$stmt->execute();
		return $stmt;
	}
	
	/**
	 * Creates the SQL string based on the current object's properties
	 *
	 * @return string the SQL string to execute
	 */
	public function getSQL()
	{
		// build the SELECT FROM part
		$sql = sprintf('SELECT * FROM `%s`', $this->from);
		// build the JOIN part
		foreach ($this->joins as $join) {
			$sql .= sprintf(' %s JOIN `%s` ON (%s)',
				$join['type'],
				$join['foreignTable'],
				$join['clause']
			);
		}
		// build the WHERE part
		if ($this->wheres) {
			$whereClauses = array();
			foreach ($this->wheres as $key => $where) {
				$whereClauses []= $where['clause'];
			}
			$sql .= ' WHERE ' . implode(' AND ', $whereClauses);
		}
		// build the LIMIT part
		if ($this->limit) {
			$sql .= ' LIMIT ' . $this->limit;
		}
		return $sql;
	}
}