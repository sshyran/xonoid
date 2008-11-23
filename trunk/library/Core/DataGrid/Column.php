<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column
 * 
 * It class to provide a Grid Column Object
 * This class represents a single column object for the DataGrid.
 * from a render type: Text, Number, Link, Date, DateTime
 * Actions, Price, LongTet etc.
 * 
 * @category   Core
 * @package    Core_DataGrid
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

class Core_DataGrid_Column
{
	protected $_grid = null;

	protected $_id = null;

	protected $_class = null;

	protected $_style = null;

	protected $_header = null;

	protected $_align = null;

	protected $_index = null;

	protected $_type = null;

	protected $_sortable = true;

	protected $_dir = null;

	protected $_sortIcon = null;

	protected $_format = null;

	protected $_default = null;

	protected $_suffix = null;

	protected $_stringLimit = null;

	protected $_width = null;

	protected $_actions = array();

	protected $_options = array();

	protected $_links = null;

	protected $_cssClass = null;

	protected $_renderer;

	public function __construct($type = null, $header = null, $width = null, $align = null)
	{
		if ($type !== null) {
			$this->setType($type);
		}

		if ($header !== null) {
			$this->setHeader($header);
		}

		if ($width !== null) {
			$this->setWidth($width);
		}

		if ($align !== null) {
			$this->setAlign($align);
		}
	}

	public function setData(array $data = array())
	{
		foreach($data as $key => $value){
			$method = "set".ucwords($key);
			$this->{$method}($value);
		}
	}

	public function setGrid(Core_DataGrid_Interface $grid)
	{
		$this->_grid = $grid;
		return $this;
	}

	public function getGrid()
	{
		return $this->_grid;
	}

	public function getId()
	{
		if ($this->_id === null) {
			$this->setId('id_'.md5(microtime()));
		}
		return $this->_id;
	}

	public function setId($id)
	{
		$this->_id =  $id;
		return $this;
	}

	public function getClass()
	{
		return $this->_class;
	}

	public function setClass($class)
	{
		$this->_class = $class;
		return $this;
	}

	public function getStyle()
	{
		return $this->_style;
	}

	public function setStyle($style)
	{
		$this->_style = $style;
		return $this;
	}

	public function isLast()
	{
		return $this->getId() == $this->getGrid()->getLastColumnId();
	}

	public function setHeader($header)
	{
		$this->_header = $header;
		return $this;
	}

	public function getHeader()
	{
		return $this->_header;
	}

	public function setAlign($align)
	{
		$this->_align = $align;
		return $this;
	}

	public function getAlign()
	{
		return $this->_align;
	}

	public function setIndex($index)
	{
		$this->_index = $index;
		return $this;
	}

	public function getIndex()
	{
		return $this->_index;
	}

	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}

	public function getType()
	{
		return $this->_type;
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

	public function setDir($dir)
	{
		$this->_dir = $dir;
		return $this;
	}

	public function getDir()
	{
		return $this->_dir;
	}

	public function setSortIcon($sortIcon)
	{
		$this->_sortIcon = $sortIcon;
		return $this;
	}

	public function getSortIcon()
	{
		return $this->_sortIcon;
	}

	public function setFormat($format)
	{
		$this->_format = $format;
		return $this;
	}

	public function getFormat()
	{
		return $this->_format;
	}

	public function setDefault($default)
	{
		$this->_default = $default;
		return $this;
	}

	public function getDefault()
	{
		return $this->_default;
	}

	public function setSuffix($suffix)
	{
		$this->_suffix = $suffix;
		return $this;
	}

	public function getSuffix()
	{
		return $this->_suffix;
	}

	public function setWidth($width)
	{
		$this->_width = $width;
		return $this;
	}

	public function getWidth()
	{
		return $this->_width;
	}

	public function setStringLimit($stringLimit)
	{
		$this->_stringLimit = $stringLimit;
		return $this;
	}

	public function getStringLimit()
	{
		return $this->_stringLimit;
	}

	public function setActions(array $actions = array())
	{
		$this->_actions = $actions;
		return $this;
	}

	public function getActions()
	{
		return $this->_actions;
	}

	public function setOptions(array $options = array())
	{
		$this->_options = $options;
		return $this;
	}

	public function getOptions()
	{
		return $this->_options;
	}

	public function setLinks($links)
	{
		$this->_links = $links;
		return $this;
	}

	public function getLinks()
	{
		return $this->_links;
	}

	public function getHeaderHtml()
	{
		return $this->getRenderer()->renderHeader();
	}

	/**
	 * Retrieve row column field value for display
	 *
	 * @param   Core_Object $row
	 * @return  string
	 */
	public function getRowField($row)
	{
		return $this->getRenderer()->render($row);
	}

	public function setRenderer($renderer)
	{
		$this->_renderer = $renderer;
		return $this;
	}

	protected function _getRendererByType()
	{
		switch (strtolower($this->getType())) {
			case 'number':
				$rendererClass = 'Core_DataGrid_Column_Renderer_Number';
				break;
			case 'action':
				$rendererClass = 'Core_DataGrid_Column_Renderer_Action';
				break;
			case 'options':
				$rendererClass = 'Core_DataGrid_Column_Renderer_Options';
				break;
			case 'text':
				$rendererClass = 'Core_DataGrid_Column_Renderer_Longtext';
				break;
			case 'link':
				$rendererClass = 'Core_DataGrid_Column_Renderer_Link';
				break;
			default:
				$rendererClass = 'Core_DataGrid_Column_Renderer_Text';
				break;
		}
		return $rendererClass;
	}

	public function getRenderer()
	{
		if (!$this->_renderer) {
			$rendererClass = $this->_getRendererByType();
			$this->_renderer = new $rendererClass();
			$this->_renderer->setColumn($this);
		}

		return $this->_renderer;
	}

	/**
	 * method getLink
	 * @access public
	 * @return string
	 * @description filter and return the URL and query string
	 */
	public function getLink($dir, $sort)
	{
		return Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array( 'direction' => $dir, 'orderBy' => $sort ));
	}

	public function getHtmlProperty()
	{
		return $this->getRenderer()->renderProperty();
	}

	public function getCssClass()
	{
		if (is_null($this->_cssClass)) {
			if ($this->getAlign()) {
				$this->_cssClass .= 'a-'.$this->getAlign();
			}
		}

		// Add a custom css class for column
		if (null !== $this->getClass()) {
			$this->_cssClass .= ' '. $this->getClass();
		}

		return $this->_cssClass;
	}

	public function getCssProperty()
	{
		return $this->getRenderer()->renderCss();
	}

	public function getHeaderCssClass()
	{
		$class = '';
		if (($this->getSortable()===false) || ($this->getGrid()->getSortable()===false)) {
			$class .= ' no-link';
		}

		if ($this->isLast()) {
			$class .= ' last';
		}
		return $class;
	}

	public function getHeaderHtmlProperty()
	{
		$str = '';
		if ($class = $this->getHeaderCssClass()) {
			$str.= ' class="'.$class.'"';
		}

		return $str;
	}

	public function getStyleProperty()
	{
		$str = '';
		if ($style = $this->getStyle()) {
			$str.= 'style="'.$style.'"';
		}

		return $str;
	}
}