<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Abstract
 * 
 * It abstract class to provide Grid and Pager Interface
 * from a data source: Data Base Table, SQL Query, Array
 * 
 * @category   Core
 * @package    Core_DataGrid
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


/**
 * @see Core_DataGrid_Interface
 */
require_once 'Core/DataGrid/Abstract/Interface.php';

abstract class Core_DataGrid_Abstract implements Core_DataGrid_Abstract_Interface, Countable, IteratorAggregate
{
	protected $_total = null;

	/**
	 * Loading state flag
	 *
	 * @var bool
	 */
	protected $_isCollectionLoaded;

	/**
	 * Data Source Object
	 * @access private
	 * data type Object
	 */
	protected $_datasource = null;

	/**
	 * number of records per page
	 * @access protected
	 * data type integer
	 */
	protected $_limit;

	/** Total number of records
	 * @access protected
	 * data type Int
	 */
	protected $_numberRecords = null;

	/** Current Page Number
	 * @access protected
	 * data type Int
	 */
	protected $_page;

	protected $_defaultLimit = 20;

	/**
	 * Array of records.
	 * @var array
	 * @access private
	 */
	protected $_recordSet = array();

	public function init()
	{}

	public function getSelect()
	{
		return $this->getDataSource()->getSelect();
	}

	public function setSelect($select)
	{
		$this->getDataSource()->setSelect($select);
		return $this;
	}

	public function setPage($page)
	{
		$this->_page = $page;
		return $this;
	}

	public function getPage()
	{
		return $this->_page;
	}

	public function setLimit($limit = null)
	{
		$limit = ($limit !== null)? $limit: $this->_defaultLimit;

		if( !is_int($limit) && ($limit < 0) ) {
			/**
			 * @see Core_DataGrid_Abstract_Exception
			 */
			require_once 'Core/DataGrid/Abstract/Exception.php';
			throw new Core_DataGrid_Abstract_Exception('Invalid number of records ' . $limit);
		}

		$this->_limit = $limit;
		return $this;
	}

	public function getLimit()
	{
		return $this->_limit;
	}

	public function setTotal($total)
	{
		$this->_total = $total;
		return $this;
	}

	public function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Retrieve collection loading status
	 *
	 * @return bool
	 */
	public function isLoaded()
	{
		return $this->_isCollectionLoaded;
	}

	/**
	 * Set collection loading status flag
	 *
	 * @param unknown_type $flag
	 * @return unknown
	 */
	protected function _setIsLoaded($flag = true)
	{
		$this->_isCollectionLoaded = $flag;
		return $this;
	}

	public function setDataSource(Core_DataGrid_DataSource_Interface $dataSource)
	{
		$this->_datasource = $dataSource;
		return $this;
	}

	public function getDataSource()
	{
		return $this->_datasource;
	}

	/**
	 * method getNumberRecords
	 * @access public
	 * @return int
	 * @description return total number of records
	 */
	public function getNumberRecords() {
		if(null === $this->_numberRecords){
			$this->setNumberRecords();
		}

		return $this->_numberRecords;
	}

	/**
	 * method setNumberRecords
	 * @access private
	 * @return int
	 * @description calculate total number of records
	 */
	protected function setNumberRecords() {
		if(null === $this->_numberRecords){
			if (null !== $this->getDataSource()) {
				$this->_numberRecords = $this->getDataSource()->count();
			} else {
				/**
				 * @see Core_DataGrid_Abstract_Exception
				 */
				require_once 'Core/DataGrid/Abstract/Exception.php';
				throw new Core_DataGrid_Abstract_Exception("Cannot fetch data: no datasource driver loaded.");
			}
		}
	}

	public function fetch()
	{
		try{
			$this->_fetch();
		} catch (Exception $e) {
			/**
			 * @see Core_DataGrid_Abstract_Exception
			 */
			require_once 'Core/DataGrid/Abstract/Exception.php';
			throw new Core_DataGrid_Abstract_Exception("Message Exception: " . $e->getMessage() . "\n");
		}
		return $this;
	}

	public function bindDataSource(Core_DataGrid_DataSource_Interface $source)
	{
		$this->setDataSource($source);

		try{
			$this->_fetch();
		} catch (Exception $e) {
			/**
			 * @see Core_DataGrid_Abstract_Exception
			 */
			require_once 'Core/DataGrid/Abstract/Exception.php';
			throw new Core_DataGrid_Abstract_Exception("Message Exception: " . $e->getMessage() . "\n");
		}
		return $this;
	}

	public function count()
	{
		return (null === $this->getTotal())? 0: $this->getTotal();
	}

	public function getIterator()
	{
		if($this->getTotal() === null){
			$this->_fetch();
		}

		return new ArrayIterator($this->_recordSet);
	}

	public function getPager($adapterName = null)
	{
		return Core_DataGrid_Pager::factory($adapterName, $this->getPage(), $this->getLimit(), $this->getNumberRecords());
	}

	public function renderPager($adapterName = null)
	{
		return $this->getPager($adapterName)->build()->displayPager();
	}

	/**
	 * Render block
	 *
	 * @return string
	 */
	public function render($adapterName = null)
	{
		if($this->getTotal() === null){
			$this->_fetch();
		}

		return $this->_render($adapterName = null);
	}

    /**
     * Serialize as string
     *
     * Proxies to {@link render()}.
     * 
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        
        return '';
    }

	abstract protected function _fetch();

	abstract protected function _render($adapterName = null);
}
