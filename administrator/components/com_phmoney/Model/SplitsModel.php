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
use Joomla\CMS\Factory;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Methods supporting a list of split records.
 *
 */
class SplitsModel extends ListModel {

        /**
         * Constructor
         *
         * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
         * @param   MVCFactoryInterface  $factory  The factory.
         *
         * @throws  \Exception
         */
        public function __construct($config = array(), MVCFactoryInterface $factory = null) {

                if (empty($config['filter_fields'])) {
                        $config['filter_fields'] = array(
                                'post_date', 't.post_date',
                                'num', 't.num',
                                'reconcile', 'a.reconcile_state',
                                'account_id', 'a.account_id',
                        );
                }

                parent::__construct($config, $factory);
        }

        /**
         * Method to auto-populate the model state.
         *
         * This method should only be called once per instantiation and is designed
         * to be called on the first call to the getState() method unless the model
         * configuration flag to ignore the request is set.
         *
         * Note. Calling getState in this method will result in recursion.
         *
         * @param   string  $ordering   An optional ordering field.
         * @param   string  $direction  An optional direction (asc|desc).
         *
         * @return  void
         *
         */
        protected function populateState($ordering = null, $direction = null) {
                parent::populateState($ordering, $direction);

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                if (!isset($filters['portfolio'])) {
                        $this->setState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                }
        }

        /**
         * Set account filter
         * 
         * @param int $account_id
         * @return void
         */
        public function setAccount($account_id) {
                if ($account_id === 0) {
                        return;
                }

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                $filters['account'] = array(0 => (string) $account_id);
                $app->setUserState($this->context . '.filter', $filters);
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
                if (empty($data)) {
                        return parent::preprocessForm($form, $data, $group);
                }

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
                $id .= ':' . $this->getState('filter.search');
                $id .= ':' . $this->getState('filter.published');
                $id .= ':' . $this->getState('filter.portfolio');
                $id .= ':' . $this->getState('filter.reconcile_state');
                $id .= ':' . serialize($this->getState('filter.account'));
                $id .= ':' . serialize($this->getState('filter.tag'));

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
                        'list.select', 'a.id, a.transaction_id, a.account_id, a.value, a.shares, a.value/a.shares as price, a.reconcile_state'
                        )
                );
                $query->from('#__phmoney_splits AS a');

                // Join over the transaction.
                $query->select(
                                't.title, t.num, t.state, t.post_date'
                        )
                        ->join('LEFT', '#__phmoney_transactions AS t ON t.id = a.transaction_id');

                // Join over the accounts.
                $query->select(
                                'ac.title AS account_title, t.num'
                        )
                        ->join('LEFT', '#__phmoney_accounts AS ac ON ac.id = a.account_id');

                // Join over the account type
                $query->select('at.name AS account_type_name, at.value AS account_type_value')
                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = ac.account_type_id');
                
                // Join over the split type
                $query->select('st.name AS split_type_name, st.value AS split_type_value')
                        ->join('LEFT', $db->quoteName('#__phmoney_split_types') . ' AS st ON st.id = a.split_type_id');

                // Join over the currencys.
                $query->select(
                                'cur.symbol as currency_symbol, cur.denom'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = ac.currency_id');

                // Join over the portfolios
                $query->select('p.title AS portfolio_title, p.currency_id AS portfolio_currency_id')
                        ->join('LEFT', $db->quoteName('#__phmoney_portfolios') . ' AS p ON p.id = ac.portfolio_id')
                        ->where('p.published = 1');

                // Filter on the portfolio.
                $portfolio = $this->getState('filter.portfolio');
                if (!empty($portfolio)) {
                        $query->where('ac.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('ac.portfolio_id = -1');
                }

                //Filter by published state
                $published = (string) $this->getState('filter.published');
                if (is_numeric($published)) {
                        $query->where('t.state = ' . (int) $published);
                } elseif ($published === '') {
                        $query->where('(t.state = 0 OR t.state = 1)');
                }

                // Filter by account
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

                // Filter on the account type.
                $account_type = $this->getState('filter.account_type');
                if (!empty($account_type)) {
                        $query->where('ac.account_type_id = ' . (int) $account_type);
                }
                
                // Filter by split type
                $split_type_id = $this->getState('filter.split_type');
                if (!empty($split_type_id)) {
                        if (is_numeric($split_type_id)) {
                                $query->where($db->quoteName('a.split_type_id') . ' = ' . (int) $split_type_id);
                        } elseif (is_array($split_type_id)) {
                                $split_type_id = ArrayHelper::toInteger($split_type_id);
                                $split_type_id = implode(',', $split_type_id);
                                if (!empty($split_type_id)) {
                                        $query->where($db->quoteName('a.split_type_id') . ' IN (' . $split_type_id . ')');
                                }
                        }
                }

                // Filter by search in title.
                $search = $this->getState('filter.search');

                if (!empty($search)) {
                        if (stripos($search, 'id:') === 0) {
                                $query->where('a.id = ' . (int) substr($search, 3));
                        } elseif (stripos($search, 'author:') === 0) {
                                $search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
                        } else {
                                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                                $query->where('(t.title LIKE ' . $search . ' OR t.description LIKE ' . $search . ')');
                        }
                }

                // Filter by a single or group of tags.
                $hasTag = false;
                $tagId = $this->getState('filter.tag');

                if (is_numeric($tagId)) {
                        $hasTag = true;

                        $query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId);
                } elseif (is_array($tagId)) {
                        $tagId = ArrayHelper::toInteger($tagId);
                        $tagId = implode(',', $tagId);
                        if (!empty($tagId)) {
                                $hasTag = true;

                                $query->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tagId . ')');
                        }
                }

                if ($hasTag) {
                        $query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                                . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.account_id')
                                . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_phmoney.account')
                        );
                }

                // Filter by date
                $date = $this->getState('filter.post_date');
                if (!empty($date)) {
                        $query->where('t.post_date >=' . $db->q($date . ' 00:00:00'));
                }
                if (!empty($date)) {
                        $query->where('t.post_date <=' . $db->q($date . ' 23:59:59'));
                }

                // Add the list ordering clause.
                $orderCol = $this->state->get('list.ordering', 't.post_date');
                $orderDirn = $this->state->get('list.direction', 'DESC');
                $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
                $query->order('a.id ' . ' ' . $db->escape($orderDirn));

                return $query;
        }

        /**
         * Method to calculate running balance.
         *
         * @param array $items An array of data items
         * @param string $orderCol Ordering direction of the post date
         * @return array An array of data items
         */
        protected function calculateRunningBalance($items) {

                //calculate balance for all items
                $db = $this->getDbo();
                $query = $this->query;
                $query->clear("limit");

                $db->setQuery($query);
                $balance_items = $db->loadObjectList("id");

                $orderDirn = $this->state->get('list.direction', 'DESC');
                if ($orderDirn === "DESC") {
                        $balance_items = array_reverse($balance_items, true);
                }

                $previous_index = 0;
                foreach ($balance_items as $index => &$balance_item) {
                        if ($previous_index > 0) {
                                $balance_item->balance = $balance_items[$previous_index]->balance + $balance_item->value;
                        } else {
                                $balance_item->balance = $balance_item->value;
                        }
                        $previous_index = $index;
                }

                //copy balance in pagination items
                foreach ($items as &$item) {
                        $item->balance = $balance_items[$item->id]->balance;
                }

                return $items;
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
                        $account_id = $this->getState('filter.account');
                        if (!empty($account_id) && !empty($this->cache[$store])) {
                                if (is_numeric($account_id)) {
                                        $this->cache[$store] = $this->calculateRunningBalance($this->cache[$store]);
                                } elseif (is_array($account_id) && count($account_id) == 1) {
                                        $this->cache[$store] = $this->calculateRunningBalance($this->cache[$store]);
                                }
                        }
                } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                }

                return $this->cache[$store];
        }

        /**
         * Get the filter form
         *
         * @param   array    $data      data
         * @param   boolean  $loadData  load current data
         *
         * @return  \JForm|boolean  The \JForm object or false on error
         */
        public function getBatchForm() {
                $form = $this->loadForm($this->context . '.batch', 'batch_splits');

                return $form;
        }

}
