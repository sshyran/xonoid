<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_DataSource_Propel
 *
 * It class to provide a DataSource Propel ORM Object Implementation
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

class Core_DataGrid_DataSource_Propel implements Core_DataGrid_DataSource_Interface
{
	private $sortString = null;

	/**
	 * Zend_Db_Select
	 *
	 * @var Zend_Db_Select $this->_select
	 */
	private $_select;

	private static $_count = null;

	private $peerClass;

	private $peerSelectMethod;

	public function __construct($c = null, $peerClass = null, $peerSelectMethod = null)
	{
		if (!isset($peerSelectMethod)) {
			$peerSelectMethod = 'doSelect';
		}

		$this->setSelect($c);
		$this->setPeerClass($peerClass);
		$this->setPeerSelectMethod($peerSelectMethod);
	}


	/**
	 * Set the Peer Classname
	 *
	 * @param      string $class
	 * @return     void
	 */
	public function setPeerClass($class)
	{
		$this->peerClass = $class;
	}

	/**
	 * Return the Peer Classname.
	 * @return     string
	 */
	public function getPeerClass()
	{
		return $this->peerClass;
	}

	/**
	 * Set the Peer select method.
	 *
	 * @param      string $method The name of the static method to call on the Peer class.
	 * @return     void
	 */
	public function setPeerSelectMethod($method)
	{
		$this->peerSelectMethod = $method;
	}

	/**
	 * Return the Peer select method.
	 * @return     string
	 */
	public function getPeerSelectMethod()
	{
		return $this->peerSelectMethod;
	}

	/**
	 * Get Criteria instance
	 *
	 * @return Criteria
	 */
	public function getSelect()
	{
		if(null === $this->_select){
			$this->setSelect();
		}

		return $this->_select;
	}

	/**
	 * Set Criteria instance
	 *
	 * @return Core_Widget_Grid_Adapter_Propel/Void
	 */
	public function setSelect($select = null)
	{
		if(null === $select){
			$select = new Criteria();
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

		$orderExpr = $this->getSort()->__toString();
		$orderExprArr = explode(' ', $orderExpr);

		if(isset($orderExprArr[0])){
			$sort = $this->_getConstantPeerClass($orderExprArr[0]);
		}

		$dir = isset($orderExprArr[1])?$orderExprArr[1]:Criteria::DESC;

		if(strtoupper($dir) == Criteria::DESC){
			$this->getSelect()->addDescendingOrderByColumn($sort);
		} else {
			$this->getSelect()->addAscendingOrderByColumn($sort);
		}
			
		$this->getSelect()->setOffset($offset);
		$this->getSelect()->setLimit($size);

		$collection = call_user_func(array($this->getPeerClass(), $this->getPeerSelectMethod()), $this->getSelect());

		if($toArray !== false){
			$rows = array();
			foreach ($collection as $row) {
				$rows[] = $row->toArray('fieldName');
			}
			return $rows;
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

			$countCriteria = clone $this->getSelect();
			$countCriteria->setLimit(0);
			$countCriteria->setOffset(0);

			$recordCount = call_user_func(array($this->getPeerClass(), 'doCount'),$countCriteria);

			self::$_count = (int) $recordCount;
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

	public function getColumns()
	{
		return call_user_func(array($this->getPeerClass(), 'getFieldNames'), 'fieldName');
	}

	protected function _getConstantPeerClass($field){
		return constant(sprintf('%s::%s', $this->getPeerClass(), strtoupper($field)));
	}
}
