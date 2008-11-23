<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_DataSource_DbSelect
 *
 * It class to provide a DataSource Zend_Db_Select Object Implementation
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

class Core_DataGrid_DataSource_DbSelect implements Core_DataGrid_DataSource_Interface
{
	/**
	 * Table Info
	 *
	 * @var Array $this->_info
	 */
	private $_info;

	private $sortString = null;

	/**
	 * Zend_Db_Select
	 *
	 * @var Zend_Db_Select $this->_select
	 */
	private $_select;

	private $_conn = null;

	private static $_count = null;

	public function __construct(Zend_Db_Select $select = null, Zend_Db_Adapter $conn = null)
	{
		$this->setSelect($select);

		if(null !== $conn){
			$this->setConnection($conn);
		}
	}

	/**
	 * Set Zend_Db_Select instance
	 *
	 * @return Zend_Db_Select
	 */
	public function getSelect()
	{
		if(null === $this->_select){
			$this->setSelect();
		}

		return $this->_select;
	}

	/**
	 * Set Zend_Db_Select instance
	 *
	 * @return Zend_Db_Select
	 */
	public function setSelect($select = null)
	{
		if(null === $select)
		{
			$conn = Zend_Db_Table::getDefaultAdapter();

			if (!$conn instanceof Zend_Db_Adapter_Abstract){
				$conn = Zend_Registry::getInstance()->get('db');
			}

			$select = $conn->select();
				
			$this->setConnection($conn);
		}

		$this->_select = $select;
		return $this;
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
	public function fetch($offset = 0, $size = null, $toArray = false)
	{
		$count = is_null($size) ? $this->count() : $size;

		$this->getSelect()
		->order($this->getSort())
		->limit($count, $offset);

		return $this->getSelect()->query()->fetchAll();
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
		if( null === self::$_count){

			$count = $this->getConnection()->fetchOne($this->_getSelectCountSql());

			self::$_count = (int) $count;
			return self::$_count;
		} else {
			return self::$_count;
		}
	}

	/**
	 *
	 */
	public function sort($sortSpec, $sortDir = 'ASC') {
		$this->sortString = new Zend_Db_Expr("$sortSpec $sortDir");
	}

	/**
	 *
	 */
	public function getSort() {
		return $this->sortString;
	}

	/**
	 * Get sql for get record count
	 *
	 * @return  string
	 */
	private function _getSelectCountSql()
	{
		$countSelect = clone $this->getSelect();

		$countSelect->reset(Zend_Db_Select::ORDER);
		$countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
		$countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
		$countSelect = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(*) from ', $countSelect->__toString());
		return $countSelect;
	}

	public function getColumns()
	{
		// Fetch Select Columns
		$rawColumns = $this->getSelect()->getPart(Zend_Db_Select::COLUMNS);
		$columns = array();
		$flag = false;
		// Get columns and Force casting as strings
		foreach($rawColumns as $col)
		{
			if($col[1] === "*"){
				$flag = true;
				break;
			}
			
			if($col[1] instanceof Zend_Db_Expr){
				$colResult = $col[2];
			} else {
				$colResult = $col[1];
			}
			
			$columns[] = (string) $colResult;
		}
		
		if($flag === true){
			$columns = $this->_getColumnsAlternative();
		}

		return $columns;
	}
	
	private function _getColumnsAlternative()
	{
		$query = $this->getSelect()->__toString();
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
