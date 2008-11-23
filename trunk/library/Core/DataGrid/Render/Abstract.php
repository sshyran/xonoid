<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Render_Abstract
 *
 * It class to provide a Abstract Render Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Render
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

class Core_DataGrid_Render_Abstract
{
	protected $_grid = null;

	public function __construct(Core_DataGrid_Interface $grid)
	{
		$this->setGrid($grid);
		$this->init();
	}

	public function init()
	{}

	public function setGrid(Core_DataGrid_Interface $grid)
	{
		$this->_grid = $grid;
		return $this;
	}

	public function getGrid()
	{
		return $this->_grid;
	}
}