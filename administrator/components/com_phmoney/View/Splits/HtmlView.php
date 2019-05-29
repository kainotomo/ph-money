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

namespace Joomla\Component\Phmoney\Administrator\View\Splits;

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
     * The submit button of batch process
     * @var string
     */
    protected $batch_submit;

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
         * The batch form
         * 
         * @var form 
         */
        protected $batchForm;

        /**
         * Display the view
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  mixed  A string if successful, otherwise an Error object.
         */
        public function display($tpl = null) {
                if ($this->getLayout() !== 'modal') {
                        PhmoneyHelper::addSubmenu('splits');
                }

                $this->items = $this->get('Items');
                $this->pagination = $this->get('Pagination');
                $this->state = $this->get('State');
                $this->authors = $this->get('Authors');
                $this->filterForm = $this->get('FilterForm');
                $this->batchForm = $this->get('BatchForm');
                $this->activeFilters = $this->get('ActiveFilters');

                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                        throw new \JViewGenericdataexception(implode("\n", $errors), 500);
                }

                // We don't need toolbar in the modal window.
                if ($this->getLayout() !== 'modal') {
                        $this->addNavbar();
                        $this->addSeachbar();
                        $this->addToolbar();
                        $this->addButtonbar();
                } else {
                        // In split associations modal we need to remove language filter if forcing a language.
                        // We also need to change the category filter to show show categories with All or the forced language.
                        if ($forcedLanguage = \JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
                                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                                $this->filterForm->setField($languageXml, 'filter', true);

                                // Also, unset the active language filter so the search tools is not open by default with this filter.
                                unset($this->activeFilters['language']);

                                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
                        }
                }

                return parent::display($tpl);
        }

        /**
         * Add searchbar markup
         */
        protected function addSeachbar() {
                $data_sidebar = $this->filterForm;

                // Create a layout object and ask it to render the sidebar
                $layout = new FileLayout('searchbar', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/searchbars');

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
                $active_portfolio = $this->state->get('filter.portfolio');
                $portfolios = PhmoneyHelper::getPortfolios();
                if (!empty($portfolios)) {
                        $data['account_name'] = $portfolios[$active_portfolio]->text;
                }
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
                        $html[] = $standarButtonLayout->render(Array('task' => 'transaction.add', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => false));
                }
                
                if ($canDo->get('core.edit')) {
                              $html[] = $standarButtonLayout->render(Array('task' => 'transaction.edit', 'class' => 'btn-outline-primary', 'alt' => 'JTOOLBAR_EDIT', 'icon' => 'fa-pencil', 'list' => true, 'group' => false));
                        }

                if ($canDo->get('core.edit.state')) {
                        
                        $toolbarButtons = [];
                        $toolbarButtons[] = Array('alt' => 'COM_PHMONEY_ASSIGN_SPLIT_TYPE', 'class' => 'btn-outline-secondary');
                        $toolbarButtons[] = Array('task' => 'splits.nan', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_NAN_LINES', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.buy', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_BUY', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.sell', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_SELL', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.dividend', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_DIVIDEND', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.fee', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_FEE', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.price', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_PRICE', 'icon' => 'fa-', 'list' => true, 'group' => true);
                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-primary'
                        );
                        
                        $toolbarButtons = [];
                        $toolbarButtons[] = Array('task' => 'splits.publish', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_PUBLISH', 'icon' => 'fa-check', 'list' => true, 'group' => false);
                        $toolbarButtons[] = Array('task' => 'splits.unpublish', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_UNPUBLISH', 'icon' => 'fa-times', 'list' => true, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'splits.archive', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_ARCHIVE', 'icon' => 'fa-archive', 'list' => true, 'group' => true);
                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-primary'
                        );
                        
                        $toolbarButtons = [];
                        $toolbarButtons[] = Array('task' => 'splits.reconcile', 'class' => 'btn-outline-success', 'alt' => 'COM_PHMONEY_RECONCILE', 'icon' => 'fa-check-square', 'list' => true, 'group' => false);
                        $toolbarButtons[] = Array('task' => 'splits.unreconcile', 'class' => 'btn-outline-danger', 'alt' => 'COM_PHMONEY_UNRRECONCILE', 'icon' => 'fa-ban', 'list' => true, 'group' => true);
                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-primary'
                        );
                }

                if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
                        $html[] = $standarButtonLayout->render(Array('task' => 'splits.delete', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_EMPTY_TRASH', 'icon' => 'fa-trash', 'list' => true, 'group' => false, 'confirm' => true));
                } elseif ($canDo->get('core.edit.state')) {
                        $html[] = $standarButtonLayout->render(Array('task' => 'splits.trash', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_TRASH', 'icon' => 'fa-trash', 'list' => true, 'group' => false, 'confirm' => false));
                }
                
                $html[] = $standarButtonLayout->render(Array('task' => 'splits.print_view', 'class' => 'btn-outline-secondary', 'alt' => 'JGLOBAL_SHOW_PRINT_ICON_LABEL', 'icon' => 'fa-print', 'list' => false, 'group' => false));

                if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0) {
                        // Instantiate a new \JLayoutFile instance and render the batch button
                        $layout = new FileLayout('joomla.toolbar.batch');
                        $html[] = $layout->render(array('title' => Text::_('JTOOLBAR_BATCH')));
                }
                
                $this->batch_submit = $standarButtonLayout->render(Array('task' => 'split.batch', 'class' => 'btn-success', 'alt' => 'JGLOBAL_BATCH_PROCESS', 'icon' => 'fa-ok', 'list' => true, 'group' => false, 'confirm' => true));
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
