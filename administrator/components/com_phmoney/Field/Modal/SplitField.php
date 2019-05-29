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

namespace Joomla\Component\Phmoney\Administrator\Field\Modal;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

/**
 * Supports a modal split picker.
 *
 */
class SplitField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Modal_Split';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @sinc   1.6
	 */
	protected function getInput()
	{
		$allowNew    = ((string) $this->element['new'] == 'true');
		$allowEdit   = ((string) $this->element['edit'] == 'true');
		$allowClear  = ((string) $this->element['clear'] != 'false');
		$allowSelect = ((string) $this->element['select'] != 'false');

		// Load language
		\JFactory::getLanguage()->load('com_phmoney', JPATH_ADMINISTRATOR);

		// The active split id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Split_' . $this->id;

		// Add the modal field script to the document head.
		\JHtml::_('jquery.framework');
		\JHtml::_('script', 'system/fields/modal-fields.min.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				\JFactory::getDocument()->addScriptDeclaration("
				function jSelectSplit_" . $this->id . "(id, title, catid, object, url, language) {
					window.processModalSelect('Split', '" . $this->id . "', id, title, catid, object, url, language);
				}
				");

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkSplits = 'index.php?option=com_phmoney&amp;view=splits&amp;layout=modal&amp;tmpl=component&amp;' . \JSession::getFormToken() . '=1';
		$linkSplit  = 'index.php?option=com_phmoney&amp;view=split&amp;layout=modal&amp;tmpl=component&amp;' . \JSession::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkSplits .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkSplit  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle    = \JText::_('COM_CONTENT_CHANGE_ARTICLE') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle    = \JText::_('COM_CONTENT_CHANGE_ARTICLE');
		}

		$urlSelect = $linkSplits . '&amp;function=jSelectSplit_' . $this->id;
		$urlEdit   = $linkSplit . '&amp;task=split.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkSplit . '&amp;task=split.add';

		if ($value)
		{
			$db    = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__phmoney_splits'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		$title = empty($title) ? \JText::_('COM_CONTENT_SELECT_AN_ARTICLE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current split display field.
		$html  = '';
		if ($allowSelect || $allowNew || $allowEdit || $allowClear)
		{
			$html .= '<span class="input-group">';
		}

		$html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35">';

		if ($allowSelect || $allowNew || $allowEdit || $allowClear)
		{
			$html .= '<span class="input-group-btn">';
		}

		// Select split button
		if ($allowSelect)
		{
			$html .= '<a'
				. ' class="btn btn-primary hasTooltip' . ($value ? ' sr-only' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalSelect' . $modalId . '"'
				. ' title="' . \JHtml::tooltipText('COM_CONTENT_CHANGE_ARTICLE') . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . \JText::_('JSELECT')
				. '</a>';
		}

		// New split button
		if ($allowNew)
		{
			$html .= '<a'
				. ' class="btn btn-secondary hasTooltip' . ($value ? ' sr-only' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalNew' . $modalId . '"'
				. ' title="' . \JHtml::tooltipText('COM_CONTENT_NEW_ARTICLE') . '">'
				. '<span class="icon-new" aria-hidden="true"></span> ' . \JText::_('JACTION_CREATE')
				. '</a>';
		}

		// Edit split button
		if ($allowEdit)
		{
			$html .= '<a'
				. ' class="btn btn-secondary hasTooltip' . ($value ? '' : ' sr-only') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalEdit' . $modalId . '"'
				. ' title="' . \JHtml::tooltipText('COM_CONTENT_EDIT_ARTICLE') . '">'
				. '<span class="icon-edit" aria-hidden="true"></span> ' . \JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear split button
		if ($allowClear)
		{
			$html .= '<a'
				. ' class="btn btn-secondary' . ($value ? '' : ' sr-only') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' href="#"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove" aria-hidden="true"></span>' . \JText::_('JCLEAR')
				. '</a>';
		}

		if ($allowSelect || $allowNew || $allowEdit || $allowClear)
		{
			$html .= '</span></span>';
		}

		// Select split modal
		if ($allowSelect)
		{
			$html .= \JHtml::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<a role="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
										. \JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
				)
			);
		}

		// New split modal
		if ($allowNew)
		{
			$html .= \JHtml::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => \JText::_('COM_CONTENT_NEW_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<a role="button" class="btn btn-secondary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'split\', \'cancel\', \'item-form\'); return false;">'
							. \JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'split\', \'save\', \'item-form\'); return false;">'
							. \JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'split\', \'apply\', \'item-form\'); return false;">'
							. \JText::_('JAPPLY') . '</a>',
				)
			);
		}

		// Edit split modal
		if ($allowEdit)
		{
			$html .= \JHtml::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => \JText::_('COM_CONTENT_EDIT_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<a role="button" class="btn btn-secondary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'split\', \'cancel\', \'item-form\'); return false;">'
							. \JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'split\', \'save\', \'item-form\'); return false;">'
							. \JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'split\', \'apply\', \'item-form\'); return false;">'
							. \JText::_('JAPPLY') . '</a>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(\JText::_('COM_CONTENT_SELECT_AN_ARTICLE', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
