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
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

require_once __DIR__ . '/../libraries/vendor/autoload.php';

use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

/**
 * Methods supporting a list of price records.
 *
 */
class PricesModel extends ListModel {

        public function __construct($config = array(), MVCFactoryInterface $factory = null) {

                if (empty($config['filter_fields'])) {
                        $config['filter_fields'] = array(
                                'id', 'a.id',
                                'created', 'a.created',
                        );
                }

                parent::__construct($config, $factory);
        }

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
                $id .= ':' . serialize($this->getState('filter.account'));

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
                $query->from('#__phmoney_prices AS a');

                // Join over the accounts.
                $query->select(
                                'ac.title AS account_title, ac.code AS account_code'
                        )
                        ->join('LEFT', '#__phmoney_accounts AS ac ON ac.id = a.account_id');

                // Join over the portfolios.
                $query->select(
                                'p.title AS portfolio_title'
                        )
                        ->join('LEFT', '#__phmoney_portfolios AS p ON p.id = ac.portfolio_id');

                // Join over the currencys.
                $query->select(
                                'cur.name as currency_name, cur.symbol as currency_symbol'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = ac.currency_id');

                // Join over the portfolio currency.
                $query->select(
                                'cur2.name as portfolio_currency'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur2 ON cur2.id = p.currency_id');

                // Filter on the portfolio.
                $portfolio = $this->getState('filter.portfolio');
                if (!empty($portfolio)) {
                        $query->where('ac.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('ac.portfolio_id = -1');
                }

                // Filter on the account.
                $account_id = $this->getState('filter.account');
                if (!empty($account_id)) {
                        if (is_numeric($account_id)) {
                                $query->where($db->quoteName('a.account_id') . ' = ' . (int) $account_id);
                        } elseif (is_array($account_id)) {
                                $account_id = ArrayHelper::toInteger($account_id);
                                $account_id = implode(',', $account_id);
                                if (!empty($account_id)) {
                                        $query->where($db->quoteName('a.account_id') . ' IN (' . $account_id . ')');
                                }
                        }
                }

                // Filter by search in account title.
                $search = $this->getState('filter.search');

                if (!empty($search)) {
                        if (stripos($search, 'id:') === 0) {
                                $query->where('a.id = ' . (int) substr($search, 3));
                        } else {
                                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                                $query->where('(ac.title LIKE ' . $search . ' OR ac.code LIKE ' . $search . ')');
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
         * Retrieve prices
         */
        public function retrieve() {

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                // Select the required fields from the table.
                $query->select(
                        $this->getState(
                                'list.select', 'a.id, a.code'
                        )
                );
                $query->from('#__phmoney_accounts AS a');
                $query->where('a.published = 1');
                $query->where('a.code IS NOT NULL');

                $this->populateState();
                $portfolio = $this->getState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                if (!empty($portfolio)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('a.portfolio_id = -1');
                }

                $db->setQuery($query);
                $items = $db->loadObjectList('code');
                unset($items['']);

                // Create a new client from the factory
                $client = ApiClientFactory::createApiClient();
                // Or use your own Guzzle client and pass it in
                $options = [/* ... */];
                $guzzleClient = new Client($options);
                $client = ApiClientFactory::createApiClient($guzzleClient);

                $codes = array();
                foreach ($items as $item) {
                        if (!empty($item->code)) {
                                $codes[] = $item->code;
                        }
                }
                $quotes = $client->getQuotes($codes);

                $values = array();
                foreach ($quotes as $quote) {
                        $regularMarketPrice = $quote->getRegularMarketPrice();
                        if (is_float($regularMarketPrice)) {
                                $item = $items[$quote->getSymbol()];
                                $values[] = $item->id . "," . $regularMarketPrice;
                        }
                }

                if (!empty($values)) {
                        $query->clear();
                        $query->insert('#__phmoney_prices')
                                ->columns('account_id, value')
                                ->values($values);
                        $db->setQuery($query);
                        $db->execute();
                }
        }

}
