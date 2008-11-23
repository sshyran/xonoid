<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_DataSource_Interface
 * 
 * It class to provide a DataSource Interface Implementation
 * 
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_DataSource
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


interface Core_DataGrid_DataSource_Interface
{
	/**
	 * Fetching method prototype
	 *
	 * When overloaded this method must return a 2D array of records
	 * on success or a PEAR_Error object on failure.
	 *
	 * @abstract
	 * @param   integer $offset     Limit offset (starting from 0)
	 * @param   integer $len        Limit length
	 * @return  object              PEAR_Error with message
	 *                              "No data source driver loaded"
	 * @access  public
	 */
	public function fetch($offset = 0, $len = null, $toArray = false);

	/**
	 * Counting method prototype
	 *
	 * Note: must be called before fetch()
	 *
	 * When overloaded, this method must return the total number or records
	 * or a PEAR_Error object on failure
	 *
	 * @abstract
	 * @return  object              PEAR_Error with message
	 *                              "No data source driver loaded"
	 * @access  public
	 */
	public function count();

	/**
	 * Sorting method prototype
	 *
	 * When overloaded this method must return true on success or a PEAR_Error
	 * object on failure.
	 *
	 * Note: must be called before fetch()
	 *
	 * @abstract
	 * @param   string  $sortSpec   If the driver supports the "multiSort"
	 *                              feature this can be either a single field
	 *                              (string), or a sort specification array of
	 *                              the form: array(field => direction, ...)
	 *                              If "multiSort" is not supported, then this
	 *                              can only be a string.
	 * @param   string  $sortDir    Sort direction: 'ASC' or 'DESC'
	 * @return  object              PEAR_Error with message
	 *                              "No data source driver loaded"
	 * @access  public
	 */
	public function sort($sortSpec, $sortDir = null);
	
	public function getColumns();
}