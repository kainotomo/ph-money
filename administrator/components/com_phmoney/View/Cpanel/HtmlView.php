<?php

/*
 * Copyright (C) 2017 KAINOTOMO PH LTD <info@kainotomo.com>
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

namespace Joomla\Component\Phmoney\Administrator\View\CPanel;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

/**
 * View class for a list of articles.
 *
 */
class HtmlView extends BaseHtmlView {

        /**
         * The navbar markup
         * 
         * @var string 
         */
        protected $navbar;
        
        /**
         * The component manifest
         * 
         * @var \Joomla\Registry\Registry
         */
        protected $manifest;

        /**
         * Display the view
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  mixed  A string if successful, otherwise an Error object.
         */
        public function display($tpl = null) {
                
                PhmoneyHelper::addSubmenu('cpanel');
                
                $this->manifest = $this->get('Manifest');
                
                $this->addNavbar();
                $this->addToolbar();

                return parent::display($tpl);
        }

        /**
         * Add the page title and toolbar.
         *
         * @return  void
         *
         */
        protected function addToolbar() {
                $user = Factory::getUser();

                ToolbarHelper::title(Text::_('COM_PHMONEY'), 'stack split');

                if ($user->authorise('core.admin', 'com_phmoney') || $user->authorise('core.options', 'com_phmoney')) {
                        ToolbarHelper::preferences('com_phmoney');
                }

                ToolbarHelper::help(NULL, FALSE, "https://www.kainotomo.com/products/ph-money/documentation");
        }
        
        /**
         * Add navbar markup
         */
        protected function addNavbar() {
                $layout = new FileLayout('navbar', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/navbars');

                $data = Array();
                $data['navbar'] = \JHtmlSidebar::getEntries();
                $data['account_name'] = Text::_('COM_PHMONEY');
                $this->navbar = $layout->render($data);
        }

}
