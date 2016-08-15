<?php

abstract class Adamr_Contact_Model_AbstractModel
{
	protected $_pdo;
	protected $_table 	= '';
	protected $_pk 		= '';
	protected $_data 	= [];
	protected $_id;
	protected $_columns;
	protected $_collection;

	public function __construct()
	{
		$this->init();
	}

	public function init()
	{
		$config = Mage::getConfig()->getResourceConnectionConfig('default_setup');

		$dsn 		= $this->getDsn($config);
		$username 	= $config->username;
		$password	= $config->password;

		$opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];

		$this->setPdo($dsn, $username, $password, $opt);
	}

	public function getPdo()
	{
		return $this->_pdo;
	}

	public function setPdo($dsn, $username, $password)
	{
		return $this->_pdo = new PDO($dsn, $username, $password);	
	}

	public function save()
	{	
		$data 	 = $this->getData();
		$columns = $this->getColumns();

		/* Remove keys that do not have matching columns in table */

		foreach ($data as $column => $value)
		{
			if (!in_array($column, $columns))
			{
				unset($data[$column]);
			}
		}
		
		if (!$this->getId())
		{
			$this->insert($data);
			return $this;
		}

		unset($data[$this->_pk]);

		$this->update($data);
		return $this;
	}

	private function update($data)
	{
		/* Create a string 'key = ?, key = ?' ... n for every key in $data */

		$colValPairs = array_reduce(array_keys($data), function($carry, $key)
		{
			return $carry .= $key . ' = ' . '?, ';
		});

		$sql = 'UPDATE ' . $this->_table . ' SET ' . trim($colValPairs,', ') . ' WHERE ' . $this->_pk . ' = ' . $this->_id;

		$stmt = $this->getPdo()
					 ->prepare($sql)
					 ->execute(array_values($data));
	}

	private function insert($data)
	{
		$columns = implode(array_keys($data), ',');
		$values  = implode(array_fill(0, count($data), '?'),',');

		$sql = 'INSERT INTO ' . $this->_table . ' (' . $columns . ') VALUES (' . $values . ')';

		$stmt = $this->getPdo()
					 ->prepare($sql)
					 ->execute(array_values($data));

		$id = $this->getPdo()->lastInsertId();
		$this->_data = [];

		$this->load($id);
	}

	public function load($id)
	{
		$sql = 'SELECT * FROM ' . $this->_table . ' WHERE ' . $this->_pk . ' = :pk';

		$stmt = $this->getPdo()->prepare($sql);

		$stmt->execute(['pk' => $id]);

		if ($stmt->rowCount() === 0)
		{
			return null;
		}
		
		$this->setId($id);
		$this->setData($stmt->fetch(PDO::FETCH_ASSOC));

		return $this;
	}

	public function delete($id=false)
	{
		if (!$id)
		{
			$id = $this->getId();
		}

		if (!is_int($id))
		{
			return $this;
		}

		$sql = 'DELETE FROM ' . $this->_table . ' WHERE ' . $this->_pk . ' = :pk';

		$stmt = $this->getPdo()->prepare($sql);

		$stmt->execute(['pk' => $id]);

		$this->setId(null)
			 ->setData([]);

		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

	public function getData($key=false)
	{
		if ($key && is_string($key))
		{
			return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
		}

		return $this->_data;
	}

	public function setData($arr, $value=false)
	{
		if (is_string($arr))
		{
			$this->_data[$arr] = $value;
			return $this;
		}

		/* A better implementation would probaby include array_merge,
			but for the sake of simplicity (no key sorting) I've left it here */
		if (!is_array($arr))
		{
			$arr = [];
		}

		$this->_data = $arr;

		return $this;
	}

	public function getCollection()
	{
		if (null === $this->_collection)
		{
			$sql = 'SELECT * FROM ' . $this->_table;

			$stmt = $this->getPdo()->prepare($sql);

			$stmt->execute();

			$this->_collection = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return $this->_collection;
	}

	public function getColumns()
	{
		if (null === $this->_columns)
		{
			$sql  = 'DESCRIBE ' . $this->_table;
			$stmt = $this->getPdo()->prepare($sql);
			$stmt->execute();

			$this->_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
		}

		return $this->_columns;
	}

	private function getDsn($config)
	{
		$driver 	= 'mysql';
		$host 		= $config->host;
		$port 		= $config->port ? ';port=' . $config->port : '';
		$dbName 	= $config->dbname;
		$charset	= str_replace('SET NAMES ', '', $config->initStatements);

		return $driver . ':host=' . $host . $port . ';dbname=' . $dbName . ';charset=' . $charset;
	}
}