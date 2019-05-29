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

namespace Joomla\Component\Phmoney\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

/**
 * Component Controller
 *
 */
class DisplayController extends BaseController {

        /**
         * The default view.
         *
         * @var    string
         */
        protected $default_view = 'cpanel';

        /**
         * Method to display a view.
         *
         * @param   boolean  $cachable   If true, the view output will be cached
         * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
         *
         * @return  BaseController|bool  This object to support chaining.
         *
         */
        public function display($cachable = true, $urlparams = array()) {

                // Set the default view name and format from the Request.                
                $option = $this->input->get('option', 'com_phcloud');
                $view = $this->input->get('view', 'cpanel');                
                $layout = $this->input->get('layout', 'default', 'string');
                $this->input->set('view', $view);
                $id = $this->input->getInt('id');

                // Receive & set filters and safeurloparams
                $safeurlparams = Array();
                $safeurlparams = array_merge($urlparams, array('id' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
                        'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'lang' => 'CMD'));
                $filters = $this->app->getUserStateFromRequest($option . '.' . $view . '.filter', 'filter', array(), 'array');
                if ($filters) {
                        foreach ($filters as $name => $value) {
                                if (is_array($value)) {
                                        $value = json_encode($value);
                                }
                                $this->input->set('filter_' . $name, $value);
                                $safeurlparams = array_merge($safeurlparams, Array('filter_' . $name => $value));
                        }
                }

                if ($this->input->getMethod() === 'POST') {
                        $cachable = false;
                }

                // Check for edit form.
                if ($view == 'split' && $layout == 'edit' && !$this->checkEditId('com_phmoney.edit.split', $id)) {
                        // Somehow the person just went to the form - we don't allow that.
                        $this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
                        $this->setRedirect(\JRoute::_('index.php?option=com_phmoney&view=splits', false));

                        return false;
                }

                if ($layout === 'edit') {
                        $safeurlparams = array_merge($safeurlparams, array('type' => 'STRING'));
                        $cachable = FALSE;
                }

                //set default portfolio
                $portfolio = $this->app->getUserState('com_phmoney.portfolio');
                if (is_null($portfolio)) {
                        $portfolio = PhmoneyHelper::getDefaultPortfolio();
                        $this->app->setUserState('com_phmoney.portfolio', $portfolio);
                }

                parent::display($cachable, $safeurlparams);
        }

}
