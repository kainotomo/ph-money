<?php

/* 
 * Copyright (C) 2018 KAINOTOMO PH LTD <info@kainotomo.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('ContactHelper', JPATH_ADMINISTRATOR . '/components/com_contact/helpers/contact.php');

/**
 * Contact HTML helper class.
 *
 
 */
abstract class JHtmlSplit
{

	/**
	 * Show the reconciled/not-reconciled icon.
	 *
	 * @param   integer  $value      The reconciled value.
	 * @param   integer  $i          Id of the item.
	 * @param   boolean  $canChange  Whether the value can be changed or not.
	 *
	 * @return  string	The anchor tag to toggle reconciled/unreconciled splits.
	 *
	 */
	public static function reconciled($value = 0, $i, $canChange = true)
	{

		// Array of image, task, title, action
		$states = array(
			0 => array('ban text-danger', 'splits.reconcile', 'COM_PHOMONEY_UNRECONCILED', 'COM_PHMONEY_TOGGLE'),
			1 => array('check-square text-success', 'splits.unreconcile', 'COM_PHOMONEY_RECONCILED', 'COM_PHMONEY_TOGGLE'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
				. '"><span class="fa fa-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="tbody-icon hasTooltip disabled' . ($value == 1 ? ' active' : '')
				. '" title="' . JHtml::_('tooltipText', $state[2]) . '"><span class="fa fa-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}