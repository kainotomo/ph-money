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

namespace Joomla\Component\Phmoney\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

/**
 * Description of AccountModel
 *
 */
class ImportModel extends AdminModel {

        /**
         * The type alias for this content type. Used for content version history.
         *
         * @var      string
         */
        public $typeAlias = null;

        public function __construct($config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null, \Joomla\CMS\Form\FormFactoryInterface $formFactory = null) {
                $this->typeAlias = 'com_phmoney.import';
                parent::__construct($config, $factory, $formFactory);
        }

        /**
         * Method to get the row form.
         *
         * @param   array    $data      Data for the form.
         * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
         *
         * @return  \JForm|boolean  A JForm object on success, false on failure
         *
         */
        public function getForm($data = array(), $loadData = true) {
                $jinput = Factory::getApplication()->input;

                // Get the form.
                $form = $this->loadForm('com_phmoney.import', 'import', array('control' => 'jform', 'load_data' => $loadData));

                if (empty($form)) {
                        return false;
                }

                $jinput = Factory::getApplication()->input;

                /*
                 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
                 * The back end uses id so we use that the rest of the time and set it to 0 by default.
                 */
                $id = $jinput->get('a_id', $jinput->get('id', 0));

                // Determine correct permissions to check.
                if (!$this->getState('portfolio.id')) {
                        // New record. Can only create in selected categories.
                        $portfolio_id = Factory::getApplication()->getUserStateFromRequest('com_phmoney.imports.filter.portfolio', 'filter_portfolio');
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
                $app = Factory::getApplication();
                $data = $app->getUserState('com_phmoney.edit.import.data', array());

                if (empty($data)) {
                        $data = $this->getItem();

                        // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Split Manager: Splits
                        if ($this->getState('account.id') == 0) {
                                $filters = (array) $app->getUserState('com_phmoney.imports.filter', array('portfolio' => PhmoneyHelper::getDefaultPortfolio()));
                                $data->set(
                                        'state', $app->input->getInt(
                                                'state', ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                                        )
                                );
                                //$data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                                $data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : Factory::getConfig()->get('access'))));
                                $portfolio_id = (int) $filters['portfolio'];
                                $data->set('portfolio_id', $portfolio_id);
                                $db = $this->getDbo();
                                $query = $db->getQuery(true);
                                $query->select('currency_id')
                                        ->from('#__phmoney_portfolios')
                                        ->where('id = ' . $portfolio_id);
                                $db->setQuery($query);
                                $currency_id = $db->loadResult();
                                $data->set('currency_id', $currency_id);

                                //convert date
                                $dateFormat = \DateTime::createFromFormat($filters['date_format'], $data->post_date);
                                if ($dateFormat) {
                                        $data->post_date = $dateFormat->format('Y-m-d H:i:s');
                                } else {
                                        $data->post_date = date('Y-m-d H:i:s');
                                }
                        }
                }

                $this->preprocessData('com_phmoney.account', $data);

                return $data;
        }

}
