<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_DataSource_Table
 *
 * It class to provide a DataSource Zend_Db_Table_Abstract Object Implementation
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

class Core_DataGrid_DataSource_Table implements Core_DataGrid_DataSource_Interface
{
	protected $_conn = null;

	/**
	 * Zend_Db_Table_Abstract
	 *
	 * @var Zend_Db_Table_Abstract $this->_table
	 */
	private $_table;

	private $sortString = null;

	/**
	 * Zend_Db_Select
	 *
	 * @var Zend_Db_Select $this->_select
	 */
	private $_select;

	private static $_count = null;

	public function __construct(Zend_Db_Table_Abstract $table)
	{
		$this->setTable($table)
		->setConnection($table->getAdapter());
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

	public function getTable()
	{
		return $this->_table;
	}

	public function setTable($table)
	{
		$this->_table = $table;
		return $this;
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
		if(null === $select){
			$select = $this->getTable()->select();
		}

		$this->_select = $select;
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

		$collection = $this->getTable()->fetchAll($this->getSelect());
		
		if($toArray !== false){
			return $collection->toArray();
		}
		
		return $collection;
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
		$info = $this->getTable()->info();
		return $info['cols'];
	}
}
