<?php
/**
 * Zsamer Framework
 *
 * Core_DataGrid_Pager_Abstract_Standard
 *
 * It class to provide a Pager Standard Object Implementation
 *
 * @category   Core
 * @package    Core_DataGrid
 * @subpackage Core_DataGrid_Pager_Abstract
 * @copyright  Copyright (c) 2008 Bolsa de Ideas. Consultor en TIC (http://www.bolsadeideas.cl)
 * @author Andres Guzman F. <aguzman@bolsadeideas.cl>
 */


/**
 * @see Core_DataGrid_Pager_Abstract
 */
require_once 'Core/DataGrid/Pager/Abstract.php';

class Core_DataGrid_Pager_Abstract_Standard extends Core_DataGrid_Pager_Abstract
{
	/**
	 * Handles building the body of the table
	 *
	 * @access  public
	 * @return  void
	 */
	public function build($addPrevNextText = true)
	{
		if ($this->getNumberPages() == 1 || !$this->getNumberRecords())
		{
			return $this;
		}
		$this->setOnPage();

		$output = '';
		$output .= ($this->getOnPage() == 1) ? '<strong>1</strong>' : '<span class="pagelink"><a href="' . $this->getLink(0) . '">1</a></span>';

		if ($this->getNumberPages() > 5)
		{
			$start_cnt = min(max(1, $this->getOnPage() - 4), $this->getNumberPages() - 5);
			$end_cnt = max(min($this->getNumberPages(), $this->getOnPage() + 4), 6);

			$output .= ($start_cnt > 1) ? ' ... ' : self::$_seperator;

			for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
			{
				$output .= ($i == $this->getOnPage()) ? '<span class="pagecurrent"><strong>' . $i . '</strong></span>' : '<span class="pagelink"><a href="' . $this->getLink((($i - 1) * $this->getLimit())) . '">' . $i . '</a></span>';
				if ($i < $end_cnt - 1)
				{
					$output .= self::$_seperator;
				}
			}

			$output .= ($end_cnt < $this->getNumberPages()) ? ' ... ' : self::$_seperator;
		}
		else
		{
			$output .= self::$_seperator;

			for ($i = 2; $i < $this->getNumberPages(); $i++)
			{
				$output .= ($i == $this->getOnPage()) ? '<span class="pagecurrent"><strong>' . $i . '</strong></span>' : '<span class="pagelink"><a href="' . $this->getLink((($i - 1) * $this->getLimit())) . '">' . $i . '</a></span>';
				if ($i < $this->getNumberPages())
				{
					$output .= self::$_seperator;
				}
			}
		}

		$output .= ($this->getOnPage() == $this->getNumberPages()) ? '<span class="pagecurrent"><strong>' . $this->getNumberPages() . '</strong></span>' : '<span class="pagelink"><a href="' . $this->getLink((($this->getNumberPages() - 1) * $this->getLimit())) . '">' . $this->getNumberPages() . '</a></span>';

		if ($addPrevNextText)
		{
			if ($this->getOnPage() != 1)
			{
				$output = '<span class="pagelinklast"><a href="' . $this->getLink((($this->getOnPage() - 2) * $this->getLimit())) . '">' . $this->getPrevious() . '</a></span>&nbsp;&nbsp;' . $output;
			}

			if ($this->getOnPage() != $this->getNumberPages())
			{
				$output .= '&nbsp;&nbsp;<span class="pagelinklast"><a href="' . $this->getLink(($this->getOnPage() * $this->getLimit())) . '">' . $this->getNext() . '</a></span>';
			}
		}

		$output = '<div id="' . $this->getLinksId() . '">Ir a página:' . ' ' . $output . '</div>';

		$this->setOutput($output);
		return $this;
	}
}
