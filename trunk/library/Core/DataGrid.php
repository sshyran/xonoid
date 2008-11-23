<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid
 *
 * It class to provide Web Grid that draws data in a render
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
require_once 'Core/DataGrid/Abstract.php';

/**
 * @see Core_DataGrid_Interface
 */
require_once 'Core/DataGrid/Interface.php';

class Core_DataGrid extends Core_DataGrid_Abstract implements Core_DataGrid_Interface
{
	protected $_columns = array();

	protected $_lastColumnId;

	protected $_defaultSort = false;

	protected $_defaultDir = 'desc';

	/** Current order
	 * @access protected
	 * data type String
	 */
	protected $order = null;

	/** Current direction
	 * @access protected
	 * data type String
	 */
	protected $direction = null;

	protected $_sortable = true;

	/**
	 * Empty grid text
	 *
	 * @var sting|null
	 */
	protected $_emptyText;

	/**
	 * Empty grid text CSS class
	 *
	 * @var sting|null
	 */
	protected $_emptyTextCss = 'a-center';

	protected $_generateColumns = true;

	protected $_idFieldName = 'id';

	/**
	 * Data Grid constructor
	 * @access public
	 * @param Core_DataGrid_DataSource_Interface dataSource
	 * @param int limit
	 * @param array params
	 */

	public function __construct(Core_DataGrid_DataSource_Interface $dataSource = null, $limit = null, array $_params = array())
	{
		$this->setLimit($limit);

		$this->_emptyText = 'No records found.';

		$filters = array(
            'orderBy'     => 'StripTags',
			'direction'   => 'alpha',
			'page'   	  => 'digits');

		$valids = array(
            'orderBy'     => array('allowEmpty' => true),
		    'direction'   => array('Alpha', 'allowEmpty' => true),
			'page'        => array('int', 'default' => 0));

		Zend_Loader::loadClass('Zend_Filter_Input');

		if(empty($_params)){
			$_params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
		}

		$input = new Zend_Filter_Input($filters, $valids, $_params);

		if (!$input->isValid()) {
			$errors = '';

			foreach ($input->getMessages() as $messageId => $messages) {
				$message = current($messages);
				$errors .= "'$messageId': $message\n";
			}

			/**
			 * @see Core_DataGrid_Exception
			 */
			require_once 'Core/DataGrid/Exception.php';
			throw new Core_DataGrid_Exception('Invalid Parmas for DataGrid: '.$errors);
		}

		$this->setPage((int)$input->page);
		$this->setOrder( !empty($input->orderBy)? $input->orderBy: null );
		$this->setDirection( !empty($input->direction)? $input->direction: null );

		if( null !== $dataSource){
			$this->setDataSource($dataSource);
		}

		$this->init();
	}

	public function getGenerateColumns()
	{
		return $this->_generateColumns;
	}

	public function setGenerateColumns($generateColumns)
	{
		$this->_generateColumns = $generateColumns;
		return $this;
	}

	public function setDefaultSort($sort)
	{
		if(is_array($sort)){
			list($sort, $dir) = each($sort);
			$this->setDefaultDir($dir);
		}

		$this->_defaultSort = $sort;
		return $this;
	}

	public function getDefaultSort()
	{
		return $this->_defaultSort;
	}

	public function setDefaultDir($dir)
	{
		$this->_defaultDir = $dir;
		return $this;
	}

	public function getDefaultDir()
	{
		return $this->_defaultDir;
	}

	public function setOrder($order)
	{
		$this->order = $order;
		return $this;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function setDirection($direction)
	{
		$this->direction = $direction;
		return $this;
	}

	public function getDirection()
	{
		return $this->direction;
	}

	public function setSortable($sortable)
	{
		$this->_sortable = $sortable;
		return $this;
	}

	public function getSortable()
	{
		return $this->_sortable;
	}

	/**
	 * Set empty text for grid
	 *
	 * @param string $text
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	public function setEmptyText($text)
	{
		$this->_emptyText = $text;
		return $this;
	}

	/**
	 * Return empty text for grid
	 *
	 * @return string
	 */
	public function getEmptyText()
	{
		return $this->_emptyText;
	}

	/**
	 * Set empty text CSS class
	 *
	 * @param string $text
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	public function setEmptyTextClass($cssClass)
	{
		$this->_emptyTextCss = $text;
		return $this;
	}

	/**
	 * Return empty text CSS class
	 *
	 * @return string
	 */
	public function getEmptyTextClass()
	{
		return $this->_emptyTextCss;
	}

	public function getSortIconLink()
	{
		return ($this->getDirection() == 'asc')? '&dArr;':'&uArr;';
	}

	public function getDirLink()
	{
		return ($this->getDirection() == 'asc')? 'desc' : 'asc';
	}

	public function setIdFieldName($fieldName)
	{
		$this->_idFieldName = $fieldName;
		return $this;
	}

	public function getIdFieldName()
	{
		return $this->_idFieldName;
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
			require_once 'Core/DataGrid/Exception.php';
			throw new Core_DataGrid_Exception("Cannot fetch data: no datasource driver loaded.");
		}

		$this->setNumberRecords();

		$columnId = ($this->getOrder() !== null)? $this->getOrder(): $this->getDefaultSort();
		$dir = ($this->getDirection() !== null)? $this->getDirection(): $this->getDefaultDir();
		$dir = strtolower($dir);

		if(empty($columnId)){
			$columnId = $this->getIdFieldName();
		}

		if (empty($this->_columns) && true === $this->getGenerateColumns()) {
			$this->addDefaultColumn();
		}

		if (isset($this->_columns[$columnId])) {
			$this->_columns[$columnId]->setDir($dir);
		}

		if(!empty($columnId) && !empty($dir)){
			$this->getDataSource()->sort($columnId, $dir);
		} else {
			/**
			 * @see Core_DataGrid_Exception
			 */
			require_once 'Core/DataGrid/Exception.php';
			throw new Core_DataGrid_Exception("Cannot sort data: OrderBy or Direction are empty.");
		}

		$this->setOrder($columnId);
		$this->setDirection($dir);

		$this->_recordSet = $this->getDataSource()->fetch($this->getPage(), $this->getLimit(), true);

		$this->setTotal(count($this->_recordSet));
		$this->_setIsLoaded();
	}

	public function addDefaultColumn()
	{
		$colums = $this->getDataSource()->getColumns();

		foreach($colums as $colum){
			$this->addColumn($colum, array('header' => ucfirst(str_replace('_', '&nbsp;', $colum)) ));
		}
	}

	/**
	 * Add column to grid
	 *
	 * @param   string $columnId
	 * @param   array || Varien_Object $column
	 * @return  Mage_Adminhtml_Block_Widget_Grid
	 */
	public function addColumn($columnId, $column)
	{
		if (is_array($column)) {
			$this->_columns[$columnId] = new Core_DataGrid_Column();
			$this->_columns[$columnId]->setData($column);
		} else if($column instanceof Core_DataGrid_Column) {
			$this->_columns[$columnId] = $column;
		} else {
			/**
			 * @see Core_DataGrid_Exception
			 */
			require_once 'Core/DataGrid/Exception.php';
			throw new Core_DataGrid_Exception('Wrong column format');
		}

		$this->_columns[$columnId]->setId($columnId);
		$this->_columns[$columnId]->setIndex($columnId);
		$this->_columns[$columnId]->setGrid($this);
		$this->_lastColumnId = $columnId;
		return $this;
	}

	public function getLastColumnId()
	{
		return $this->_lastColumnId;
	}

	public function getColumnCount()
	{
		return count($this->getColumns());
	}

	/**
	 * Retrieve grid column by column id
	 *
	 * @param   string $columnId
	 * @return  Varien_Object || false
	 */
	public function getColumn($columnId)
	{
		if (!empty($this->_columns[$columnId])) {
			return $this->_columns[$columnId];
		}
		return false;
	}

	/**
	 * Retrieve all grid columns
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * Render block
	 *
	 * @return string
	 */
	protected function _render($adapterName = null)
	{
		if (empty($this->_columns)){
			/**
			 * @see Core_DataGrid_Exception
			 */
			require_once 'Core/DataGrid/Exception.php';
			throw new Core_DataGrid_Exception("Cannot render columns: the columns are empty.");
		}

		return Core_DataGrid_Render::factory($this, $adapterName)->render();
	}
}
