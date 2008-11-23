<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Pager_Abstract
 *
 * It class to provide a Abstract Pager Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Pager
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


/**
 * @see Core_DataGrid_Pager_Abstract_Interface
 */
require_once 'Core/DataGrid/Pager/Abstract/Interface.php';

abstract class Core_DataGrid_Pager_Abstract implements Core_DataGrid_Pager_Abstract_Interface
{
	/**
	 * number of records per page
	 * @access protected
	 * data type integer
	 */
	protected $_limit;

	/**
	 * generated output for records and paging links
	 * @access protected
	 * data type string
	 */
	protected $_output;


	/** ID attribute for styling paging links
	 * @access protected
	 * data type string
	 */
	protected $_linksId = 'paginglinks';


	/** Current Page Number
	 * @access protected
	 * data type Int
	 */
	protected $_page;


	/** Interval or Rank of the Pager (Floor Page)
	 * @access protected
	 * data type Int
	 */
	protected $_onPage;


	/** Next String
	 * @access protected
	 * data type String
	 */
	protected $_next = 'Next »';


	/** Previous String
	 * @access protected
	 * data type String
	 */
	protected $_previous = '« Previous';


	/** Total number of records
	 * @access protected
	 * data type Int
	 */
	protected $_numberRecords;


	/** Total total number of pages
	 * @access protected
	 * data type Int
	 */
	protected $_numberPages;


	protected static $_seperator = '<span>&nbsp;&nbsp;</span>';

	/**
	 * Constructor
	 *
	 * @access  public
	 */
	public function __construct($_page, $_pageLimit, $_recordsNum)
	{
		$this->setPage($_page)
		->setlimit($_pageLimit)
		->setNumberRecords($_recordsNum)
		->setNumberPages();
	}

	/**
	 * method setLinksId
	 * @access public
	 * @return void
	 * @set String Links Id
	 */
	public function setLinksId($linksId)
	{
		$this->_linksId = $linksId;
		return $this;
	}

	/**
	 * method getLinksId
	 * @access public
	 * @return String
	 * @return String Links Id
	 */
	public function getLinksId()
	{
		return $this->_linksId;
	}

	/**
	 * method setNumberPages
	 * @access private
	 * @return int
	 * @description set total number of pages
	 */
	public function setNumberPages()
	{
		// calculate number of pages
		$this->_numberPages = ceil($this->getNumberRecords()/$this->getLimit());
		return $this;
	}

	/**
	 * method getNumberPages
	 * @access public
	 * @return int
	 * @description return Number Pages
	 */
	public function getNumberPages()
	{
		return $this->_numberPages;
	}

	/**
	 * method setPage
	 * @access public
	 * @return int
	 * @description set current page
	 */
	public function setPage($_page)
	{
		$this->_page = $_page;
		return $this;
	}

	/**
	 * method getPage
	 * @access public
	 * @return int
	 * @description return current page
	 */
	public function getPage()
	{
		return $this->_page;
	}

	/**
	 * method setlimit
	 * @access public
	 * @return int
	 * @description set Number Per Page
	 */
	public function setLimit($_pageLimit)
	{
		$this->_limit = $_pageLimit;
		return $this;
	}

	/**
	 * method getlimit
	 * @access public
	 * @return int
	 * @description return Number Per Page
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * method setNumberRecords
	 * @access public
	 * @return int
	 * @description set total number of records
	 */
	public function setNumberRecords($_recordsNum)
	{
		$this->_numberRecords = $_recordsNum;
		return $this;
	}

	/**
	 * method getNumberRecords
	 * @access public
	 * @return int
	 * @description return total number of records
	 */
	public function getNumberRecords()
	{
		return $this->_numberRecords;
	}

	/**
	 * method setOnPage
	 * @access private
	 * @return int
	 * @description calculate the floor page
	 */
	public function setOnPage()
	{
		$this->_onPage = floor($this->getPage() / $this->getLimit()) + 1;
		return $this;
	}

	/**
	 * method getOnPage
	 * @access public
	 * @return int
	 * @description calculate the floor page
	 */
	public function getOnPage()
	{
		return $this->_onPage;
	}

	/**
	 * method setNext
	 * @access public
	 * @return void
	 * @set String Next Page
	 */
	public function setNext($next)
	{
		$this->_next=$next;
		return $this;
	}

	/**
	 * method getNext
	 * @access public
	 * @return String
	 * @return String Next Page
	 */
	public function getNext()
	{
		return $this->_next;
	}

	/**
	 * method setPrevious
	 * @access public
	 * @return void
	 * @set String Previous Page
	 */
	public function setPrevious($previous)
	{
		$this->_previous = $previous;
		return $this;
	}

	/**
	 * method getPrevious
	 * @access public
	 * @return String
	 * @return String Previous Page
	 */
	public function getPrevious()
	{
		return $this->_previous;
	}

	/**
	 * method setOutput
	 * @access public
	 * @return string Html
	 * @description set output the Pager
	 */
	public function setOutput($output)
	{
		$this->_output = $output;
		return $this;
	}

	/**
	 * method getOutput
	 * @access public
	 * @return string Html
	 * @description get output the Pager
	 */
	public function getOutput()
	{
		return $this->_output;
	}

	/**
	 * method displayPager
	 * @access public
	 * @return string Html
	 * @description display or output the Pager
	 */
	public function displayPager()
	{
		return $this->getOutput();
	}

	/**
	 * method getLink
	 * @access public
	 * @return string
	 * @description filter and return the URL and query string
	 */
	public function getLink($page = null, $emptyQuery = false)
	{
		return Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array( 'page' => $page ), null, $emptyQuery);
	}

	/**
	 * Handles building the body of the table
	 *
	 * @access  public
	 * @return  void
	 */
	abstract public function build($addPrevNextText = true);
}
