<?php
/**
 * Zsamer Framework
 *
 * Core_Paginator
 *
 * It class to provide Paginator
 * from a data source: Data Base Table, SQL Query, Array
 * 
 * @category   Core
 * @package    Core_Paginator
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


/**
 * @see Core_Paginator_Interface
 */
require_once 'Core/DataGrid/Abstract.php';

class Core_Paginator extends Core_DataGrid_Abstract
{
	/**
	 * Mix Object/Array of records.
	 * @var array
	 * @access private
	 */
	protected $_recordSet = null;

	/**
	 * Paginator constructor
	 * @access public
	 * @param Core_DataGrid_DataSource_Interface dataSource
	 * @param int limit
	 * @param array params
	 */
	public function __construct(Core_DataGrid_DataSource_Interface $dataSource = null, $limit = null, $page = null)
	{
		$this->setLimit($limit);

		if(null === $page){
			$page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 0);
		}

		$this->setPage((int)$page);

		if( null !== $dataSource){
			$this->setDataSource($dataSource);
		}

		$this->init();
	}

	protected function _fetch()
	{
		if ($this->isLoaded()) {
			return $this;
		}

		if (null === $this->getDataSource()) {
			/**
			 * @see Core_DataGrid_Exception
			 */
			require_once 'Core/Paginator/Exception.php';
			throw new Core_Paginator_Exception("Cannot fetch data: no datasource driver loaded.");
		}

		$this->setNumberRecords();

		$this->_recordSet = $this->getDataSource()->fetch($this->getPage(), $this->getLimit());

		$this->setTotal(count($this->_recordSet));
		$this->_setIsLoaded();
	}

	protected function _render($adapterName = null)
	{
		return $this->renderPager($adapterName);
	}
}