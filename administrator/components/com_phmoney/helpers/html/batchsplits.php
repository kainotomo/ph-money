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

/**
 * Contact HTML helper class.
 *
 
 */
abstract class JHtmlBatchSplits
{
	/**
	 * Display a batch widget for the access level selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.access'); use JLayoutHelper::render('joomla.html.batch.access', array());
	 */
	public static function access()
	{
		JLog::add('The use of JHtml::_("batch.access") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.access', array());
                
	}

	/**
	 * Displays a batch widget for moving or copying items.
	 *
	 * @param   string  $extension  The extension that owns the category.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.item'); use JLayoutHelper::render('joomla.html.batch.item', array('extension' => 'com_XXX'));
	 */
	public static function item($extension)
	{
		$displayData = array('extension' => $extension);

		JLog::add('The use of JHtml::_("batch.item") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.item', $displayData);
	}

	/**
	 * Display a batch widget for the language selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.language'); use JLayoutHelper::render('joomla.html.batch.language', array());
	 */
	public static function language()
	{
		JLog::add('The use of JHtml::_("batch.language") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.language', array());
	}

	/**
	 * Display a batch widget for the user selector.
	 *
	 * @param   boolean  $noUser  Choose to display a "no user" option
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.user'); use JLayoutHelper::render('joomla.html.batch.user', array());
	 */
	public static function user($noUser = true)
	{
		$displayData = array('noUser' => $noUser);

		JLog::add('The use of JHtml::_("batch.user") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.user', $displayData);
	}

	/**
	 * Display a batch widget for the tag selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.tag'); use JLayoutHelper::render('joomla.html.batch.tag', array());
	 */
	public static function tag()
	{
		JLog::add('The use of JHtml::_("batch.tag") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');
                echo "tag";
		return JLayoutHelper::render('joomla.html.batch.tag', array());
	}
}
