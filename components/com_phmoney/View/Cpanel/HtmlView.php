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

namespace Joomla\Component\Phmoney\Site\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\Component\Phmoney\Administrator\View\Cpanel\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

/**
 * HTML View class for the Phcloud component
 *
 */
class HtmlView extends BaseHtmlView
{
        public function display($tpl = null) {
                Factory::getLanguage()->load('com_phmoney', JPATH_ADMINISTRATOR);
                $this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/cpanel');
                return parent::display($tpl);
        }
}