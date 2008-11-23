<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Interface
 *
 * It class to provide a grid item renderer interface
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


interface Core_DataGrid_Column_Renderer_Interface
{
    public function setColumn($column);
    
    public function getColumn();

    /**
     * Renders grid column
     *
     * @param Core_Object $row
     */
    public function render($row);
}