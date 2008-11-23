<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Link
 *
 * It class to provide a grid item renderer link
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Link extends Core_DataGrid_Column_Renderer_Text
{
	/**
	 * Format variables pattern
	 *
	 * @var string
	 */

	public function render($row)
	{
		$links = $this->getColumn()->getLinks();

		if (empty($links)) {
			return parent::render($row);
		}

		$text = parent::render($row);

		$this->getColumn()->setFormat($links);
		$action = parent::render($row);
		$this->getColumn()->setFormat(null);

		return '<a href="' . $action . '" title="' . $text . '">' . $text . '</a>';
	}
}