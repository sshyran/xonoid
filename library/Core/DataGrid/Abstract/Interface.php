<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Abstract_Interface
 *
 * It class to provide a Abstract DataGrid Interface
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Abstract
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

interface Core_DataGrid_Abstract_Interface
{
	public function setSelect($select);

	public function getSelect();

	public function getPage();

	public function setPage($page);

	public function setLimit($limit = null);

	public function getLimit();

	public function getNumberRecords();

	public function setDataSource(Core_DataGrid_DataSource_Interface $dataSource);

	public function getDataSource();

	public function fetch();

	public function setTotal($total);

	public function getTotal();

	public function isLoaded();

	public function getPager($adapterName = null);

	public function renderPager($adapterName = null);
	
	public function render($adapterName = null);
}