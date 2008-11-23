<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Render
 *
 * It class to provide a DataGrid Render Object
 * for default the render is Zend View
 *
 * @category   Core
 * @package    Core_DataGrid
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

final class Core_DataGrid_Render
{
	const DEFAULT_ADAPTER = 'ZendView';

	public static function factory(Core_DataGrid_Interface $grid, $adapterName = null)
	{
		if (null === $adapterName){
			$adapterName = self::DEFAULT_ADAPTER;
		}

		if (!is_string($adapterName) or !strlen($adapterName)) {
			throw new Exception('Reder Datagrid: Adapter name must be specified in a string.');
		}

		$adapterName = 'Core_DataGrid_Render_' . $adapterName;

		Zend_Loader::loadClass($adapterName);

		return new $adapterName($grid);
	}
}
