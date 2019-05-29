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

namespace Joomla\Component\Phmoney\Administrator\View\Importscsv;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

/**
 * View class for a list of splits.
 *
 
 */
class HtmlView extends BaseHtmlView {

        /**
         * The buttonbar markup
         * @var string
         */
        protected $buttonbar;

        /**
         * The navbar markup
         *
         * @var string
         */
        protected $navbar;

        /**
         * The searcbar markup
         *
         * @var  string
         */
        protected $searcbar;

        /**
         * Display the view
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  mixed  A string if successful, otherwise an Error object.
         */
        public function display($tpl = null) {
                if ($this->getLayout() !== 'modal') {
                        PhmoneyHelper::addSubmenu('imports');
                }

                $this->items = $this->get('Items');
                $this->pagination = $this->get('Pagination');
                $this->state = $this->get('State');
                $this->filterForm = $this->get('FilterForm');
                $this->activeFilters = $this->get('ActiveFilters');

                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                        throw new \JViewGenericdataexception(implode("\n", $errors), 500);
                }

                $this->addNavbar();
                $this->addSeachbar();
                $this->addToolbar();
                $this->addButtonbar();

                $document = Factory::getDocument();
                $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'media/com_phmoney/js/imports.js');

                return parent::display($tpl);
        }

        /**
         * Add searchbar markup
         */
        protected function addSeachbar() {
                $data_sidebar = $this->filterForm;

                // Create a layout object and ask it to render the sidebar
                $layout = new FileLayout('searchbar_imports', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/searchbars');

                $this->searchbar = $layout->render($data_sidebar);
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

        /**
         * Add buttonbar markup
         */
        protected function addButtonbar() {

                $canDo = PhmoneyHelper::getActions('com_phmoney', 'category', $this->state->get('filter.category_id'));
                $user = Factory::getUser();

                $html = array();
                $standarButtonLayout = new FileLayout('toolbar.buttons.standardbutton', null, Array('client' => 'admin'));

                if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0) {
                        $html[] = $standarButtonLayout->render(Array('task' => 'imports.import_csv_columns_splits', 'class' => 'btn-outline-success', 'alt' => 'JNEXT', 'icon' => 'fa-arrow-right', 'list' => false, 'group' => false, 'confirm' => true));
                }

                $this->buttonbar = implode('', $html);
        }

        /**
         * Returns an array of fields the table can be sorted by
         *
         * @return  array  Array containing the field name to sort by as the key and display text as value
         *
         
         */
        protected function getSortFields() {
                return array(
                        'a.ordering' => \JText::_('JGRID_HEADING_ORDERING'),
                        'a.state' => \JText::_('JSTATUS'),
                        'a.title' => \JText::_('JGLOBAL_TITLE'),
                        'category_title' => \JText::_('JCATEGORY'),
                        'access_level' => \JText::_('JGRID_HEADING_ACCESS'),
                        'a.created_by' => \JText::_('JAUTHOR'),
                        'language' => \JText::_('JGRID_HEADING_LANGUAGE'),
                        'a.created' => \JText::_('JDATE'),
                        'a.id' => \JText::_('JGRID_HEADING_ID'),
                        'a.featured' => \JText::_('JFEATURED')
                );
        }

}
