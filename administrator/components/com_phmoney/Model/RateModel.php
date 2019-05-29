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

namespace Joomla\Component\Phmoney\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

/**
 * Item Model for an Rate.
 *
 */
class RateModel extends AdminModel {

        /**
         * The prefix to use with controller messages.
         *
         * @var    string
         */
        protected $text_prefix = 'COM_PHMONEY';

        /**
         * The type alias for this phmoney type (for example, 'com_phmoney.rate').
         *
         * @var    string
         */
        public $typeAlias = 'com_phmoney.rate';

        /**
         * The context used for the associations table
         *
         * @var    string
         */
        protected $associationsContext = 'com_phmoney.rate';

        /**
         * Method to get the record form.
         *
         * @param   array    $data      Data for the form.
         * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
         *
         * @return  \JForm|boolean  A \JForm object on success, false on failure
         *
         */
        public function getForm($data = array(), $loadData = true) {
                // Get the form.
                $form = $this->loadForm('com_phmoney.rate', 'rate', array('control' => 'jform', 'load_data' => $loadData));

                if (empty($form)) {
                        return false;
                }

                $jinput = \JFactory::getApplication()->input;

                /*
                 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
                 * The back end uses id so we use that the rest of the time and set it to 0 by default.
                 */
                $id = $jinput->get('a_id', $jinput->get('id', 0));

                // Determine correct permissions to check.
                if ($this->getState('rate.id')) {
                        $id = $this->getState('rate.id');
                } else {
                        // New record. Can only create in selected categories.
                        $portfolio_id = Factory::getApplication()->getUserStateFromRequest('com_phmoney.accounts.filter.portfolio', 'filter_portfolio');
                        $form->setFieldAttribute('portfolio_id', 'default', $portfolio_id);
                }

                return $form;
        }

        /**
         * Method to get the data that should be injected in the form.
         *
         * @return  mixed  The data for the form.
         *
         */
        protected function loadFormData() {
                // Check the session for previously entered form data.
                $app = \JFactory::getApplication();
                $data = $app->getUserState('com_phmoney.edit.rate.data', array());

                if (empty($data)) {
                        $data = $this->getItem();

                        // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Rate Manager: Rates
                        if ($this->getState('rate.id') == 0) {
                                $filters = (array) $app->getUserState('com_phmoney.rates.filter');
                                $portfolio_id = (int) $app->getUserStateFromRequest('com_phmoney.accounts.filter.portfolio', 'filter_portfolio');
                                $data->set('portfolio_id', $portfolio_id);
                        }
                }

                // If there are params fieldsets in the form it will fail with a registry object
                if (isset($data->params) && $data->params instanceof Registry) {
                        $data->params = $data->params->toArray();
                }

                $this->preprocessData('com_phmoney.rate', $data);

                return $data;
        }

        public function validate($form, $data, $group = null) {
                $result = parent::validate($form, $data, $group);

                if ($result) {
                        $db = $this->getDbo();
                        $query = $db->getQuery(TRUE);
                        $query->select('a.currency_id')
                                ->from('#__phmoney_portfolios as a')
                                ->where('a.id = ' . (int) $data['portfolio_id']);
                        $query->select('cur.id')
                                ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id=a.currency_id');
                        $db->setQuery($query);
                        $porfolio_currency = $db->loadResult();

                        if ($porfolio_currency == $data['currency_id']) {
                                $result = false;
                                $this->setError(\Joomla\CMS\Language\Text::_('COM_PHMONEY_SAME_CURRENCY_NOT_ALLOWED'));
                        }
                }

                return $result;
        }
}
