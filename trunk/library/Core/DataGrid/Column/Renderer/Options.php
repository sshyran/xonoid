<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Column_Renderer_Options
 *
 * It class to provide a Grid column widget for rendering grid cells that contains mapped values
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Column_Renderer
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


class Core_DataGrid_Column_Renderer_Options extends Core_DataGrid_Column_Renderer_Text
{
    public function render($row)
    {
        $options = $this->getColumn()->getOptions();
        if (!empty($options) && is_array($options)) {
            $value = $row[$this->getColumn()->getIndex()];
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    $res[] = isset($options[$item]) ? $options[$item] : $item;
                }
                return implode(', ', $res);
            }
            elseif (isset($options[$value])) {
                return $options[$value];
            }
            return '';
        }
    }

}
