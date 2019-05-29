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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/../libraries/vendor/autoload.php';

use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

/**
 * Methods supporting a list of rate records.
 *
 */
class RatesModel extends ListModel {

        /**
         * Method to allow derived classes to preprocess the form.
         *
         * @param   \JForm  $form   A \JForm object.
         * @param   mixed   $data   The data expected for the form.
         * @param   string  $group  The name of the plugin group to import (defaults to "content").
         *
         * @return  void
         *
         * @throws  \Exception if there is an error in the form event.
         */
        protected function preprocessForm(\JForm $form, $data, $group = 'content') {
                if (isset($data->filter['portfolio'])) {
                        $portfolio = $data->filter['portfolio'];
                }

                if (empty($portfolio)) {
                        $data->filter['portfolio'] = PhmoneyHelper::getDefaultPortfolio();
                }

                return parent::preprocessForm($form, $data, $group);
        }

        /**
         * Method to get a store id based on model configuration state.
         *
         * This is necessary because the model is used by the component and
         * different modules that might need different sets of data or different
         * ordering requirements.
         *
         * @param   string  $id  A prefix for the store id.
         *
         * @return  string  A store id.
         *
         */
        protected function getStoreId($id = '') {
                // Compile the store id.
                $id .= ':' . $this->getState('filter.portfolio');
                $id .= ':' . $this->getState('filter.currency_id_1');
                $id .= ':' . $this->getState('filter.currency_id_2');

                return parent::getStoreId($id);
        }

        /**
         * Build an SQL query to load the list data.
         *
         * @return  \JDatabaseQuery
         *
         */
        protected function getListQuery() {

                // Create a new query object.
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                // Select the required fields from the table.
                $query->select(
                        $this->getState(
                                'list.select', 'a.id, a.created, a.value, 1 / a.value as inverse_value'
                        )
                );
                $query->from('#__phmoney_rates AS a');

                // Join over the portfolios.
                $query->select(
                                'p.title AS portfolio_title'
                        )
                        ->join('LEFT', '#__phmoney_portfolios AS p ON p.id = a.portfolio_id');

                // Join over the currencys.
                $query->select(
                                'cur.name as currency_name, cur.symbol'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = a.currency_id');

                // Join over the portfolio currency.
                $query->select(
                                'cur2.name as portfolio_currency'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur2 ON cur2.id = p.currency_id');

                // Filter on the portfolio.
                $portfolio = $this->getState('filter.portfolio');
                if (!empty($portfolio)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('a.portfolio_id = -1');
                }

                // Filter by search in currency name.
                $search = $this->getState('filter.search');

                if (!empty($search)) {
                        if (stripos($search, 'id:') === 0) {
                                $query->where('a.id = ' . (int) substr($search, 3));
                        } else {
                                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                                $query->where('(cur.name LIKE ' . $search . ')');
                        }
                }

                // Filter by published currency
                $currency = $this->getState('filter.currency_id');
                if (!empty($currency)) {

                        $query->where('a.currency_id = ' . (int) $currency);
                }

                // Add the list ordering clause.
                $orderCol = $this->state->get('list.ordering', 'a.created');
                $orderDirn = $this->state->get('list.direction', 'DESC');

                $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

                return $query;
        }

        /**
         * Method to get an array of data items.
         *
         * @return  mixed  An array of data items on success, false on failure.
         *
         */
        public function getItems() {

                // Get a storage key.
                $store = $this->getStoreId();

                // Try to load the data from internal storage.
                if (isset($this->cache[$store])) {
                        return $this->cache[$store];
                }

                try {
                        // Load the list items and add the items to the internal cache.
                        $this->cache[$store] = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));
                } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                }

                return $this->cache[$store];
        }

        /**
         * Retrieve rates from Yahoo
         */
        public function download() {
                $this->populateState();
                $portfolio_id = $this->getState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                //get portfolio currency
                $query->select(
                        $this->getState(
                                'list.select', 'c.code AS currency_code'
                        )
                );
                $query->from('#__phmoney_portfolios AS a');
                $query->select('c.code AS currency_code')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id');
                if (!empty($portfolio_id)) {
                        $query->where('a.id = ' . (int) $portfolio_id);
                } else {
                        throw new \Exception(Text::_('COM_PHMONEY_PORTFOLIO_NOT_SET'), 500);
                }

                $db->setQuery($query);
                $portfolio_currency = $db->loadResult();

                //get accounts currency
                $query->clear();
                $query->select(
                        $this->getState(
                                'list.select', 'c.id AS currency_id, c.code AS currency_code'
                        )
                );
                $query->from('#__phmoney_accounts AS a')
                        ->where('a.published = 1');
                $query->select('c.code AS currency_code')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id')
                        ->where('c.code NOT LIKE ' . $db->q($portfolio_currency));
                if (!empty($portfolio_id)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio_id);
                } else {
                        $query->where('a.portfolio_id = -1');
                }

                $db->setQuery($query);
                $items = $db->loadObjectList('currency_code');
                unset($items['']);

                // Create a new client from the factory
                $client = ApiClientFactory::createApiClient();
                // Or use your own Guzzle client and pass it in
                $options = [/* ... */];
                $guzzleClient = new Client($options);
                $client = ApiClientFactory::createApiClient($guzzleClient);

                $codes = array();
                foreach ($items as $item) {
                        $codes[] = [$item->currency_code, $portfolio_currency];
                }
                $rates = $client->getExchangeRates($codes);

                $query->clear();
                $query->insert('#__phmoney_rates')
                        ->columns('portfolio_id, currency_id, value');
                $values = array();
                foreach ($rates as $rate) {
                        $regularMarketPrice = $rate->getRegularMarketPrice();
                        if (is_float($regularMarketPrice)) {
                                $symbol = substr($rate->getShortName(), 0, 3);
                                $query->values((int) $portfolio_id . ',' . 
                                        $items[$symbol]->currency_id . ',' .
                                        $regularMarketPrice);
                        }                        
                }
                
                $db->setQuery($query);
                $db->execute();
        }

}
