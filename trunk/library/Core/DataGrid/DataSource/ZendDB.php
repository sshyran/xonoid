<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_DataSource_ZendDB
 *
 * It class to provide a DataSource Zend_Db With SQL Query String Object Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_DataSource
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


/**
 * @see Core_DataGrid_DataSource_Interface
 */
require_once 'Core/DataGrid/DataSource/Interface.php';

class Core_DataGrid_DataSource_ZendDB implements Core_DataGrid_DataSource_Interface
{
	private static $_count = null;

	/**
	 * Reference to the PEAR::DB object
	 *
	 * @var object DB
	 * @access private
	 */
	private $_conn;

	/**
	 * The query string
	 *
	 * @var string
	 * @access private
	 */
	private $_query;

	private $sortString = null;


	public function __construct($query)
	{
		$conn = Zend_Db_Table::getDefaultAdapter();

		if (!$conn instanceof Zend_Db_Adapter_Abstract){
			$conn = Zend_Registry::getInstance()->get('db');
		}

		$this->setConnection($conn);

		if(!is_string($query)){
			require_once 'Core/DataGrid/DataSource/Exception.php';
			throw new Core_DataGrid_DataSource_Exception('Core_DataGrid_DataSource_ZendDB: Query parameter must be a string');
		}

		$this->_query = $query;
	}

	public function getConnection()
	{
		return $this->_conn;
	}

	public function setConnection($conn)
	{
		$this->_conn = $conn;
		return $this;
	}

	/**
	 * Fetching method prototype
	 *
	 * When overloaded this method must return a 2D array of records
	 * on success or a PEAR_Error object on failure.
	 *
	 * @abstract
	 * @param   integer $offset     Limit offset (starting from 0)
	 * @param   integer $len        Limit length
	 * @return  object              PEAR_Error with message
	 *                              "No data source driver loaded"
	 * @access  public
	 */
	public function fetch($offset = 0, $limit = null, $toArray = false)
	{
		$query = $this->_query;

		$query = preg_replace('#\sLIMIT\s.*$#isD', ' ', $query);

		if( strpos( strtoupper($query), "ORDER BY" ) === false ) {
			$query .= " ORDER BY $this->sortString";
		} else {
			$query .= ', ' . $this->sortString;
		}

		$query = $this->getConnection()->limit($query, $limit, $offset );

		$result = $this->getConnection()->query($query);

		$recordSet = $result->fetchAll();

		$result = null;

		return $recordSet;
	}

	/**
	 * Counting method prototype
	 *
	 * Note: must be called before fetch()
	 *
	 * When overloaded, this method must return the total number or records
	 * or a PEAR_Error object on failure
	 *
	 * @abstract
	 * @return  object              PEAR_Error with message
	 *                              "No data source driver loaded"
	 * @access  public
	 */
	public function count(){
		if( null !== self::$_count){
			return self::$_count;
		}

		$query = eregi_replace("select[[:space:]](.*)[[:space:]]from", "SELECT COUNT(*) FROM", strtolower($this->_query));
		$count = $this->getConnection()->fetchOne($query);

		self::$_count = (int) $count;
		return self::$_count;
	}

	/**
	 *
	 */
	public function sort($sortSpec, $sortDir = 'asc') {
		$this->sortString = "`$sortSpec` $sortDir";
	}

	public function getColumns()
	{
		$query = $this->_query;
		$query = $this->getConnection()->limit($query, 1);
		/**
		 * Send Query
		 **/
		$rst = $this->getConnection()->query( $query );

		/**
		 * Fetch Column Names / Headers
		 **/
		$totalColumns = $rst->columnCount();
		$cols = array();
		for( $i = 0; $i < $totalColumns; $i++ ) {
			$data = $rst->getColumnMeta($i);
			$cols[$i] = $data['name'];
		}
		$rst = null;

		return $cols;
	}
}
