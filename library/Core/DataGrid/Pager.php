<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Pager
 *
 * It class to provide a DataGrid Pager Object
 * for default the Pager is Standard class
 *
 * @category   Core
 * @package    Core_DataGrid
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */

final class Core_DataGrid_Pager
{
	const DEFAULT_ADAPTER = 'Standard';

	public static function factory($adapterName = null, $_page, $_pageLimit, $_recordsNum)
	{
		if (null === $adapterName){
			$adapterName = self::DEFAULT_ADAPTER;
		}

		if (!is_string($adapterName) or !strlen($adapterName)) {
			throw new Exception('Adapter name must be specified in a string.');
		}

		$adapterName = 'Core_DataGrid_Pager_Abstract_' . $adapterName;

		Zend_Loader::loadClass($adapterName);

		return new $adapterName($_page, $_pageLimit, $_recordsNum);
	}
}
