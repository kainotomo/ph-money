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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Description of AccountsModel
 *
 */
class AccountsModel extends ListModel
{

        public function __construct($config = array(), MVCFactoryInterface $factory = null)
        {

                if (empty($config['filter_fields'])) {
                        $config['filter_fields'] = array(
                                'lft', 'a.lft',
                                'rgt', 'a.rgt',
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
        protected function populateState($ordering = 'a.lft', $direction = 'asc')
        {
                parent::populateState($ordering, $direction);

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                if (!isset($filters['portfolio'])) {
                        $this->setState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                        $this->setState('filter.report_type', 'balances');
                }

                //set dates filter
                $quarters = array();
                $quarters['01'] = 'January';
                $quarters['02'] = 'January';
                $quarters['03'] = 'January';
                $quarters['04'] = 'April';
                $quarters['05'] = 'April';
                $quarters['06'] = 'April';
                $quarters['07'] = 'July';
                $quarters['08'] = 'July';
                $quarters['09'] = 'July';
                $quarters['10'] = 'October';
                $quarters['11'] = 'October';
                $quarters['12'] = 'October';

                if (empty($filters['report_type'])) {
                        $filters['report_type'] = 'balances';
                        $this->setState('filter.report_type', 'balances');
                }
                $report_type = $filters['report_type'];
                if (!isset($filters['relative_start'])) {
                        $filters['relative_start'] = '';
                }
                $relative_start = $filters['relative_start'];
                if (!isset($filters['relative_end'])) {
                        $filters['relative_end'] = '';
                }
                $relative_end = $filters['relative_end'];
                switch ($report_type) {
                        case 'balance_sheet':
                                if ($relative_end == '8') {
                                        $relative_end = '0';
                                }
                                break;
                        case 'income_statement' || 'cash_flow' || 'accounts_bar_chart':
                                if ($relative_start == '8') {
                                        $relative_start = '0';
                                }
                                if ($relative_end == '8') {
                                        $relative_end = '0';
                                }
                                break;
                        default:
                                break;
                }

                if ($report_type != 'balances' && $report_type != 'shares_portfolio' && $report_type != 'shares_signals') {
                        $this->setState('list.limit', 0);
                        $this->setState('list.start', 0);
                }

                if (!isset($filters['start_date'])) {
                        $filters['start_date'] = '';
                }
                $start_date = $filters['start_date'];
                switch ($relative_start) {
                        case '1': //today
                                $start_date = date("Y-m-d");
                                break;
                        case '2': //start of this month
                                $start_date = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
                                break;
                        case '3': //start of previous month
                                $start_date = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                                break;
                        case '4': //start of this quarter                                
                                $quarter = strtotime('first day of ' . $quarters[date("m")], time());
                                $start_date = date('Y-m-d', $quarter);
                                break;
                        case '5': //start of previous quarter       
                                $quarter = strtotime('first day of ' . $quarters[date("m")], time());
                                $date = date_create(date('Y-m-d', $quarter));
                                $date_time = date_sub($date, date_interval_create_from_date_string("3 months"));
                                $start_date = $date_time->format('Y-m-d');
                                break;
                        case '6': //start of this year
                                $start_date = date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y")));
                                break;
                        case '7': //start of previous year
                                $start_date = date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y") - 1));
                                break;
                        case '8': //no filtering
                                $start_date = '';
                                break;
                        default: //no filtering
                                $start_date = '';
                                break;
                }
                $this->setState('filter.start_date', $start_date);

                if (!isset($filters['end_date'])) {
                        $filters['end_date'] = '';
                }
                $end_date = $filters['end_date'];
                switch ($relative_end) {
                        case '1': //today
                                $end_date = date("Y-m-d");
                                break;
                        case '2': //last day of this month
                                $month_end = strtotime('last day of this month', time());
                                $end_date = date('Y-m-d', $month_end);
                                break;
                        case '3': //last day of previous month
                                $previous_month_end = strtotime('last day of previous month', time());
                                $end_date = date('Y-m-d', $previous_month_end);
                                break;
                        case '4': //last day of this quarter
                                $quarter = strtotime('last day of ' . $quarters[date("m")], time());
                                $end_date = date('Y-m-d', $quarter);
                                break;
                        case '5': //last day of previous quarter
                                $quarter = strtotime('first day of ' . $quarters[date("m")], time());
                                $date = date_create(date('Y-m-d', $quarter));
                                $date_time = date_sub($date, date_interval_create_from_date_string("1 day"));
                                $end_date = $date_time->format('Y-m-d');
                                break;
                        case '6': //end of this year
                                $end_date = date("Y-m-d", mktime(0, 0, 0, 12, 31, date("Y")));
                                break;
                        case '7': //end of previous year
                                $end_date = date("Y-m-d", mktime(0, 0, 0, 12, 31, date("Y") - 1));
                                break;
                        case '8': //no filtering
                                $end_date = '';
                                break;
                        default: //absolute
                                $end_date = '';
                                break;
                }
                $this->setState('filter.end_date', $end_date);
        }

        protected function preprocessForm(\JForm $form, $data, $group = 'content')
        {

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

        protected function loadFormData()
        {
                $data = parent::loadFormData();
                // Pre-fill the list options
                if (!property_exists($data, 'filter')) {
                        $portfolio_id = $this->getState('filter.portfolio');
                        if (!is_null($portfolio_id)) {
                                $portfolios = PhmoneyHelper::getPortfolios();
                                $portfolio = $portfolios[$portfolio_id];
                                $data->filter = array(
                                        'start_date' => $portfolio->params->start_date,
                                        'end_date' => $portfolio->params->end_date
                                );
                        }
                }

                return $data;
        }

        protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
        {

                $form = parent::loadForm($name, $source, $options, $clear, $xpath);

                //hide list limit in case of report
                $report_type = $this->getState('filter.report_type', 'balances');
                if ($report_type != 'balances' && $report_type != 'shares_portfolio' && $report_type != 'shares_signals') {
                        $form->setFieldAttribute('limit', 'type', 'hidden', 'list');
                }
                if ($report_type == 'tree' || $report_type == 'balances') {
                        $form->setFieldAttribute('show_zero_accounts', 'type', 'hidden', 'filter');
                }
                if ($report_type !== 'accounts_bar_chart') {
                        $form->setFieldAttribute('date_interval', 'type', 'hidden', 'filter');
                }

                return $form;
        }

        protected function getStoreId($id = '')
        {
                // Add the new filter state to the store id.
                $id .= ':' . $this->getState('filter.search');
                $id .= ':' . $this->getState('filter.published');
                $id .= ':' . $this->getState('filter.level');
                $id .= ':' . serialize($this->getState('filter.tag'));
                $id .= ':' . $this->getState('filter.portfolio');

                return parent::getStoreId($id);
        }

        protected function getListQuery()
        {
                // Create a new query object.
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                // Select the required fields from the table.
                $query->select(
                        $this->getState(
                                'list.select', 'a.id, a.title, a.alias, a.note, a.code, a.published' .
                                ', a.checked_out, a.checked_out_time, a.created_user_id' .
                                ', a.path, a.parent_id, a.level, a.lft, a.rgt, a.params'
                        )
                );
                $query->from('#__phmoney_accounts AS a');

                // Join over the portfolios
                $query->select('p.title AS portfolio_title, p.currency_id AS portfolio_currency_id')
                        ->join('LEFT', $db->quoteName('#__phmoney_portfolios') . ' AS p ON p.id = a.portfolio_id')
                        ->where('p.published = 1');

                // Join over the portfolio currencys
                $query->select('c2.name AS portfolio_currency_name, c2.symbol AS portfolio_currency_symbol, c2.denom AS portfolio_currency_denom')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c2 ON c2.id = p.currency_id');

                // Join over the currencys
                $query->select('c.name AS currency_name, c.symbol AS currency_symbol, c.denom AS currency_denom')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id');

                // Join over the account type
                $query->select('at.name AS account_type_name, at.value AS account_type_value')
                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = a.account_type_id');

                // Join over the users for the checked out user.
                $query->select('uc.name AS editor')
                        ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

                // Join over the users for the author.
                $query->select('ua.name AS author_name')
                        ->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

                //get report type necessary for filtering
                $report_type = $this->getState('filter.report_type', 'balances');

                // Filter on the portfolio.
                $portfolio = $this->getState('filter.portfolio');
                if (!empty($portfolio)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('a.portfolio_id = -1');
                }

                // Filter on the account type.
                $account_type = $this->getState('filter.account_type');
                if (!empty($account_type)) {
                        if (is_numeric($account_type)) {
                                $query->where($db->quoteName('a.account_type_id') . ' = ' . (int) $account_type);
                        } elseif (is_array($account_type)) {
                                $account_type = ArrayHelper::toInteger($account_type);
                                $account_type = implode(',', $account_type);
                                if (!empty($account_type)) {
                                        $query->where($db->quoteName('a.account_type_id') . ' IN (' . $account_type . ')');
                                }
                        }
                }

                // Filter by account
                $account_id = $this->getState('filter.account');
                if (!empty($account_id)) {
                        if (is_numeric($account_id)) {
                                $query->where($db->quoteName('a.id') . ' = ' . (int) $account_id);
                        } elseif (is_array($account_id)) {
                                $account_id = ArrayHelper::toInteger($account_id);
                                $account_id = implode(',', $account_id);
                                if (!empty($account_id)) {
                                        $query->where($db->quoteName('a.id') . ' IN (' . $account_id . ')');
                                }
                        }
                }

                // Filter on the currency
                $currency = $this->getState('filter.currency');
                if (!empty($currency)) {
                        $query->where('a.currency_id = ' . (int) $currency);
                }

                // Filter on the level.
                $level = $this->getState('filter.level');
                if ($report_type === "accounts_pie_chart" || $report_type === "accounts_bar_chart" || $report_type === "accounts_bar_chart_cumulative") {
                        if (empty($level)) {
                                $level = 1;
                        }
                        $query->where('a.level = ' . (int) $level);
                } else {
                        if ($level) {
                                $query->where('a.level <= ' . (int) $level);
                        }
                }

                // Filter by published state
                $published = (string) $this->getState('filter.published');
                if ($report_type != 'tree' && $report_type != 'balances') {
                        $published = 1;
                }
                if (is_numeric($published)) {
                        $query->where('a.published = ' . (int) $published);
                } elseif ($published === '') {
                        $query->where('(a.published IN (0, 1))');
                }

                // Filter by search in title
                $search = $this->getState('filter.search');

                if (!empty($search)) {
                        if (stripos($search, 'id:') === 0) {
                                $query->where('a.id = ' . (int) substr($search, 3));
                        } else {
                                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
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

                if ($hasTag || $report_type === 'tags_balance' || $report_type === 'tags_pie_chart' || $report_type === 'tags_bar_chart' || $report_type === 'tags_bar_chart_cumulative') {
                        $query->select('tagmap.tag_id');
                        $query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                                . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
                                . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_phmoney.account')
                        );
                }

                // Filter on the report type.                
                if (!empty($report_type)) {
                        switch ($report_type) {
                                case 'balance_sheet':
                                        $query->where('a.account_type_id IN (1,2,3)');
                                        break;
                                case 'income_statement':
                                        $query->where('a.account_type_id IN (4,5)');
                                        break;
                                case 'shares_portfolio':
                                        $query->where('a.account_type_id = 2');
                                        break;
                                case 'shares_signals':
                                        $query->where('a.account_type_id = 2');
                                        break;
                                default:
                                        break;
                        }
                }

                // Add the list ordering clause
                $listOrdering = $this->getState('list.ordering', 'a.lft');
                $listDirn = $db->escape($this->getState('list.direction', 'ASC'));

                if ($listOrdering == 'a.lft') {
                        //$query->order($db->escape($listOrdering) . ' ' . $listDirn);			
                } else {
                        //$query->order('a.rgt desc' . ', ' . $listOrdering . ' ' . $listDirn);
                        //$query->order($db->escape($listOrdering) . ' ' . $listDirn . ', ' . 'a.rgt desc');
                }
                $query->order($db->escape($listOrdering) . ' ' . $listDirn);

                /*
                  $query->group('a.id,
                  a.title,
                  a.alias,
                  a.note,
                  a.code,
                  a.published,
                  a.checked_out,
                  a.checked_out_time,
                  a.created_user_id,
                  a.path,
                  a.parent_id,
                  a.level,
                  a.lft,
                  a.rgt,
                  uc.name,
                  p.title,
                  p.currency_id,
                  c.name,
                  c.symbol,
                  c.denom,
                  c2.name,
                  c2.symbol,
                  c2.denom,
                  at.name,
                  at.value,
                  ua.name'
                  );
                 * 
                 */

                return $query;
        }

        /**
         * Method to get an array of data items.
         *
         * @return  mixed  An array of data items on success, false on failure.
         *
         */
        public function getItems()
        {

                // Get a storage key.
                $store = $this->getStoreId();

                // Try to load the data from internal storage.
                if (isset($this->cache[$store])) {
                        return $this->cache[$store];
                }

                try {
                        $report_type = $this->getState('filter.report_type', 'balances');
                        // Load the list items and add the items to the internal cache.
                        $this->cache[$store] = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));
                        if ($this->cache[$store] != false && $report_type !== 'accounts_bar_chart') {
                                $start_date = $this->getState('filter.start_date');
                                $end_date = $this->getState('filter.end_date');
                                $this->cache[$store] = $this->sumItems($this->cache[$store], $start_date, $end_date);
                        }
                } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                }

                return $this->cache[$store];
        }

        /**
         * Calculate sums based on sums
         * 
         * @param array $items
         * @param string $start_date
         * @param string $end_date
         * @return array of items
         */
        protected function sumItems($items, $start_date, $end_date)
        {

                $report_type = $this->getState('filter.report_type', 'balances');

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                //get rates
                $portfolio_id = $this->getState('filter.portfolio');
                if (empty($portfolio_id)) {
                        $portfolio_id = -1;
                }
                $query->select('a.currency_id, a.value')
                        ->from('#__phmoney_rates as a')
                        ->where('a.portfolio_id = ' . (int) $portfolio_id)
                        ->order('a.created asc');
                $db->setQuery($query);
                $rates = $db->loadObjectList('currency_id');

                //get values for all accounts
                $query = $db->getQuery(true);
                $portfolio = $this->getState('filter.portfolio');
                if (empty($portfolio)) {
                        $portfolio = -1;
                }
                $query->select('ac.id, ac.level, ac.parent_id, act.value as account_type')
                        ->from('#__phmoney_accounts AS ac')
                        ->where('ac.portfolio_id = ' . (int) $portfolio)
                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS act ON act.id = ac.account_type_id');

                $db->setQuery($query);
                $sum_items = $db->loadObjectList('id');

                //get split_types
                $query->clear();
                $query->select('id, value')
                        ->from('#__phmoney_split_types');
                $db->setQuery($query);
                $split_type_ids = $db->loadObjectList('value');

                foreach ($sum_items as $item) {
                        $query->clear();
                        $query->select('SUM(a.value) AS sum_value_account, SUM(ac.currency_id) AS currency_id, COUNT(ac.currency_id) AS count, SUM(a.shares) AS shares, SUM(a.value * a.shares / a.shares) AS sum_shares_value')
                                ->from('#__phmoney_splits as a')
                                ->where('a.account_id = ' . (int) $item->id)
                                ->where('a.state = 1');
                        $query->join('LEFT', $db->quoteName('#__phmoney_accounts') . ' AS ac ON ac.id = a.account_id');
                        $query->join('LEFT', $db->quoteName('#__phmoney_transactions') . ' AS t ON t.id = a.transaction_id');
                        if (!empty($start_date)) {
                                $query->where('t.post_date >=' . $db->q($start_date . ' 00:00:00'));
                        }
                        if (!empty($end_date)) {
                                $query->where('t.post_date <=' . $db->q($end_date . ' 23:59:59'));
                        }

                        $db->setQuery($query);
                        $result = $db->loadObject();

                        //calculate share account 
                        if (is_null($result->sum_shares_value)) {
                                $result->sum_shares_value = 0;
                                $result->money_in = 0;
                        }
                        if ($item->account_type == 'share' && $result->shares == 0) {
                                $result->sum_value_account = 0;
                        }

                        //for excluding share price corrections
                        $result->dividends = 0;
                        $result->money_in = 0;
                        $result->money_out = 0;
                        if (($report_type == 'shares_portfolio' || $report_type == 'shares_signals') && $item->account_type == 'share') {
                                //money in
                                $query->where('a.shares >= ' . 0);
                                $db->setQuery($query);
                                $result2 = $db->loadObject();
                                $result->money_in = $result2->sum_shares_value;
                                $result->money_out = $result->money_in - $result->sum_shares_value;
                                $x = $result->shares / $result2->shares; //percentage of kept shares

                                //dividends or fee
                                $query->where('a.split_type_id = ' . $split_type_ids['dividend']->id . ' || a.split_type_id = ' . $split_type_ids['fee']->id);
                                $db->setQuery($query);
                                $result2 = $db->loadObject();
                                $result->dividends = -$result2->sum_value_account;
                        }

                        //calculate to portfolio currency
                        $result->sum_value_portfolio = $result->sum_value_account;
                        $result->sum_shares_value_portfolio = $result->sum_shares_value;
                        $result->money_in_portfolio = $result->money_in;
                        $result->money_out_portfolio = $result->money_out;
                        $result->dividends_portfolio = $result->dividends;
                        if ($result->count != 0) {
                                $result->currency_id = $result->currency_id / $result->count;
                                if (isset($rates[$result->currency_id])) {
                                        $result->sum_value_portfolio *= $rates[$result->currency_id]->value;
                                        $result->sum_shares_value_portfolio = $result->sum_shares_value * $rates[$result->currency_id]->value;
                                        $result->money_in_portfolio = $result->money_in * $rates[$result->currency_id]->value;
                                        $result->money_out_portfolio = $result->money_out * $rates[$result->currency_id]->value;
                                        $result->dividends_portfolio = $result->dividends * $rates[$result->currency_id]->value;
                                }
                        }

                        if (!is_null($result->sum_value_account)) {
                                $item->value = $result->sum_value_account;
                                $item->value_portfolio = $result->sum_value_portfolio;
                                $item->value_portfolio_total = $result->sum_value_portfolio;
                                if (($report_type == 'shares_portfolio' || $report_type == 'shares_signals') && $item->account_type == 'share') {
                                        $item->sum_shares_value = $result->sum_shares_value;
                                        $item->sum_shares_value_portfolio = $result->sum_shares_value_portfolio;
                                        $item->money_in = $result->money_in;
                                        $item->money_in_portfolio = $result->money_in_portfolio;
                                        $item->money_out = $result->money_out;
                                        $item->money_out_portfolio = $result->money_out_portfolio;
                                        $item->dividends = $result->dividends;
                                        $item->dividends_portfolio = $result->dividends_portfolio;
                                        $item->basis = $item->money_in * $x;
                                        $item->basis_portfolio = $item->money_in_portfolio * $x;
                                        $item->total_return = $item->value - $item->sum_shares_value;
                                        $item->total_return_portfolio = $item->value_portfolio - $item->sum_shares_value_portfolio;
                                        $item->unrealized_gain = $item->value - $item->basis;
                                        $item->unrealized_gain_portfolio = $item->value_portfolio - $item->basis_portfolio;   
                                        $item->realized_gain = $item->total_return - $item->unrealized_gain;
                                        $item->realized_gain_portfolio = $item->total_return_portfolio - $item->unrealized_gain_portfolio;                                                                          
                                        $item->total_return += $item->dividends;
                                        $item->total_return_portfolio += $item->dividends_portfolio;
                                        if ($item->sum_shares_value < 0) {
                                                $item->sum_shares_value = 0;
                                                $item->sum_shares_value_portfolio = 0;
                                        }
                                        if ($item->money_in == 0) {
                                                $item->rate_of_return = 0;
                                        } else {
                                                $item->rate_of_return = $item->total_return / $item->money_in * 100;
                                        }
                                }
                        } else {
                                $item->value = 0;
                                $item->value_portfolio = 0;
                                $item->value_portfolio_total = 0;
                                if ($report_type == 'shares_portfolio' || $report_type == 'shares_signals') {
                                        $item->sum_shares_value = 0;
                                        $item->sum_shares_value_portfolio = 0;
                                        $item->money_in = 0;
                                        $item->money_in_portfolio = 0;
                                        $item->dividends = 0;
                                        $item->dividends_portfolio = 0;
                                        $item->basis=0;
                                        $item->basis_portfolio=0;
                                        $item->unrealized_gain = 0;
                                        $item->unrealized_gain_portfolio = 0;
                                        $item->realized_gain = 0;
                                        $item->realized_gain_portfolio = 0;
                                        $item->total_return = 0;
                                        $item->total_return_portfolio = 0;
                                        $item->rate_of_return = 0;
                                }
                        }
                        $item->shares = (float) $result->shares;
                }

                //sum child accounts
                foreach ($sum_items as $item_parent) {
                        if ($item_parent->level == 1) {
                                $item_parent->value_portfolio_total += $this->sumItem($sum_items, $item_parent);
                        }
                }

                //sum accounts
                foreach ($items as $item) {
                        $item->value = $sum_items[$item->id]->value;
                        $item->value_portfolio_total = $sum_items[$item->id]->value_portfolio_total;
                        $item->value_portfolio = $sum_items[$item->id]->value_portfolio;
                        $item->shares = $sum_items[$item->id]->shares;
                        if ($report_type == 'shares_portfolio' || $report_type == 'shares_signals') {
                                $item->sum_shares_value = $sum_items[$item->id]->sum_shares_value;
                                $item->sum_shares_value_portfolio = $sum_items[$item->id]->sum_shares_value_portfolio;
                                $item->basis = $sum_items[$item->id]->basis;
                                $item->basis_portfolio = $sum_items[$item->id]->basis_portfolio;
                                $item->money_in = $sum_items[$item->id]->money_in;
                                $item->money_in_portfolio = $sum_items[$item->id]->money_in_portfolio;
                                $item->money_out = $sum_items[$item->id]->money_out;
                                $item->money_out_portfolio = $sum_items[$item->id]->money_out_portfolio;
                                $item->dividends = $sum_items[$item->id]->dividends;
                                $item->dividends_portfolio = $sum_items[$item->id]->dividends_portfolio;
                                $item->unrealized_gain = $sum_items[$item->id]->unrealized_gain;
                                $item->unrealized_gain_portfolio = $sum_items[$item->id]->unrealized_gain_portfolio;
                                $item->realized_gain = $sum_items[$item->id]->realized_gain;
                                $item->realized_gain_portfolio = $sum_items[$item->id]->realized_gain_portfolio;
                                $item->total_return = $sum_items[$item->id]->total_return;
                                $item->total_return_portfolio = $sum_items[$item->id]->total_return_portfolio;
                                $item->rate_of_return = $sum_items[$item->id]->rate_of_return;
                        }
                        $item->params = json_decode($item->params);
                }

                //remove zero balance items
                $show_zero_balance = $this->state->get('filter.show_zero_accounts', 0);
                if ($show_zero_balance || $report_type == 'tree' || $report_type == 'balances') {
                        $result = $items;
                } else {
                        $result = array();
                        foreach ($items as $item) {
                                if ($item->value_portfolio_total != 0) {
                                        $result[] = $item;
                                }
                        }
                }

                if ($report_type === "accounts_pie_chart") { //calculate percentage
                        $total = 0;
                        foreach ($result as $item) {
                                $total += abs($item->value_portfolio_total);
                        }
                        foreach ($result as &$item) {
                                $item->percentage = round(abs($item->value_portfolio_total) / $total * 100, 2);
                        }
                }

                return $result;
        }

        protected function sumItem(&$items, &$item_parent)
        {

                $sum = 0;
                foreach ($items as $item_child) {
                        if ($item_parent->id == $item_child->parent_id) {
                                $item_child->value_portfolio_total += $this->sumItem($items, $item_child);
                                $sum += $item_child->value_portfolio_total;
                        }
                }

                return $sum;
        }

        /**
         * Calculate the sum of accounts per type.
         * The sum is done individual per period. Suitable for Income/Expences
         * 
         * @return array The account types balances
         */
        public function getBarChart()
        {

                $start_date = $this->getState('filter.start_date');
                $end_date = $this->getState('filter.end_date');
                $date_inteval = $this->getState('filter.date_interal', 'month');
                if (empty($start_date)) { //one year ago
                        $start_date = date('Y-m-d', strtotime('-1 year'));
                }
                if (empty($end_date)) { //today                        
                        $end_date = date("Y-m-d");
                }

                $items2 = array();
                $items = $this->getItems();
                $running_date1 = $start_date;
                $running_date2 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                while ($running_date2 <= $end_date) {
                        $items2[$running_date2] = ArrayHelper::toObject(json_decode(json_encode($items), true));
                        $items2[$running_date2] = $this->sumItems($items2[$running_date2], $running_date1, $running_date2);
                        $running_date1 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                        $running_date2 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                }

                return $items2;
        }

        /**
         * Calculate the sum of accounts per type.
         * The sum is done cumulative per period. Suitable for Assets
         * 
         * @return array The account types balances
         */
        public function getBarChartCumulative()
        {

                $start_date = $this->getState('filter.start_date');
                $end_date = $this->getState('filter.end_date');
                $date_inteval = $this->getState('filter.date_interal', 'month');
                if (empty($start_date)) { //one year ago
                        $start_date = date('Y-m-d', strtotime('-1 year'));
                }
                if (empty($end_date)) { //today                        
                        $end_date = date("Y-m-d");
                }

                $items2 = array();
                $items = $this->getItems();
                $running_date = date("Y-m-d", strtotime($start_date . " +1 " . $date_inteval));
                while ($running_date <= $end_date) {
                        $items2[$running_date] = ArrayHelper::toObject(json_decode(json_encode($items), true));
                        $items2[$running_date] = $this->sumItems($items2[$running_date], null, $running_date);
                        $running_date = date("Y-m-d", strtotime($running_date . " +1 " . $date_inteval));
                }

                return $items2;
        }

        /**
         * Calculate the sum of accounts per tag.
         * The sum is done individual per period. Suitable for Income/Expences
         * 
         * @return array The account types balances
         */
        public function getTagsBarChart()
        {

                $start_date = $this->getState('filter.start_date');
                $end_date = $this->getState('filter.end_date');
                $date_inteval = $this->getState('filter.date_interal', 'month');
                if (empty($start_date)) { //one year ago
                        $start_date = date('Y-m-d', strtotime('-1 year'));
                }
                if (empty($end_date)) { //today                        
                        $end_date = date("Y-m-d");
                }

                $tags = array();
                $items2 = array();
                $items = $this->getItems();
                $running_date1 = $start_date;
                $running_date2 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                while ($running_date2 <= $end_date) {
                        $items2[$running_date2] = ArrayHelper::toObject(json_decode(json_encode($items), true));
                        $items2[$running_date2] = $this->sumItems($items2[$running_date2], $running_date1, $running_date2);
                        $tags[$running_date2] = $this->getTagsBalance($items2[$running_date2]);
                        $running_date1 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                        $running_date2 = date("Y-m-d", strtotime($running_date1 . " +1 " . $date_inteval));
                }

                return $tags;
        }

        /**
         * Calculate the sum of accounts per tag.
         * The sum is done individual per period. Suitable for Income/Expences
         * 
         * @return array The account types balances
         */
        public function getTagsBarChartCumulative()
        {

                $start_date = $this->getState('filter.start_date');
                $end_date = $this->getState('filter.end_date');
                $date_inteval = $this->getState('filter.date_interal', 'month');
                if (empty($start_date)) { //one year ago
                        $start_date = date('Y-m-d', strtotime('-1 year'));
                }
                if (empty($end_date)) { //today                        
                        $end_date = date("Y-m-d");
                }

                $tags = array();
                $items2 = array();
                $items = $this->getItems();
                $running_date = date("Y-m-d", strtotime($start_date . " +1 " . $date_inteval));
                while ($running_date <= $end_date) {
                        $items2[$running_date] = ArrayHelper::toObject(json_decode(json_encode($items), true));
                        $items2[$running_date] = $this->sumItems($items2[$running_date], null, $running_date);
                        $tags[$running_date] = $this->getTagsBalance($items2[$running_date]);
                        $running_date = date("Y-m-d", strtotime($running_date . " +1 " . $date_inteval));
                }

                return $tags;
        }

        /**
         * Calculate the sum of accounts per type
         * 
         * @return array The account types balances
         */
        public function getAccountTypesBalance()
        {
                $items = $this->getItems();

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('*, 0 as balance')
                        ->from('#__phmoney_account_types');
                $db->setQuery($query);
                $account_types = $db->loadObjectList('value');

                if (!empty($account_types)) {
                        foreach ($items as $item) {
                                $account_types[$item->account_type_value]->balance += $item->value_portfolio_total;
                                $account_types[$item->account_type_value]->portfolio_currency_symbol = $item->portfolio_currency_symbol;
                                $account_types[$item->account_type_value]->portfolio_currency_denom = $item->portfolio_currency_denom;
                                $account_types[$item->account_type_value]->account_type_value = $item->account_type_value;
                        }
                }

                //calculate percentage
                $total = 0;
                foreach ($account_types as $account_type) {
                        $total += abs($account_type->balance);
                }
                if ($total > 0) {
                        foreach ($account_types as &$account_type) {
                                $account_type->percentage = round(abs($account_type->balance) / $total * 100, 2);
                        }
                }

                return $account_types;
        }

        /**
         * Calculate the sum of accounts per tag
         * 
         * @return array The tags balances
         */
        public function getTagsBalance($items = null)
        {

                if (is_null($items)) {
                        $items = $this->getItems();
                }

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('*, 0 as balance')
                        ->from('#__tags as a');
                //join over tag map
                $query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                        . ' ON ' . $db->quoteName('tagmap.tag_id') . ' = ' . $db->quoteName('a.id')
                );
                $query->where($db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_phmoney.account'));

                $db->setQuery($query);
                $tags = $db->loadObjectList('id');

                if (!empty($tags)) {
                        foreach ($items as $item) {
                                if (!is_null($item->tag_id)) {
                                        $tags[$item->tag_id]->balance += $item->value_portfolio_total;
                                        $tags[$item->tag_id]->portfolio_currency_symbol = $item->portfolio_currency_symbol;
                                        $tags[$item->tag_id]->portfolio_currency_denom = $item->portfolio_currency_denom;
                                        $tags[$item->tag_id]->account_type_value = $item->account_type_value;
                                }
                        }
                }

                //calculate percentage
                $total = 0;
                foreach ($tags as $tag) {
                        $total += abs($tag->balance);
                }
                if ($total > 0) {
                        foreach ($tags as &$tag) {
                                $tag->percentage = round(abs($tag->balance) / $total * 100, 2);
                        }
                }

                return $tags;
        }

        /**
         * Set portfolio filter
         * @param int $account_id
         * @return void
         */
        public function setPortfolio($portfolio_id)
        {
                if ($portfolio_id === 0) {
                        return;
                }

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                $filters['portfolio'] = (string) $portfolio_id;
                $app->setUserState($this->context . '.filter', $filters);
        }

        /**
         * Save filter as json file
         */
        public function save_report()
        {
                $this->populateState();
                $app = Factory::getApplication();
                $json_arr = array();
                $json_arr['filter'] = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                $json_arr['list'] = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
                $json_str = json_encode($json_arr);

                $filename = $json_arr['filter']['report_type'] . '_' . date(DATE_COOKIE) . '.json';

                header("Content-Type: text/json");
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header("Content-Length: " . strlen($json_str));
                echo $json_str;
                exit;
        }

        /**
         * Read report file, assign filter and list
         * 
         * @param array $file
         * @return int
         */
        public function open_report($file)
        {

                if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                        $json_str = fread($handle, $file['size']);
                        fclose($handle);
                }

                $json_arr = json_decode($json_str, true);

                $app = Factory::getApplication();

                foreach ($json_arr as $label => $value) {
                        $app->setUserState($this->context . '.' . $label, $value);
                }
        }

        /**
         * Get the filter form
         *
         * @param   array    $data      data
         * @param   boolean  $loadData  load current data
         *
         * @return  \JForm|boolean  The \JForm object or false on error
         */
        public function getBatchForm()
        {
                $form = $this->loadForm($this->context . '.batch', 'batch_accounts');

                return $form;
        }

}
