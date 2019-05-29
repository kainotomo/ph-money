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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/../libraries/vendor/autoload.php';

use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

/**
 * Description of ImportsModel
 *
 */
class ImportsModel extends ListModel
{

        /**
         * Array of accounts frequent appearances in existing transactions.
         * It can be used calculate possible source and destination accounts
         *
         * @var array
         */
        protected $accounts_freq;

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
        protected function populateState($ordering = null, $direction = null)
        {
                parent::populateState($ordering, $direction);

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                if (!isset($filters['portfolio'])) {
                        $this->setState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                        $this->setState('filter.status', 'default');
                }
        }

        /**
         * Read the csv file and returns the total number of lines
         *
         * @return  mixed  An array of data items on success, false on failure.
         *
         */
        protected function getLinesFromFile()
        {

                $config = Factory::getApplication()->getConfig();
                $filename = $config->get('tmp_path') . '/' . File::makeSafe('import_splits.csv');

                $row = 0;
                if (!file_exists($filename)) {
                        return $row;
                }
                if (($handle = fopen($filename, "r")) !== FALSE) {
                        while (($line = fgets($handle)) !== FALSE) {
                                $row++;
                        }
                        fclose($handle);
                }

                return $row;
        }

        /**
         * Read the csv file and returns items
         *
         * @return  mixed  An array of data items on success, false on failure.
         *
         */
        protected function getItemsFromFile($limitstart = 0, $limit = 0)
        {

                $config = Factory::getApplication()->getConfig();
                $filename = $config->get('tmp_path') . '/' . File::makeSafe('import_splits.csv');

                $items = array();
                if (!file_exists($filename)) {
                        return $items;
                }
                $row = 1;
                if (($handle = fopen($filename, "r")) !== FALSE) {
                        while (($line = fgetcsv($handle)) !== FALSE) {
                                if (($row < $limitstart || $row > $limitstart + $limit) && $limit > 0) {
                                        $row++;
                                        continue;
                                }

                                $num = count($line);
                                $item = array();
                                $item[0] = $row;
                                for ($c = 0; $c < $num; $c++) {
                                        $item[$c + 1] = $line[$c];
                                }
                                $items[] = $item;
                                $row++;
                        }
                        fclose($handle);
                }

                return $items;
        }

        /**
         * Import selected items in database
         *
         * @param array $accounts Selected rows with source and destination account
         * @param array $headers The headers of the columns to import
         */
        public function ImportSelected($accounts)
        {
                $this->populateState();
                $portfolio_id = (int) $this->getState('filter.portfolio');

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->from('#__phmoney_portfolios as p')
                        ->where('p.id = ' . $portfolio_id);
                $query->select(
                                'c.denom'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS c ON c.id = p.currency_id');
                $db->setQuery($query);
                $currency_denom = (int) $db->loadResult();

                $items = $this->getItems();
                $items_selected = array();
                $transactions = array();
                foreach ($accounts as $account) {
                        foreach ($items as &$item) {
                                if ($item->id === $account['id']) {

                                        //define transaction
                                        $transaction = array();

                                        if ($account['source_account'] == $account['destination_account']) {
                                                $transaction['status'] = 'invalid_account';
                                                $transactions[] = $transaction;
                                                $item->status = 2;
                                                $item->message = Text::_('COM_PHMONEY_INVALID_ACCOUNT');
                                                continue;
                                        }

                                        $transaction['status'] = 'import';

                                        $transaction['portfolio_id'] = $portfolio_id;

                                        if (!is_null($item->title)) {
                                                $transaction['title'] = $item->title;
                                        }


                                        if (!is_null($item->num)) {
                                                $transaction['num'] = $item->num;
                                        }

                                        if (!is_null($item->description)) {
                                                $transaction['description'] = $item->description;
                                        }

                                        if (!is_null($item->post_date)) {
                                                $date_format = $this->getState('filter.date_format', 'Y-m-d');
                                                $transaction['post_date'] = date_create_from_format($date_format, $item->post_date);
                                                if ($transaction['post_date'] === false) {
                                                        $transaction['status'] = 'invalid_post_date';
                                                        $item->status = 2;
                                                        $item->message = Text::_('COM_PHMONEY_INVALID_DATE');
                                                } else {
                                                        $transaction['post_date'] = $transaction['post_date']->format("Y-m-d H:i:s");
                                                }
                                        }

                                        //define splits
                                        $split_source = array();
                                        $split_destination = array();

                                        $split_source['account_id'] = $account['source_account'];
                                        $split_destination['account_id'] = $account['destination_account'];
                                        $item->account_id_source = $account['source_account'];
                                        $item->account_id_destination = $account['destination_account'];

                                        if (is_numeric($item->value)) {
                                                $split_source['value'] = -$item->value * $currency_denom;
                                                $split_destination['value'] = $item->value * $currency_denom;
                                        } else {
                                                $split_source['value'] = false;
                                                $split_destination['value'] = false;
                                                $transaction['status'] = 'invalid_value';
                                                $item->status = 2;
                                                $item->message = Text::_('COM_PHMONEY_INVALID_VALUE');
                                        }

                                        $transaction['split_source'] = $split_source;
                                        $transaction['split_destination'] = $split_destination;
                                        $transaction['state'] = 1;
                                        $transaction['item'] = $item;

                                        unset($item->account_name_source);
                                        unset($item->account_name_destination);

                                        $transactions[] = $transaction;
                                        $items_selected[$item->id] = $item;

                                        break;
                                }
                        }
                }

                //add data in db
                foreach ($transactions as &$transaction) {
                        if ($transaction['status'] == 'import') {

                                //add transaction
                                $query->clear();
                                $query->insert('#__phmoney_transactions')
                                        ->set($query->qn('portfolio_id') . '=' . $query->q($transaction['portfolio_id']))
                                        ->set($query->qn('title') . '=' . $query->q($transaction['title']))
                                        ->set($query->qn('num') . '=' . $query->q($transaction['num']))
                                        ->set($query->qn('description') . '=' . $query->q($transaction['description']))
                                        ->set($query->qn('post_date') . '=' . $query->q($transaction['post_date']))
                                        ->set($query->qn('state') . '=' . $query->q($transaction['state']));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                //get transaction id
                                $query->clear();
                                $query->select('t.id')->from('#__phmoney_transactions as t')->order('t.id desc');
                                $db->setQuery($query);
                                $transaction_id = $db->loadResult();

                                //add split source
                                $query->clear();
                                $query->insert('#__phmoney_splits')
                                        ->set($query->qn('account_id') . '=' . $query->q($transaction['split_source']['account_id']))
                                        ->set($query->qn('value') . '=' . $query->q($transaction['split_source']['value']))
                                        ->set($query->qn('transaction_id') . '=' . $query->q($transaction_id));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                //add split destination
                                $query->clear();
                                $query->insert('#__phmoney_splits')
                                        ->set($query->qn('account_id') . '=' . $query->q($transaction['split_destination']['account_id']))
                                        ->set($query->qn('value') . '=' . $query->q($transaction['split_destination']['value']))
                                        ->set($query->qn('split_type_id') . '=' . $query->q($transaction['item']->split_type_id_destination))
                                        ->set($query->qn('transaction_id') . '=' . $query->q($transaction_id));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                $transaction['item']->status = 1;
                                $transaction['item']->message = $transaction_id;
                        }
                }

                //Update existing items with status and message
                foreach ($items_selected as $item) {
                        unset($item->account_type_source);
                        unset($item->account_type_destination);
                        $query->clear();
                        $query->update('#__phmoney_imports')
                                ->where('id = ' . (int) $item->id);
                        foreach ($item as $label => $value) {
                                if (!empty($value)) {
                                        $query->set($query->qn($label) . '=' . $query->q($value));
                                }
                        }
                        $db->setQuery($query);
                        $db->execute();
                }


                //prepare user message
                $success_count = 0;
                $fail_count = 0;
                foreach ($transactions as $transaction) {
                        if ($transaction['status'] === 'import') {
                                $success_count++;
                        } else {
                                $fail_count++;
                        }
                }

                parent::cleanCache();
                return Text::plural('COM_PHMONEY_IMPORT_RESULT', $success_count, $fail_count);
        }

        /**
         * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
         *
         * @return  \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
         *
         */
        public function getListQuery()
        {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select(
                        $this->getState(
                                'list.select', 'a.id, a.account_id_source, a.account_id_destination, a.split_type_id_destination, a.percent, a.post_date, a.title, a.num, a.description, a.value, a.status, a.message'
                        )
                );
                $query->from('#__phmoney_imports AS a');

                // Join over the source accounts.
                $query->select(
                                'ac_s.title AS account_name_source'
                        )
                        ->join('LEFT', '#__phmoney_accounts AS ac_s ON ac_s.id = a.account_id_source');

                // Join over the source account types.
                $query->select(
                                'act_s.value AS account_type_source'
                        )
                        ->join('LEFT', '#__phmoney_account_types AS act_s ON act_s.id = ac_s.account_type_id');

                // Join over the destination accounts.
                $query->select(
                                'ac_d.title AS account_name_destination'
                        )
                        ->join('LEFT', '#__phmoney_accounts AS ac_d ON ac_d.id = a.account_id_destination');

                // Join over the destination account types.
                $query->select(
                                'act_d.value AS account_type_destination'
                        )
                        ->join('LEFT', '#__phmoney_account_types AS act_d ON act_d.id = ac_d.account_type_id');

                // Filter on the portfolio.
                $portfolio = $this->getState('filter.portfolio');
                if (!empty($portfolio)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio);
                } else {
                        $query->where('a.portfolio_id = -1');
                }

                // Filter by status
                $status = (string) $this->getState('filter.status');
                if (is_numeric($status)) {
                        $query->where('a.status = ' . (int) $status);
                }
                // Add the list ordering clause
                $listOrdering = $this->getState('list.ordering', 'a.id');
                $listDirn = $db->escape($this->getState('list.direction', 'ASC'));

                if ($listOrdering == 'a.id') {
                        //$query->order($db->escape($listOrdering) . ' ' . $listDirn);
                } else {
                        //$query->order('a.rgt desc' . ', ' . $listOrdering . ' ' . $listDirn);
                        //$query->order($db->escape($listOrdering) . ' ' . $listDirn . ', ' . 'a.rgt desc');
                }
                $query->order($db->escape($listOrdering) . ' ' . $listDirn);

                return $query;
        }

        /**
         * Estimate source and destination accounts
         */
        public function estimate()
        {

                //get portfolio currency denom
                $this->populateState();
                $portfolio_id = (int) $this->getState('filter.portfolio');

                //get db
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->from('#__phmoney_portfolios as p')
                        ->where('p.id = ' . $portfolio_id);
                $query->select(
                                'c.denom'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS c ON c.id = p.currency_id');
                $db->setQuery($query);
                $currency_denom = (int) $db->loadResult();

                //get accounts possibilities
                $query->clear();
                $query->select('COUNT(*) AS appearances, title')
                        ->from('#__phmoney_transactions')
                        ->group('title')
                        ->order('appearances desc');
                $db->setQuery($query);
                $this->accounts_freq = $db->loadAssocList();

                $items = $this->_getList($this->_getListQuery());

                foreach ($items as $item) {
                        $transaction = ArrayHelper::fromObject($item);
                        if (is_numeric($transaction['value'])) {
                                $transaction['value_int'] = $transaction['value'] * $currency_denom;
                                $transaction = $this->estimateAccounts($transaction);

                                if ($transaction !== FALSE) {
                                        unset($transaction['value_int']);

                                        $query->clear();
                                        $query->update('#__phmoney_imports')
                                                ->where('id = ' . $transaction['id']);
                                        $query->set($query->qn('account_id_source') . '=' . $query->q($transaction['account_id_source']));
                                        if ($transaction['account_type_destination'] != 'share') {
                                                $query->set($query->qn('account_id_destination') . '=' . $query->q($transaction['account_id_destination']));
                                        }
                                        $query->set($query->qn('percent') . '=' . $query->q($transaction['percent']));
                                        $db->setQuery($query);
                                        $db->execute();
                                }
                        }
                }

                parent::cleanCache();
        }

        /**
         * Based on title, number of appearances estimates the source and destination account
         *
         * @param array $transaction An array with transaction data
         * @return array An array with the transaction data, including the source and destination account, if possible. or false if estimation not found
         */
        protected function estimateAccounts($transaction)
        {

                $percent = 0;
                $title = $this->accounts_freq[0]['title'];
                foreach ($this->accounts_freq as $value) {
                        //$distance = levenshtein($transaction['title'], $value['title']);
                        similar_text($transaction['title'], $value['title'], $percent_new);
                        if ($percent_new > $percent) {
                                $percent = $percent_new;
                                $title = $value['title'];
                        }
                }

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('s.account_id, s.id, s.value, s.transaction_id')
                        ->from('#__phmoney_splits as s')
                        ->join('LEFT', '#__phmoney_transactions AS t ON t.id = s.transaction_id')
                        ->where('t.title LIKE ' . $query->q($title))
                        ->order('t.post_date DESC, s.id ASC');

                $db->setQuery($query);
                $possible_splits = $db->loadAssocList('id');

                if (!empty($possible_splits)) {
                        $difference_source = 99999999;
                        $difference_destination = 99999999;
                        foreach ($possible_splits as $possible_split) {
                                $difference_new = abs(-$transaction['value_int'] - $possible_split['value']);
                                if ($difference_source > $difference_new) {
                                        $difference_source = $difference_new;
                                        $split_id_source = $possible_split['id'];
                                }

                                $difference_new = abs($transaction['value_int'] - $possible_split['value']);
                                if ($difference_destination > $difference_new) {
                                        $difference_destination = $difference_new;
                                        $split_id_destination = $possible_split['id'];
                                }
                        }

                        $transaction['percent'] = $percent;
                        $transaction['account_id_source'] = $possible_splits[$split_id_source]['account_id'];
                        $transaction['account_id_destination'] = $possible_splits[$split_id_destination]['account_id'];

                        return $transaction;
                }

                return false;
        }

        /**
         * Import all items in imports table to database
         */
        public function ImportAll()
        {
                $this->populateState();
                $portfolio_id = (int) $this->getState('filter.portfolio');

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->from('#__phmoney_portfolios as p')
                        ->where('p.id = ' . $portfolio_id);
                $query->select(
                                'c.denom'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS c ON c.id = p.currency_id');
                $db->setQuery($query);
                $currency_denom = (int) $db->loadResult();

                $items = $this->_getList($this->_getListQuery());
                $transactions = array();
                foreach ($items as &$item) {
                        if (!is_null($item->account_id_source) && !is_null($item->account_id_destination)) {

                                //define transaction
                                $transaction = array();

                                if ($item->account_id_source == $item->account_id_destination) {
                                        $transaction['status'] = 'invalid_account';
                                        $transactions[] = $transaction;
                                        $item->status = 2;
                                        $item->message = Text::_('COM_PHMONEY_INVALID_ACCOUNT');
                                        continue;
                                }

                                $transaction['status'] = 'import';

                                $transaction['portfolio_id'] = $portfolio_id;

                                if (!is_null($item->title)) {
                                        $transaction['title'] = $item->title;
                                }


                                if (!is_null($item->num)) {
                                        $transaction['num'] = $item->num;
                                }

                                if (!is_null($item->description)) {
                                        $transaction['description'] = $item->description;
                                }

                                if (!is_null($item->post_date)) {
                                        $date_format = $this->getState('filter.date_format', 'Y-m-d');
                                        $transaction['post_date'] = date_create_from_format($date_format, $item->post_date);
                                        if ($transaction['post_date'] === false) {
                                                $transaction['status'] = 'invalid_post_date';
                                                $item->status = 2;
                                                $item->message = Text::_('COM_PHMONEY_INVALID_DATE');
                                        } else {
                                                $transaction['post_date'] = $transaction['post_date']->format("Y-m-d H:i:s");
                                        }
                                }

                                //define splits
                                $split_source = array();
                                $split_destination = array();

                                $split_source['account_id'] = $item->account_id_source;
                                $split_destination['account_id'] = $item->account_id_destination;

                                if (is_numeric($item->value)) {
                                        $split_source['value'] = -$item->value * $currency_denom;
                                        $split_destination['value'] = $item->value * $currency_denom;
                                } else {
                                        $split_source['value'] = false;
                                        $split_destination['value'] = false;
                                        $transaction['status'] = 'invalid_value';
                                        $item->status = 2;
                                        $item->message = Text::_('COM_PHMONEY_INVALID_VALUE');
                                }

                                $transaction['split_source'] = $split_source;
                                $transaction['split_destination'] = $split_destination;
                                $transaction['state'] = 1;
                                $transaction['item'] = $item;

                                unset($item->account_name_source);
                                unset($item->account_name_destination);

                                $transactions[] = $transaction;
                        }
                }

                //add data in db
                foreach ($transactions as &$transaction) {
                        if ($transaction['status'] == 'import') {

                                //add transaction
                                $query->clear();
                                $query->insert('#__phmoney_transactions')
                                        ->set($query->qn('portfolio_id') . '=' . $query->q($transaction['portfolio_id']))
                                        ->set($query->qn('title') . '=' . $query->q($transaction['title']))
                                        ->set($query->qn('num') . '=' . $query->q($transaction['num']))
                                        ->set($query->qn('description') . '=' . $query->q($transaction['description']))
                                        ->set($query->qn('post_date') . '=' . $query->q($transaction['post_date']))
                                        ->set($query->qn('state') . '=' . $query->q($transaction['state']));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                //get transaction id
                                $query->clear();
                                $query->select('t.id')->from('#__phmoney_transactions as t')->order('t.id desc');
                                $db->setQuery($query);
                                $transaction_id = $db->loadResult();

                                //add split source
                                $query->clear();
                                $query->insert('#__phmoney_splits')
                                        ->set($query->qn('account_id') . '=' . $query->q($transaction['split_source']['account_id']))
                                        ->set($query->qn('value') . '=' . $query->q($transaction['split_source']['value']))
                                        ->set($query->qn('transaction_id') . '=' . $query->q($transaction_id));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                //add split destination
                                $query->clear();
                                $query->insert('#__phmoney_splits')
                                        ->set($query->qn('account_id') . '=' . $query->q($transaction['split_destination']['account_id']))
                                        ->set($query->qn('value') . '=' . $query->q($transaction['split_destination']['value']))
                                        ->set($query->qn('transaction_id') . '=' . $query->q($transaction_id));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $ex) {
                                        $transaction['status'] = $ex->getMessage();
                                        $transaction['item']->status = 2;
                                        $transaction['item']->message = $ex->getMessage();
                                        continue;
                                }

                                $transaction['item']->status = 1;
                                $transaction['item']->message = $transaction_id;
                        }
                }

                //Update existing items with status and message
                foreach ($items as $item) {
                        $query->clear();
                        $query->update('#__phmoney_imports')
                                ->where('id = ' . (int) $item->id);
                        foreach ($item as $label => $value) {
                                if (!empty($value)) {
                                        $query->set($query->qn($label) . '=' . $query->q($value));
                                }
                        }
                        $db->setQuery($query);
                        $db->execute();
                }


                //prepare user message
                $success_count = 0;
                $fail_count = 0;
                foreach ($transactions as $transaction) {
                        if ($transaction['status'] === 'import') {
                                $success_count++;
                        } else {
                                $fail_count++;
                        }
                }

                return Text::plural('COM_PHMONEY_IMPORT_RESULT', $success_count, $fail_count);
        }

        /**
         * Retrieve prices from Yahoo
         */
        public function download_prices()
        {

                $table_accounts = $this->getTable('Account');

                //First download prices
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
                $query->where("a.code > ''");
                // Join over the account type
                $query->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = a.account_type_id')
                        ->where('at.value LIKE ' . $query->q('share'));

                $query->select('c.symbol AS currency_symbol, c.denom AS currency_denom')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id');

                $this->populateState();
                $portfolio_id = $this->getState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                if (!empty($portfolio_id)) {
                        $query->where('a.portfolio_id = ' . (int) $portfolio_id);
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
                
                //get split_types
                $query->clear();
                $query->select('id')
                        ->from('#__phmoney_split_types')
                        ->where('value LIKE ' . $db->quote('price'));
                $db->setQuery($query);
                $split_type_id = $db->loadResult();

                $imports = array();
                foreach ($quotes as $quote) {
                        $regularMarketPrice = $quote->getRegularMarketPrice();
                        if (is_float($regularMarketPrice)) {
                                //calculate and save statistics
                                $table_accounts->load($item->id);
                                $data = null;
                                $table_accounts->calculateIntrinsicValue($data, $quote);
                                $params = ArrayHelper::fromObject(json_decode($table_accounts->params));
                                foreach ($data['params'] as $key => $value) {
                                        $params[$key] = $value;
                                }
                                $table_accounts->params = json_encode($params);
                                if ($table_accounts->params !== false) {
                                        $table_accounts->store();
                                }

                                $item = $items[$quote->getSymbol()];
                                $item->regularMarketPrice = $regularMarketPrice;

                                $query->clear();
                                $query->select(('SUM(value) as value, SUM(shares) as shares'))
                                        ->from('#__phmoney_splits')
                                        ->where('account_id = ' . $item->id)
                                        ->where('state = 1');
                                $db->setQuery($query);
                                $sum = $db->loadObject();

                                if ((float) $sum->shares > 0) {
                                        $value = (float) $sum->value / $item->currency_denom;
                                        $shares = (float) $sum->shares;
                                        $share_value = $regularMarketPrice * $shares;
                                        $value_diff = PhmoneyHelper::roundMoney($share_value - $value, $item->currency_denom);

                                        if ($value_diff != 0) {
                                                $import = array();

                                                $import['portfolio_id'] = $portfolio_id;
                                                $import['account_id_destination'] = $item->id;
                                                $import['split_type_id_destination'] = $split_type_id;
                                                $import['post_date'] = date("Y-m-d");
                                                if ($value_diff > 0) {
                                                        $import['title'] = Text::plural('COM_PHMONEY_PRICE_GAIN', $item->code, $item->currency_symbol, $regularMarketPrice);
                                                } else {
                                                        $import['title'] = Text::plural('COM_PHMONEY_PRICE_LOSS', $item->code, $item->currency_symbol, $regularMarketPrice);
                                                }
                                                $import['value'] = $value_diff;
                                                $import['status'] = 0;

                                                $imports[$item->id] = $import;
                                        }
                                }
                        }
                }

                //truncate imports table
                $query->clear();
                $db->setQuery("TRUNCATE #__phmoney_imports;");
                $db->execute();

                if (!empty($imports)) {

                        //add data in db
                        foreach ($imports as $import) {
                                $query->clear();
                                $query->insert('#__phmoney_imports');
                                foreach ($import as $label => $value) {
                                        $query->set($query->qn($label) . '=' . $query->q($value));
                                }
                                $db->setQuery($query);
                                $db->execute();
                        }
                }

                parent::cleanCache();
        }

                }
