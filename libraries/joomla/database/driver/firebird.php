<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

	/**
	 * Firebird database driver
	 *
	 * @since  12.1
	 */
class JDatabaseDriverFirebird extends JDatabaseDriver
{
	/**
	 * The database driver name
	 *
	 * @var    string
	 * @since  12.1
	 */
	public $name = 'firebird';

	/**
	 * The type of the database server family supported by this driver.
	 *
	 * @var    string
	 * @since  CMS 3.10
	 */
	 public $serverType = 'firebird';

	/**
	 * Quote for named objects
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $nameQuote = '"';

	/**
	 * The null/zero date string
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $nullDate = '1970-01-01 00:00:00';

	/**
	 * The minimum supported database version.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected static $dbMinimum = '2.5';

	/**
	 * Operator used for concatenation
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $concat_operator = '||';

	/**
	 * JDatabaseDriverFirebirdQuery object returned by getQuery
	 *
	 * @var    JDatabaseQueryFirebird
	 * @since  12.1
	 */
	protected $queryObject = null;

	/**
	 * Database object constructor
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since	12.1
	 */
	public function __construct($options)
	{
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : 'SYSDBA';
		$options['password'] = (isset($options['password'])) ? $options['password'] : 'masterkey';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['port'] = (isset($options['port'])) ? $options['port'] : '';


		// Finalize initialization
		parent::__construct($options);
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
	 return;
		}

		// Make sure the ibase extension for PHP is installed and enabled.
		if (!self::isSupported())
		{
	 throw new JDatabaseExceptionUnsupported('The ibase extension for PHP is not installed or enabled.');
		}
		$port = $this->options['port'] ? '/' . $this->options['port'] : '';

		if (!($this->connection = ibase_connect($this->options['host'] . $port . ':' . $this->options['database'], $this->options['user'], $this->options['password'], 'UTF-8'))
		)
		{
	 throw new JDatabaseExceptionConnecting('Error connecting to Firebird database.');
		}
	}


	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function disconnect()
	{
		// Close the connection.
		if (is_resource($this->connection))
		{
	 foreach ($this->disconnectHandlers as $h)
	 {
		call_user_func_array($h, array( &$this));
	 }

	 ibase_close($this->connection);
		}

		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   12.1
	 */
	public function escape($text, $extra = false)
	{
		return $text;
	}

	/**
	 * Test to see if the Firebird connector is available
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function test()
	{
		return function_exists('ibase_connect');
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return	boolean
	 *
	 * @since	12.1
	 */
	public function connected()
	{
		$this->connect();

		if (is_resource($this->connection))
		{
	 return true;
		}

		return false;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  boolean
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->connect();

		$this->setQuery('DROP TABLE ' . $this->quoteName($tableName));
		$this->execute();

		return true;
	}

	/**
	 * Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows in the previous operation
	 *
	 * @since   12.1
	 */
	public function getAffectedRows()
	{
		$this->connect();

		return ibase_affected_rows($this->cursor);
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getCollation()
	{
		$this->connect();
		$this->setQuery('SHOW LC_COLLATE');
		$array = $this->loadAssocList();

		return $array[0]['lc_collate'];
	}

	/**
	 * Method to get the database connection collation, as reported by the driver. If the connector doesn't support
	 * reporting this value please return an empty string.
	 *
	 * @return  string
	 */
	public function getConnectionCollation()
	{
		return '';
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 * This command is only valid for statements like SELECT or SHOW that return an actual result set.
	 * To retrieve the number of rows affected by an INSERT, UPDATE, REPLACE or DELETE query, use getAffectedRows().
	 *
	 * @param   resource  $cur  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   12.1
	 */
	public function getNumRows($cur = null)
	{
		$this->connect();

		return ibase_num_rows((int) $cur ? $cur : $this->cursor);
	}

	/**
	 * Get the current or query, or new JDatabaseQuery object.
	 *
	 * @param   boolean  $new    False to return the last query set, True to return a new JDatabaseQuery object.
	 * @param   boolean  $asObj  False to return last query as string, true to get JDatabaseQueryPostgresql object.
	 *
	 * @return  JDatabaseQuery  The current query object or a new object extending the JDatabaseQuery class.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getQuery($new = false, $asObj = false)
	{
		if ($new)
		{
			// Make sure we have a query class for this driver.
			if (!class_exists('JDatabaseQueryFirebird'))
			{
				throw new JDatabaseExceptionUnsupported('JDatabaseQueryFirebird Class not found.');
			}
			$this->queryObject = new JDatabaseQueryFirebird($this);

			return $this->queryObject;
		}
		else
		{
			if ($asObj)
			{
				return $this->queryObject;
			}
			else
			{
				return $this->sql;
			}
		}
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * This is unsuported by Firebird.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  string  An empty char because this function is not supported by Firebird.
	 *
	 * @since   12.1
	 */
	public function getTableCreate($tables)
	{
		return '';
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		// https://www.firebirdsql.org/file/documentation/reference_manuals/fblangref25-en/html/fblangref-appx04-fields.html
		$fieldTypes = array (7 => 'SMALLINT', 8 => 'INTEGER', 10 => 'FLOAT', 12 => 'DATE', 13 => 'TIME', 14 => 'CHAR', 16 => 'BIGINT', 27 => 'DOUBLE PRECISION', 35 => 'TIMESTAMP', 37 => 'VARCHAR', 261 => 'BLOB' );

		$this->connect();
		$result = array();
		$tableSub = $this->replacePrefix($table);

		$this->setQuery('select f.rdb$description as "description", f.rdb$null_flag as "null", 
		f.rdb$default_source as "default", f.rdb$field_name as "column_name", field.rdb$field_type "typeindex",
		field.rdb$field_length as "field_lenght",
		field.rdb$field_scale as "field_scale"
		from rdb$relation_fields f
		join rdb$fields field on field.rdb$field_name=f.rdb$field_source
		join rdb$relations r on f.rdb$relation_name = r.rdb$relation_name and 
		r.rdb$view_blr is null and (r.rdb$system_flag is null or r.rdb$system_flag = 0)
		where f.rdb$relation_name="' . $this->quote($tableSub) . '"
		order by f.rdb$field_position;'
		);

		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = preg_replace('/[(0-9)]/', '', $field->type);
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = (object) array(
				'column_name' => $field->column_name,
				'type' => $fieldTypes[$field->typeindex],
				'null' => $field->null,
				'default' => $field->default,
				'comments' => $field->$description
				);
			}
		}

		return $result;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		// To check if table exists and prevent SQL injection
		$tableList = $this->getTableList();

		if (in_array($table, $tableList))
		{
			// Get the details columns information.
			$this->setQuery('
			select
    			sg.rdb$field_name as "field_name"
			from
    			rdb$indices ix
    		left join rdb$index_segments sg on ix.rdb$index_name = sg.rdb$index_name
    		left join rdb$relation_constraints rc on rc.rdb$index_name = ix.rdb$index_name
				where
    		rc.rdb$relation_name =\'' . $this->quote($table) . '\' and
    		rc.rdb$constraint_type = \'PRIMARY KEY\''
			);

			$keys = $this->loadObjectList();

			return $keys;
		}

		return false;
		}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();
		$this->setQuery('select rdb$relation_name as "name"
		from rdb$relations 
		where rdb$view_blr is null 
		and (rdb$system_flag is null or rdb$system_flag = 0)
		order by 1'
		);
		$tables = $this->loadObjectList();

		return $tables;
	}

	/**
	 * Get the details list of sequences for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of sequences specification for the table.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableSequences($table)
	{
		return false;
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   12.1
	 */
	public function getVersion()
	{
		$this->connect();
		$this->setQuery('SELECT rdb$get_context(\'SYSTEM\', \'ENGINE_VERSION\') as "version"
		from rdb$database'
		);
		$info = $this->loadObjectList();

		return $info->version;
	}

	

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by PostgreSQL.
	 * @param   string  $prefix    Not used by PostgreSQL.
	 *
	 * @return  JDatabaseDriverPostgresql  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		throw new JDatabaseExceptionUnsupported('Remaining of table are not supported.');
	}

	/**
	 * Selects the database, but redundant for Firebird
	 *
	 * @param   string  $database  Database name to select.
	 *
	 * @return  boolean  Always true
	 *
	 * @since   12.1
	 */
	public function select($database)
	{
		return true;
	}

	/**
	 * Custom settings for UTF support
	 *
	 * @return  integer  Zero on success, -1 on failure
	 *
	 * @since   12.1
	 */
	public function setUtf()
	{
		$this->connect();

		return 0;
	}

	/**
	 * This function return a field value as a prepared string to be used in a SQL statement.
	 *
	 * @param   array   $columns      Array of table's column returned by ::getTableColumns.
	 * @param   string  $field_name   The table field's name.
	 * @param   string  $field_value  The variable value to quote and return.
	 *
	 * @return  string  The quoted string.
	 *
	 * @since   12.1
	 */
	public function sqlValue($columns, $field_name, $field_value)
	{
		switch ($columns[$field_name])
		{
		case 'boolean':
		$val = 'NULL';

		if ($field_value == 't')
		{
			$val = 'TRUE';
		}
		elseif ($field_value == 'f')
		{
			$val = 'FALSE';
		}

		break;

		case 'bigint':
		case 'bigserial':
		case 'integer':
		case 'money':
		case 'numeric':
		case 'real':
		case 'smallint':
		case 'serial':
		case 'numeric,':
		$val = strlen($field_value) == 0 ? 'NULL' : $field_value;
		break;

		case 'date':
		case 'timestamp without time zone':
		if (empty($field_value))
		{
			$field_value = $this->getNullDate();
		}

		$val = $this->quote($field_value);
		break;

		default:
		$val = $this->quote($field_value);
		break;
	 }

		return $val;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();
		ibase_commit();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();
		ibase_rollback();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();
		ibase_trans();
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchArray($cursor = null)
	{
		return ibase_fetch_row($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchAssoc($cursor = null)
	{
		return ibase_fetch_assoc($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return ibase_fetch_object(is_null($cursor) ? $this->cursor : $cursor);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function freeResult($cursor = null)
	{
		ibase_free_result($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table    The name of the database table to insert into.
	 * @param   object  &$object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key      The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean    True on success.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$columns = $this->getTableColumns($table);

		$fields = array();
		$values = array();

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) || is_object($v) || $v === null)
			{
				continue;
			}

			// Ignore any internal fields or primary keys with value 0.
			if (($k[0] == '_') || ($k == $key && (($v === 0) || ($v === '0'))))
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->quoteName($k);
			$values[] = $this->sqlValue($columns, $k, $v);
		}

		// Create the base insert statement.
		$query = $this->getQuery(true)
			->insert($this->quoteName($table))
			->columns($fields)
			->values(implode(',', $values));

			$retVal = false;

		if ($key)
		{
			$query->returning($key);

			// Set the query and execute the insert.
			$this->setQuery($query);

			$id = $this->loadResult();

			if ($id)
			{
				$object->$key = $id;
				$retVal = true;
			}
		}
		else
		{
			// Set the query and execute the insert.
			$this->setQuery($query);

			if ($this->execute())
			{
				$retVal = true;
			}
		}

		return $retVal;
	}

	/**
	 * Test to see if the Firebird connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return function_exists('ibase_connect');
	}

	/**
	 * Returns an array containing database's table list.
	 *
	 * @return  array  The database's table list.
	 *
	 * @since   12.1
	 */
	public function showTables()
	{
		$this->connect();

		$this->setQuery('select r.rdb$relation_name
		from rdb$relations r
		where r.rdb$view_blr is null and (r.rdb$system_flag is null or r.rdb$system_flag = 0)
		order by 1'
		);

		$tableList = $this->loadColumn();

		return $tableList;
	}

	/**
	 * Get the substring position inside a string
	 *
	 * @param   string  $substring  The string being sought
	 * @param   string  $string     The string/column being searched
	 *
	 * @return  integer  The position of $substring in $string
	 *
	 * @since   12.1
	 */
	public function getStringPositionSql($substring, $string)
	{
		$this->connect();

		$query = "select position('$substring','$string')  from rdb\$database";
		$this->setQuery($query);
		$position = $this->loadRow();

		return $position['POSITION'];
	}

	/**
	 * Generate a random value
	 *
	 * @return  float  The random generated number
	 *
	 * @since   12.1
	 */
	public function getRandom()
	{
		$this->connect();

		$this->setQuery('select rand() from rdb$database');
		$random = $this->loadAssoc();

		return $random['RAND'];
	}

	/**
	 * Get the query string to alter the database character set.
	 *
	 * @param   string  $dbName  The database name
	 *
	 * @return  string  The query that alter the database query string
	 *
	 * @since   12.1
	 */
	public function getAlterDbCharacterSet($dbName)
	{
		return 'SET NAMES UTF8';
	}

	/**
	 * Get the query string to create new Database in correct PostgreSQL syntax.
	 *
	 * @param   object   $options  object coming from "initialise" function to pass user and database name to database driver.
	 * @param   boolean  $utf      True if the database supports the UTF-8 character set, not used in PostgreSQL "CREATE DATABASE" query.
	 *
	 * @return  string	The query that creates database, owned by $options['user']
	 *
	 * @since   12.1
	 */
	public function getCreateDbQuery($options, $utf)
	{
		$query = 'CREATE DATABASE ' . $this->quoteName($options->db_name);

		return $query;
	}

		/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $query   The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   12.1
	 */
		public function replacePrefix($query, $prefix = '#__')
		{
	 $query = trim($query);
	 
	 if (strpos($query, '\''))
	 {
		// Sequence name quoted with ' ' but need to be replaced
		if (strpos($query, 'currval'))
		{
			$query = explode('currval', $query);
	 
			for ($nIndex = 1, $nIndexMax = count($query); $nIndex < $nIndexMax; $nIndex += 2)
			{
		 $query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
			}
	 
			$query = implode('currval', $query);
		}

		// Sequence name quoted with ' ' but need to be replaced
		if (strpos($query, 'nextval'))
		{
			$query = explode('nextval', $query);
	 
			for ($nIndex = 1, $nIndexMax = count($query); $nIndex < $nIndexMax; $nIndex += 2)
			{
		 $query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
			}
	 
			$query = implode('nextval', $query);
		}

		// Sequence name quoted with ' ' but need to be replaced
		if (strpos($query, 'setval'))
		{
			$query = explode('setval', $query);
	 
			for ($nIndex = 1, $nIndexMax = count($query); $nIndex < $nIndexMax; $nIndex += 2)
			{
		 $query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
			}
	 
			$query = implode('setval', $query);
		}

		$explodedQuery = explode('\'', $query);

		for ($nIndex = 0, $nIndexMax = count($explodedQuery); $nIndex < $nIndexMax; $nIndex += 2)
		{
			if (strpos($explodedQuery[$nIndex], $prefix))
			{
		 $explodedQuery[$nIndex] = str_replace($prefix, $this->tablePrefix, $explodedQuery[$nIndex]);
			}
		}

		$replacedQuery = implode('\'', $explodedQuery);
	 }
	 else
	 {
		$replacedQuery = str_replace($prefix, $this->tablePrefix, $query);
	 }
	 
	 return $replacedQuery;
		}

		/**
	 * Method to release a savepoint.
	 *
	 * @param   string  $savepointName  Savepoint's name to release
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
		public function releaseTransactionSavepoint($savepointName)
		{
	 $this->connect();
	 $this->setQuery('RELEASE SAVEPOINT ' . $this->quoteName($this->escape($savepointName)));
	 $this->execute();
		}

		/**
	 * Method to create a savepoint.
	 *
	 * @param   string  $savepointName  Savepoint's name to create
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
		public function transactionSavepoint($savepointName)
		{
	 $this->connect();
	 $this->setQuery('SAVEPOINT ' . $this->quoteName($this->escape($savepointName)));
	 $this->execute();
		}

		/**
	 * Unlocks tables in the database, this command does not exist in PostgreSQL,
	 * it is automatically done on commit or rollback.
	 *
	 * @return  JDatabaseDriverPostgresql  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
		public function unlockTables()
		{
	 $this->transactionCommit();
	 
	 return $this;
		}

		/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   array    $key      The name of the primary key.
	 * @param   boolean  $nulls    True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
		public function updateObject($table, &$object, $key, $nulls = false)
		{
	 $columns = $this->getTableColumns($table);
	 $fields  = array();
	 $where   = array();
	 
	 if (is_string($key))
	 {
		$key = array($key);
	 }
	 
	 if (is_object($key))
	 {
		$key = (array) $key;
	 }
	 
	 // Create the base update statement.
	 $statement = 'UPDATE ' . $this->quoteName($table) . ' SET %s WHERE %s';
	 
	 // Iterate over the object variables to build the query fields/value pairs.
	 foreach (get_object_vars($object) as $k => $v)
	 {
		// Only process scalars that are not internal fields.
		if (is_array($v) or is_object($v) or $k[0] == '_')
		{
			continue;
		}

		// Set the primary key to the WHERE clause instead of a field to update.
		if (in_array($k, $key))
		{
			$key_val = $this->sqlValue($columns, $k, $v);
			$where[] = $this->quoteName($k) . '=' . $key_val;
			continue;
		}

		// Prepare and sanitize the fields and values for the database query.
		if ($v === null)
		{
			// If the value is null and we want to update nulls then set it.
			if ($nulls)
			{
		 $val = 'NULL';
			}
			// If the value is null and we do not want to update nulls then ignore this field.
			else
			{
		 continue;
			}
		}
		// The field is not null so we prep it for update.
		else
		{
			$val = $this->sqlValue($columns, $k, $v);
		}

		// Add the field to be updated.
		$fields[] = $this->quoteName($k) . '=' . $val;
	 }
	 
	 // We don't have any fields to update.
	 if (empty($fields))
	 {
		return true;
	 }
	 
	 // Set the query and execute the update.
	 $this->setQuery(sprintf($statement, implode(',', $fields), implode(' AND ', $where)));
	 
	 return $this->execute();
		}

		/**
	 * Return the actual SQL Error number
	 *
	 * @return  integer  The SQL Error number
	 *
	 * @since   3.4.6
	 *
	 * @throws  \JDatabaseExceptionExecuting  Thrown if the global cursor is false indicating a query failed
	 */
		protected function getErrorNumber()
		{
	 if ($this->cursor === false)
	 {
		$this->errorMsg = ibase_errmsg();

		throw new JDatabaseExceptionExecuting($this->sql, $this->errorMsg);
	 }
	 
	 return (int) ibase_errcode ();
		}

		/**
	 * Return the actual SQL Error message
	 *
	 * @return  string  The SQL Error message
	 *
	 * @since   3.4.6
	 */
		protected function getErrorMessage()
		{
	 $errorMessage = (string) pg_last_error($this->connection);
	 
	 // Replace the Databaseprefix with `#__` if we are not in Debug
	 if (!$this->debug)
	 {
		$errorMessage = str_replace($this->tablePrefix, '#__', $errorMessage);
	 }
	 
	 return $errorMessage;
		}

		/**
	 * Get the query strings to alter the character set and collation of a table.
	 *
	 * @param   string  $tableName  The name of the table
	 *
	 * @return  string[]  The queries required to alter the table's character set and collation
	 *
	 * @since   CMS 3.5.0
	 */
		public function getAlterTableCharacterSet($tableName)
		{
	 return array();
		}

		/**
	 * Return the query string to create new Database.
	 * Each database driver, other than MySQL, need to override this member to return correct string.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 *                   This object must have "db_name" and "db_user" set.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 *
	 * @since   12.2
	 */
		protected function getCreateDatabaseQuery($options, $utf)
		{
	 return 'CREATE DATABASE ' . $this->quoteName($options->db_name);
		}
	}
