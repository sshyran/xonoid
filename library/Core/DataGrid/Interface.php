<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Interface
 *
 * It class to provide a DataGrid Interface
 *
 * @category   Core
 * @package    Core_DataGrid
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

interface Core_DataGrid_Interface
{
	public function setDefaultSort($sortSpec);

	public function getDefaultSort();

	public function setDefaultDir($dir);

	public function getDefaultDir();

	public function getOrder();

	public function getDirection();

	public function addColumn($columnId, $column);

	public function getLastColumnId();

	public function getColumnCount();

	public function getColumn($columnId);
}
