<?php

class Query extends Base {

	protected $connection;
	protected $statement;
	protected $table;
	protected $columns;
	protected $where = array();
	protected $whereCalled = false;
	protected $limit;
	protected $offset;
	protected $order;
	protected $orderDirection;
	protected $group;
	protected $groupDirection;
	protected $values = array();

	public function __construct(PDO &$connection, $table, array $columns=array('*')) {
		if (empty($table)) {
			throw new InvalidArgumentException('Database table name must be set.');
		} else if (!is_string($table)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $table, 1, 'string'));
		}

		$this->connection = $connection;
		$this->table = $table;
		$this->columns = $columns;
	}

	public function __destruct() {
		$this->connection = NULL;
		$this->statement = NULL;
	}

	public function where($column, $value, $operator='=') {
		if (empty($column)) {
			throw new InvalidArgumentException('WHERE column must be set.');
		} else if (!is_string($column)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $column, 1, 'string'));
		} else if (!is_string($operator)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $operator, 3, 'string'));
		} else if (!preg_match("/!?=(>|<)?|>|</", $operator)) {
			throw new InvalidArgumentException('Invalid WHERE comparison operator.');
		}

		$this->whereCalled = true;
		$this->where[] = "{$column} {$operator} :where_{$column}";

		try {
			$this->addValue("where_{$column}", $value);
		} catch (InvalidArgumentException $e) {
			throw $e;
		}

		return $this;
	}

	public function order($column, $direction='ASC') {
		if (empty($column)) {
			throw new InvalidArgumentException('ORDER BY column must be set.');
		} else if (!is_string($column)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $column, 1, 'string'));
		} else if (!preg_match("/ASC|DESC/", $direction)) {
			throw new InvalidArgumentException('Invalid ORDER BY direction.');
		}

		$this->order = $column;
		$this->orderDirection = $direction;

		return $this;
	}

	public function group($column, $direction='ASC') {
		if (empty($column)) {
			throw new InvalidArgumentException('GROUP BY column must be set.');
		} else if (!is_string($column)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $column, 1, 'string'));
		} else if (!preg_match("/ASC|DESC/", $direction)) {
			throw new InvalidArgumentException('Invalid GROUP BY direction.');
		}

		$this->group = $column;
		$this->groupDirection = $direciton;

		return $this;
	}

	public function limit($limit, $offset=null) {
		if (empty($limit)) {
			throw new InvalidArgumentException('LIMIT value must be set.');
		} else if (!is_int($limit)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $limit, 1, 'integer'));
		} else if (isset($offset) AND !is_int($offset)) {
			throw new InvalidArgumentException($this->invalidArgumentExceptionMessage(__METHOD__, $offset, 2, 'integer'));
		}

		$this->limit = $limit;

		if (isset($offset)) $this->offset = $offset;

		return $this;
	}

	protected function whereClause() {
		$where = '';

		if (!empty($this->where)) $where = "WHERE " . implode(' AND ', $this->where);

		return $where;
	}

	protected function groupClause() {
		$group = '';

		if (isset($this->group)) $group = "GROUP BY {$this->group} {$this->orderDirection}";

		return $group;
	}

	protected function orderClause() {
		$order = '';

		if (isset($this->order)) $order = "ORDER BY {$this->order} {$this->groupDirection}";

		return $order;
	}

	protected function limitClause() {
		$limit = '';

		if (isset($this->limit)) $limit = "LIMIT {$this->limit}";
		if (isset($this->offset)) $limit .= ",{$this->offset}";

		return $limit;
	}

	protected function buildSelect() {
		$template = "SELECT %s FROM %s %s %s %s %s";
		$columns = implode(', ', $this->columns);
		$table = $this->table;
		$where = $this->whereClause();
		$group = $this->groupClause();
		$order = $this->orderClause();
		$limit = $this->limitClause();
		$query = trim(sprintf($template, $columns, $table, $where, $group, $order, $limit));

		return $query;
	}

	protected function buildInsert(array $data) {
		$template = "INSERT INTO %s (%s) VALUES (%s)";
		$columns = array();
		$values = array();

		foreach ($data as $column => $value) {
			try {
				$this->addValue($column, $value);
			} catch (InvalidArgumentException $e) {
				throw $e;
			}

			$columns[] = (string) $column;
			$values[] = ":{$column}";
		}

		$table = $this->table;
		$columns = implode(', ', $columns);
		$values = implode(', ', $values);
		$query = trim(sprintf($template, $table, $columns, $values));

		return $query;
	}

	protected function buildUpdate(array $data) {
		$template = "UPDATE %s SET %s %s %s";
		$columns = array();

		foreach ($data as $column => $value) {
			try {
				$this->addValue($column, $value);
			} catch (InvalidArgumentException $e) {
				throw $e;
			}

			$columns[] = "{$column} = :{$column}";
		}

		$table = $this->table;
		$columns = implode(', ', $columns);
		$where = $this->whereClause();
		$limit = $this->limitClause();
		$query = trim(sprintf($template, $table, $columns, $where, $limit));

		return $query;
	}

	protected function buildDelete() {
		$template = "DELETE FROM %s %s %s";
		$table = $this->table;
		$where = $this->whereClause();
		$limit = $this->limitClause();
		$query = trim(sprintf($template, $table, $where, $limit));

		return $query;
	}

	protected function addValue($column, $value) {
		if (empty($column)) throw new InvalidArgumentException('Invalid argument: Expected column name.');

		$column = ":{$column}";
		$this->values[$column] = $value;
	}

	protected function setFetchMode($fetchMode, $options=null) {
		try {
			($options) ? $this->statement->setFetchMode($fetchMode, $options) : $this->statement->setFetchMode($fetchMode);
		} catch (PDOException $e) {
			throw $e;
		}
	}

	protected function prepare($query) {
		try {
			$this->statement =& $this->connection->prepare($query);
		} catch (PDOException $e) {
			throw $e;
		}
	}

	protected function execute() {
		try {
			$this->statement->execute($this->values);
		} catch (PDOException $e) {
			throw $e;
		}
	}

	public function resultClass($class) {
		try {
			$query = $this->buildSelect();
			$this->prepare($query);
			$this->setFetchMode(PDO::FETCH_CLASS, $class);
			$this->execute();
		} catch (Exception $e) {
			throw $e;
		}

		return $this->statement;
	}

	public function resultBoth() {
		try {
			$query = $this->buildSelect();
			$this->prepare($query);
			$this->setFetchMode(PDO::FETCH_BOTH);
			$this->execute();
		} catch (Exception $e) {
			throw $e;
		}

		return $this->statement;
	}

	public function save(array $data) {
		if (empty($data)) throw new InvalidArgumentException('Query execution halted: no data given.');

		try {
			$query = ($this->whereCalled) ? $this->buildUpdate($data) : $this->buildInsert($data);
			$this->prepare($query);
			$this->execute();
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function delete() {
		try {
			$query = $this->buildDelete();
			$this->prepare($query);
			$this->execute();
		} catch (Exception $e) {
			throw $e;
		}
	}

}