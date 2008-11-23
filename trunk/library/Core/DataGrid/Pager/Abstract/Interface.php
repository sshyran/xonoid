<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Pager_Abstract_Interface
 *
 * It class to provide a Interface Pager Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Pager_Abstract
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

interface Core_DataGrid_Pager_Abstract_Interface
{
	public function setPage($_page);
	
	public function getPage();
	
	public function setLimit($_pageLimit);
	
	public function getLimit();
	
	public function setNumberPages();
	
	public function getNumberPages();
	
	public function setNumberRecords($_recordsNum);
	
	public function getNumberRecords();
}
