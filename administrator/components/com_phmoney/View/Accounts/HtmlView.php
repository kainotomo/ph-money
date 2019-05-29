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

namespace Joomla\Component\Phmoney\Administrator\View\Accounts;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

/**
 * Description of HtmlView
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class HtmlView extends BaseHtmlView {

    /**
     * The submit button of batch process
     * @var string
     */
    protected $batch_submit;

    /**
     * The batch form
     * 
     * @var form 
     */
    protected $batchForm;

    /**
     *
     * @var stdclass Active Portfolio
     */
    protected $portfolio;

    /**
     * The summary balance items
     * 
     * @var array
     */
    protected $items2;

    public function display($tpl = null) {

        if ($this->getLayout() !== 'modal') {
            PhmoneyHelper::addSubmenu('accounts');
        }

        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->assoc = $this->get('Assoc');
        $this->filterForm = $this->get('FilterForm');
        $this->batchForm = $this->get('BatchForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $active_portfolio = $this->state->get('filter.portfolio');
        $portfolios = PhmoneyHelper::getPortfolios();
        if (!empty($portfolios)) {
            $this->portfolio = $portfolios[$active_portfolio];
        }
        $report_type = $this->escape($this->state->get('filter.report_type', 'balances'));
        if ($report_type === 'type_balance' || $report_type === 'type_pie_chart') {
            $this->items2 = $this->get('AccountTypesBalance');
        }
        if ($report_type === 'tags_balance' || $report_type === 'tags_pie_chart') {
            $this->items2 = $this->get('TagsBalance');
        }
        if ($report_type === 'accounts_bar_chart') {
            $this->items2 = $this->get('BarChart');
        }
        if ($report_type === 'accounts_bar_chart_cumulative') {
            $this->items2 = $this->get('BarChartCumulative');
        }
        if ($report_type === 'tags_bar_chart') {
            $this->items2 = $this->get('TagsBarChart');
        }
        if ($report_type === 'tags_bar_chart_cumulative') {
            $this->items2 = $this->get('TagsBarChartCumulative');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \JViewGenericdataexception(implode("\n", $errors), 500);
        }

        // Preprocess the list of items to find ordering divisions.
        foreach ($this->items as &$item) {
            $this->ordering[$item->parent_id][] = $item->id;
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addNavbar();
            $this->addSeachbar();
            $this->addToolbar();
            $this->addButtonbar();

            // We do not need to filter by language when multilingual is disabled
            if (!\JLanguageMultilang::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } else {
            // In article associations modal we need to remove language filter if forcing a language.
            if ($forcedLanguage = \JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);
            }
        }

        if ($report_type === 'type_pie_chart' || $report_type === 'tags_pie_chart' || $report_type === 'accounts_pie_chart' || $report_type === 'assets_pie_chart' || $report_type === 'assets_bar_chart' || $report_type === 'assets_bar_chart_cumulative') {
            $document = Factory::getDocument();
            $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'administrator/components/com_phmoney/libraries/node_modules/chart.js/dist/Chart.min.js');
            $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'media/com_phmoney/js/pie_charts.js');
        }
        if ($report_type === 'accounts_bar_chart' || $report_type === 'accounts_bar_chart_cumulative' || $report_type === 'tags_bar_chart' || $report_type === 'tags_bar_chart_cumulative') {
            $document = Factory::getDocument();
            $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'administrator/components/com_phmoney/libraries/node_modules/chart.js/dist/Chart.min.js');
            $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'media/com_phmoney/js/bar_charts.js');
        }

        parent::display($tpl);
    }

    /**
     * Add searchbar markup
     */
    protected function addSeachbar() {
        $data_sidebar = $this->filterForm;

        // Create a layout object and ask it to render the sidebar
        $layout = new FileLayout('searchbar_accounts', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/searchbars');

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
        if (!empty($this->portfolio)) {
            $data['account_name'] = $this->portfolio->text;
        }
        $this->navbar = $layout->render($data);
    }

    /**
     * Add buttonbar markup
     */
    protected function addButtonbar() {

        $canDo = PhmoneyHelper::getActions('com_phmoney');
        $user = Factory::getUser();
        $report_type = $this->escape($this->state->get('filter.report_type', 'balances'));

        $html = array();
        $standarButtonLayout = new FileLayout('toolbar.buttons.standardbutton', null, Array('client' => 'admin'));

        if ($report_type == 'tree' || $report_type == 'balances') {
            if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0) {
                $html[] = $standarButtonLayout->render(Array('task' => 'account.add', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => false));
            }

            if ($canDo->get('core.edit')) {
                $html[] = $standarButtonLayout->render(Array('task' => 'account.edit', 'class' => 'btn-outline-primary', 'alt' => 'JTOOLBAR_EDIT', 'icon' => 'fa-pencil', 'list' => true, 'group' => false));
            }

            if ($canDo->get('core.edit.state')) {
                $toolbarButtons = [];
                $toolbarButtons[] = Array('task' => 'accounts.publish', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_PUBLISH', 'icon' => 'fa-check', 'list' => true, 'group' => false);
                $toolbarButtons[] = Array('task' => 'accounts.unpublish', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_UNPUBLISH', 'icon' => 'fa-times', 'list' => true, 'group' => true);
                $toolbarButtons[] = Array('task' => 'accounts.archive', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_ARCHIVE', 'icon' => 'fa-archive', 'list' => true, 'group' => true);

                if ($canDo->get('core.admin')) {
                    $toolbarButtons[] = Array('task' => 'accounts.checkin', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_CHECKIN', 'icon' => 'fa-unlock', 'list' => true, 'group' => true);
                }

                $html[] = PhmoneyHelper::saveButtonGroup(
                                $toolbarButtons, 'btn-primary'
                );
            }

            if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
                $html[] = $standarButtonLayout->render(Array('task' => 'accounts.delete', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_EMPTY_TRASH', 'icon' => 'fa-trash', 'list' => true, 'group' => false, 'confirm' => true));
            } elseif ($canDo->get('core.edit.state')) {
                $html[] = $standarButtonLayout->render(Array('task' => 'accounts.trash', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_TRASH', 'icon' => 'fa-trash', 'list' => true, 'group' => false, 'confirm' => false));
                }
            }

        $html[] = $standarButtonLayout->render(Array('task' => 'accounts.print_view', 'class' => 'btn-outline-secondary', 'alt' => 'JGLOBAL_SHOW_PRINT_ICON_LABEL', 'icon' => 'fa-print', 'list' => false, 'group' => false));

        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0) {
            // Instantiate a new \JLayoutFile instance and render the batch button
            $layout = new FileLayout('joomla.toolbar.batch');
            $html[] = $layout->render(array('title' => Text::_('JTOOLBAR_BATCH')));
        }

        $this->batch_submit = $standarButtonLayout->render(Array('task' => 'account.batch', 'class' => 'btn-success', 'alt' => 'JGLOBAL_BATCH_PROCESS', 'icon' => 'fa-ok', 'list' => true, 'group' => false, 'confirm' => true));
        $this->buttonbar = implode('', $html);
    }

}
