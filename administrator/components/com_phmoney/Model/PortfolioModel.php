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
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;

/**
 * Description of PortfolioModel
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class PortfolioModel extends AdminModel
{

        public function getForm($data = array(), $loadData = true)
        {

                // Get the form.
                $form = $this->loadForm('com_phmoney.portfolio', 'portfolio', array('control' => 'jform', 'load_data' => $loadData));

                if (empty($form)) {
                        return false;
                }

                return $form;
        }

        /**
         * Method to get the data that should be injected in the form.
         *
         * @return  mixed  The data for the form.
         *
         */
        protected function loadFormData()
        {
                // Check the session for previously entered form data.
                $app = Factory::getApplication();
                $data = $app->getUserState('com_phmoney.edit.portfolio.data', array());
                $user = Factory::getUser();

                if (empty($data)) {
                        $data = $this->getItem();

                        // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Portfolio Manager: Portfolios
                        if ($this->getState('portfolio.id') == 0) {
                                $data->set('user_id', $user->id);
                                //calculate first day of this year date
                                $data->params['start_date'] = date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y")));
                                $data->params['end_date'] = date("Y-m-d", mktime(0, 0, 0, 12, 31, date("Y")));
                        }
                }

                // If there are params fieldsets in the form it will fail with a registry object
                if (isset($data->params) && $data->params instanceof Registry) {
                        $data->params = $data->params->toArray();
                }

                $this->preprocessData('com_phmoney.portfolio', $data);

                return $data;
        }

        /**
         * Method to change the home state of one or more items.
         *
         * @param   array    &$pks   A list of the primary keys to change.
         * @param   integer  $value  The value of the home state.
         *
         * @return  boolean  True on success.
         *
         */
        public function setDefault($pk, $value = 1)
        {

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $user = Factory::getUser();

                $query->select('id')
                        ->from('#__phmoney_portfolios')
                        ->where('user_id  = ' . (int) $user->id)
                        ->where('user_default = 1');
                $db->setQuery($query);
                $current = $db->loadResult();

                if (empty($current)) {
                        $query->clear();
                        $query->update('#__phmoney_portfolios')
                                ->where('id = ' . $pk)
                                ->set('published = 1')
                                ->set('user_default = 1');
                        $db->setQuery($query);
                        $db->execute();
                } else {
                        if ($value == 1) {
                                $query->clear();
                                $query->update('#__phmoney_portfolios')
                                        ->where('user_id  = ' . (int) $user->id)
                                        ->set('user_default = 0');
                                $db->setQuery($query);
                                $db->execute();

                                $query->clear();
                                $query->update('#__phmoney_portfolios')
                                        ->where('id = ' . $pk)
                                        ->set('user_default = 1');
                                $db->setQuery($query);
                                $db->execute();
                        }
                }

                // Clean the cache
                $this->cleanCache();

                return true;
        }

        public function getItem($pk = null)
        {
                $item = parent::getItem($pk);

                if ($item) {
                        if (!empty($item->id)) {
                                // Convert the params field to an array.
                                $params = new Registry($item->params);
                                $item->params = $params->toArray();
                        }
                }

                return $item;
        }

        public function validate($form, $data, $group = null)
        {

                $data = parent::validate($form, $data, $group);

                if (!$data) {
                        return false;
                }

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

                switch ($data['params']['relative_start']) {
                        case '1': //today
                                $data['params']['start_date'] = date("Y-m-d");
                                break;
                        case '2': //start of this month
                                $data['params']['start_date'] = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
                                break;
                        case '3': //start of previous month
                                $data['params']['start_date'] = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                                break;
                        case '4': //start of this quarter                                
                                $quarter = strtotime('first day of ' . $quarters[date["m"]], time());
                                $data['params']['start_date'] = date('Y-m-d', $quarter);
                                break;
                        case '5': //start of previous quarter                                          
                                $quarter = strtotime('first day of ' . $quarters[date["m"]], time());
                                $date = date_create(date('Y-m-d', $quarter));
                                $date_time = date_sub($date, date_interval_create_from_date_string("3 months"));
                                $data['params']['start_date'] = $date_time->format('Y-m-d');
                                break;
                        case '6': //start of this year
                                $data['params']['start_date'] = date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y")));
                                break;
                        case '7': //start of previous year
                                $data['params']['start_date'] = date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y") - 1));
                                break;

                        default: //absolute
                                break;
                }

                switch ($data['params']['relative_end']) {
                        case '1': //today
                                $data['params']['end_date'] = date("Y-m-d");
                                break;
                        case '2': //last day of this month
                                $month_end = strtotime('last day of this month', time());
                                $data['params']['end_date'] = date('Y-m-d', $month_end);
                                break;
                        case '3': //last day of previous month
                                $previous_month_end = strtotime('last day of previous month', time());
                                $data['params']['end_date'] = date('Y-m-d', $previous_month_end);
                                break;
                        case '4': //last day of this quarter
                                $quarter = strtotime('last day of ' . $quarters[date["m"]], time());
                                $data['params']['end_date'] = date('Y-m-d', $quarter);
                                break;
                        case '5': //last day of previous quarter
                                $quarter = strtotime('last day of ' . $quarters[date["m"]], time());
                                $date = date_create(date('Y-m-d', $quarter));
                                $date_time = date_sub($date, date_interval_create_from_date_string("3 months"));
                                $data['params']['end_date'] = $date_time->format('Y-m-d');
                                break;
                        case '6': //end of this year
                                $data['params']['end_date'] = date("Y-m-d", mktime(0, 0, 0, 12, 31, date("Y")));
                                break;
                        case '7': //end of previous year
                                $data['params']['end_date'] = date("Y-m-d", mktime(0, 0, 0, 12, 31, date("Y") - 1));
                                break;

                        default: //absolute
                                break;
                }

                return $data;
        }

        /**
         * Method to save the form data.
         *
         * @param   array  $data  The form data.
         *
         * @return  boolean  True on success, False on error.
         *
         */
        public function save($data)
        {

                if (isset($data['params']) && is_array($data['params'])) {
                        $params = new Registry($data['params']);
                        $data['params'] = (string) $params;
                }

                $result = parent::save($data);

                if ($result) {

                        $pk = $this->getState('portfolio.id');
                        $this->setDefault($pk, $data['user_default']);
                }
                return true;
        }

        /**
         * Upload a file in tmp folder
         * 
         * @param file $file         
         */
        protected function upload($file)
        {

                $config = Factory::getApplication()->getConfig();
                $tmp_dest = $config->get('tmp_path') . '/' . File::makeSafe($file['name']);
                $tmp_src = $file['tmp_name'];

                jimport('joomla.filesystem.file');
                if (!File::upload($tmp_src, $tmp_dest, false, true)) {
                        return false;
                }

                return $tmp_dest;
        }

        /**
         * Import GnuCash accounts csv file
         * 
         * @param file $file
         * @param array $data portfolio data
         */
        public function import_gnucash_csv_accounts($file, $data)
        {

                $this->populateState();

                $filename = PhmoneyHelper::upload($file);
                if ($filename === false) {
                        return false;
                }

                $headers = array();
                $rows = array();
                $row = 0;
                if (($handle = fopen($filename, "r")) !== FALSE) {
                        while (($account = fgetcsv($handle)) !== FALSE) {
                                $num = count($account);
                                $new_row = array();
                                for ($c = 0; $c < $num; $c++) {
                                        if ($row == 0) {
                                                $headers[$c] = $account[$c];
                                        } else {
                                                $new_row[$headers[$c]] = $account[$c];
                                        }
                                }
                                if ($row > 0) {
                                        $rows[$row] = $this->convert_gnucash_csv_account($new_row, $data);
                                }
                                $row++;
                        }
                        fclose($handle);
                }
                return $rows;
        }

        /**
         * Convert csv data to model data
         * 
         * @param array $data
         * @param array $portfolio_data
         * @return array
         */
        protected function convert_gnucash_csv_account($data, $portfolio_data)
        {

                $result = array();

                $result['id'] = '0';

                $result['title'] = $data['name'];

                switch (strtolower($data['type'])) {
                        case 'stock':
                                $result['account_type_id'] = '2';
                                break;
                        case 'liability':
                        case 'credit':
                        case 'payable':
                                $result['account_type_id'] = '3';
                                break;
                        case 'equity':
                                $result['account_type_id'] = '4';
                                break;
                        case 'income':
                                $result['account_type_id'] = '5';
                                break;
                        case 'expense':
                                $result['account_type_id'] = '6';
                                break;

                        default:
                                $result['account_type_id'] = '1';
                                break;
                }

                //find currency
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('a.id')
                        ->from('#__phmoney_currencys as a')
                        ->where('a.code LIKE ' . $db->q($data['commoditym']));
                $db->setQuery($query);
                $currency_id = $db->loadResult();
                if (is_null($currency_id)) {
                        $result['currency_id'] = $portfolio_data['currency_id'];
                } else {
                        $result['currency_id'] = $currency_id;
                }

                $level = substr_count($data['full_name'], ':');
                if ($level) {
                        $full_name = explode(':', $data['full_name']);
                        $parent_name = $full_name[$level - 1];
                        $result['parent_id'] = $parent_name;
                } else {
                        $result['parent_id'] = '1';
                }

                $result['published'] = '1';

                $result['code'] = $data['code'];

                $result['note'] = '';

                $result['alias'] = '';

                $result['portfolio_id'] = $this->getState('portfolio.id');

                $result['description'] = $data['description'];

                $result['asset_id'] = '';

                $result['lft'] = '';

                $result['rgt'] = '';

                $result['level'] = '';

                $result['path'] = '';

                $result['checked_out'] = null;

                $result['checked_out_time'] = null;

                $result['params'] = array(
                        'address' => '',
                        'country' => '',
                        'sector' => '',
                        'industry' => '',
                        'local_inde' => ''
                );

                return $result;
        }

        /**
         * Import GnuCash transactions csv file
         * 
         * @param file $file
         * @param array $data portfolio data
         */
        public function import_gnucash_csv_trxns($file, $data)
        {

                $this->populateState();

                $filename = PhmoneyHelper::upload($file);
                if ($filename === false) {
                        return false;
                }

                $headers = array();
                $rows = array();
                $row = 0;
                if (($handle = fopen($filename, "r")) !== FALSE) {
                        while (($account = fgetcsv($handle)) !== FALSE) {
                                $num = count($account);
                                $new_row = array();
                                for ($c = 0; $c < $num; $c++) {
                                        if ($row == 0) {
                                                $headers[$c] = $account[$c];
                                        } else {
                                                $new_row[$headers[$c]] = $account[$c];
                                        }
                                }
                                if ($row == 0) {
                                        $row++;
                                        continue;
                                }
                                if ($row > 0) {
                                        if ($new_row['Type'] === 'T') { //transaction                                                
                                                $row++;
                                                $row_split = 0;
                                                $rows[$row] = $this->convert_gnucash_csv_trxn($new_row, $data);
                                        } elseif ($new_row['Type'] === 'S') { //split
                                                $rows[$row]['splits']['splits' . $row_split] = $this->convert_gnucash_csv_split($new_row, $data);
                                                $row_split++;
                                        }
                                }
                        }
                        fclose($handle);
                }

                return $rows;
        }

        /**
         * Method that exports accounts of a specific portfolio to cvs file.
         * 
         * @param integer $portfolio_id The portfolio id.
         * @return boolean 
         */
        public function export_accounts($portfolio_id)
        {

                $this->populateState();
                $db = $this->getDbo();

                $query = $db->getQuery(true);

                $query->select('a.id AS account_id, a.title AS title, a.path AS full_title, '
                                . 'at.value AS acc_type, c.code AS currency, a.code AS code, a.note AS note, a.description AS description')
                        ->from('#__phmoney_accounts AS a')
                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = a.account_type_id')
                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id')
                        ->where('a.portfolio_id = ' . (int) $portfolio_id);

                $db->setQuery($query);
                $result = $db->loadAssocList();

                if ($result == null) {
                        return false;
                }

                $array = ArrayHelper::fromObject($result);

                $this->create_csv_file($array, 0);
                return true;
        }

        /**
         * Method that exports transactions of a specific portfolio to cvs file.
         * 
         * @param integer $portfolio_id
         * @return boolean
         */
        public function export_transactions($portfolio_id)
        {
                $this->populateState();
                $db = $this->getDbo();

                $query = $db->getQuery(true);

                $query->select('t.id AS transaction_id, t.post_date AS date, t.title AS title, s.id AS split_id, s.description AS description, '
                                . 'a.title AS account_name, a.path AS path, s.value AS value, s.rate AS rate, s.shares AS shares, s.price AS price')
                        ->from('#__phmoney_splits AS s')
                        ->join('LEFT', $db->quoteName('#__phmoney_transactions') . ' AS t ON t.id = s.transaction_id')
                        ->join('LEFT', $db->quoteName('#__phmoney_accounts') . ' AS a ON a.id = s.account_id')
                        ->where('t.portfolio_id = ' . (int) $portfolio_id);

                $db->setQuery($query);
                $result = $db->loadAssocList();

                if ($result == null) {
                        return false;
                }

                $array = ArrayHelper::fromObject($result);

                $this->create_csv_file($array, 1);
                return true;
        }

        /**
         * Convert array of data to csv file.
         * 
         * @param array $array Array that contains the data of accounts/transactions of a portfolio.
         */
        protected function create_csv_file($array, $flag)
        {

                // Output headers so that the file is downloaded rather than displayed
                header('Content-type: text/csv');

                if ($flag == 0) {
                        header('Content-Disposition: attachment; filename="Accounts_PHMoney.csv"');
                } elseif ($flag == 1) {
                        header('Content-Disposition: attachment; filename="Transactions_PHMoney.csv"');
                }

                // Do not cache the file
                header('Pragma: no-cache');
                header('Expires: 0');

                // Create a file pointer connected to the output stream
                $file = fopen('php://output', 'w');

                // Send the column headers
                if ($flag == 0) {
                        fputcsv($file, array('id', 'title', 'path', 'account_type', 'currency', 'code', 'note', 'description'));
                } elseif ($flag == 1) {
                        fputcsv($file, array('transaction_id', 'post_date', 'title', 'id', 'description', 'account_title', 'path', 'value', 'rate', 'shares', 'price'));
                }

                // Output each row of the data
                foreach ($array as $row) {
                        fputcsv($file, $row);
                }

                exit();
        }

        /**
         * Convert csv data to model data
         * 
         * @param array $data
         * @param array $portfolio_data
         * @return array
         */
        protected function convert_gnucash_csv_trxn($data, $portfolio_data)
        {

                $result = array();

                $result['id'] = '0';

                $result['portfolio_id'] = $portfolio_data['id'];

                $result['title'] = $data['Description'];

                $date = \Joomla\CMS\Date\Date::createFromFormat('d/m/Y', $data['Date']);
                $result['post_date'] = $date->format('Y-m-d H:i:s');

                $result['num'] = $data['Number'];

                $result['description'] = $data['Memo'];

                $result['state'] = '1';

                return $result;
        }

        /**
         * Convert csv data to model data
         * 
         * @param array $data
         * @param array $portfolio_data
         * @return array
         */
        protected function convert_gnucash_csv_split($data, $portfolio_data)
        {

                $result = array();

                $result['id'] = '0';

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('a.id')
                        ->from('#__phmoney_accounts as a')
                        ->where('a.portfolio_id = ' . (int) $portfolio_data['id'])
                        ->where('a.title LIKE ' . $db->q($data['Category']));
                $db->setQuery($query);
                $result['account_id'] = $db->loadResult();

                $query->clear();
                $query->select('a.denom')
                        ->from('#__phmoney_currencys as a')
                        ->where('a.id = ' . $portfolio_data['currency_id']);
                $db->setQuery($query);
                $denom = $db->loadResult();

                $value = str_replace(',', '', $data['To Num.']);
                $value = str_replace('(', '-', $value);
                $value = str_replace(')', '', $value);
                if (empty($value)) {
                        $value = str_replace(',', '', $data['From Num.']);
                        $value = str_replace('(', '-', $value);
                        $value = str_replace(')', '', $value);
                }
                $result['value'] = $value * (int) $denom;

                $result['description'] = $data['Memo'];

                switch ($data['Reconcile']) {
                        case 'Y':
                                $result['reconcile_state'] = '1';
                                break;

                        default:
                                $result['reconcile_state'] = '0';
                                break;
                }

                $result['rate'] = '1';

                $result['shares'] = '0';

                return $result;
        }

        /**
         * Import GnuCash transactions csv file
         * 
         * @param array $portfolio portfolio portfolio
         * @param int $limit The limit of the result set
         * @param int $offset The offset of the result set
         * 
         * @return int Number of total transactions
         */
        public function import_gnucash_db_trxns($portfolio, $limit, $offset)
        {
                $db_remote = $this->getDatabaseConnection($portfolio);
                $query_remote = $db_remote->getQuery(true);
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query_remote->select('t.guid, t.num, t.post_date, t.description AS title')
                        ->from('transactions AS t')
                        ->setLimit($limit, $offset);
                $db_remote->setQuery($query_remote);
                $transactions = $db_remote->loadAssocList();

                //get split_types
                $query->clear();
                $query->select('id, value')
                        ->from('#__phmoney_split_types');
                $db->setQuery($query);
                $split_type_ids = $db->loadObjectList('value');
                foreach ($transactions as $key => &$transaction) {

                        //do not import already imported transactions
                        $query->clear();
                        $query->select('a.id')
                                ->where('a.attribs LIKE ' . $db->quote('%"gnucash_guid":"' . $transaction['guid'] . '"%'))
                                ->where('a.portfolio_id = ' . $db->quote($portfolio->id))
                                ->from('#__phmoney_transactions as a');
                        $db->setQuery($query);
                        if (!empty($db->loadResult())) {
                                unset($transactions[$key]);
                                continue;
                        }

                        $transaction['id'] = '0';
                        $transaction['portfolio_id'] = $portfolio->id;
                        $transaction['state'] = '1';
                        $transaction['attribs'] = ['gnucash_guid' => $transaction['guid']];
                        unset($transaction['guid']);

                        $query_remote->clear();
                        $query_remote->select('s.guid, s.memo AS description, s.reconcile_state, s.value_num AS value, s.quantity_num / s.quantity_denom AS shares, s.account_guid')
                                ->from('splits AS s');
                        $query_remote->select('a.commodity_guid AS commodity_guid')
                                ->join('LEFT', $db->quoteName('accounts') . ' AS a ON a.guid = s.account_guid');
                        $query_remote->select('c.fraction AS commodity_fraction')
                                ->join('LEFT', $db->quoteName('commodities') . ' AS c ON c.guid = a.commodity_guid');
                        $query_remote->join('LEFT', $db->quoteName('transactions') . ' AS t ON t.guid = s.tx_guid')
                                ->where('t.guid = ' . $db->q($transaction['attribs']['gnucash_guid']));
                        $db_remote->setQuery($query_remote);
                        $splits = $db_remote->loadAssocList();
                        
                        foreach ($splits as &$split) {

                                $split['id'] = '0';
                                $query->clear();
                                $query->select('a.id')
                                        ->from('#__phmoney_accounts as a')
                                        ->where('a.portfolio_id = ' . (int) $portfolio->id)
                                        ->where('a.params LIKE ' . $db->quote('%"gnucash_guid":"' . $split['account_guid']. '"%'));
                                $query->select('type.value AS account_type')
                                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS type ON type.id = a.account_type_id');
                                $db->setQuery($query);
                                $account = $db->loadObject();
                                $split['account_id'] = $account->id;
                                $split['attribs'] = ['gnucash_guid' => $split['guid']];
                                $split['attribs'] = json_encode($split['attribs']);
                                unset($split['guid']);
                                unset($split['account_guid']);

                                switch ($split['reconcile_state']) {
                                        case 'y':
                                                $split['reconcile_state'] = '1';
                                                break;

                                        default:
                                                $split['reconcile_state'] = '0';
                                                break;
                                }

                                if ($account->account_type === 'share') {
                                        if ((int) $split['shares'] === 0) {
                                                $split['split_type_id'] = $split_type_ids['dividend']->id;
                                        }
                                        if ((int) $split['shares'] < 0) {
                                                $split['split_type_id'] = $split_type_ids['sell']->id;
                                        }
                                        if ((int) $split['shares'] > 0) {
                                                $split['split_type_id'] = $split_type_ids['buy']->id;
                                        }
                                } else {
                                        $split['value'] = $split['shares'] * $split['commodity_fraction'];
                                        $split['shares'] = '0';
                                }
                                
                                unset($split['commodity_guid']);
                                unset($split['commodity_fraction']);
                        }

                        $transaction['attribs'] = json_encode($transaction['attribs']);
                        $transaction['splits'] = $splits;
                }
                return $transactions;
        }

        /**
         * Count the total number of transactions from source database
         * 
         * @param array $portfolio The portfolio data
         * 
         * @return int Total number of transactions to copy
         */
        public function getTotalTransactions($portfolio)
        {
                $db_remote = $this->getDatabaseConnection($portfolio);
                $query_remote = $db_remote->getQuery(true);
                $query_remote->select('COUNT(t.guid)')
                        ->from('transactions AS t');
                $db_remote->setQuery($query_remote);
                $result = $db_remote->loadResult();
                return $result;
        }

        /**
         * Method to change the published state of one or more records.
         *
         * @param   array    &$pks   A list of the primary keys to change.
         * @param   integer  $value  The value of the published state.
         *
         * @return  boolean  True on success.
         *
         */
        public function publish(&$pks, $value = 1)
        {

                //set the new status for each portfolio
                $result = parent::publish($pks, $value);

                //check default when unpublishing portfolios
                if ($result && $value != 1) {
                        $data = array();

                        foreach ($pks as $pk) {
                                $item = $this->getItem($pk);
                                $itemTable = ArrayHelper::fromObject($item);

                                //check if the default portfolio got unpublished 
                                if ($itemTable['user_default'] == 1) {
                                        $data = $itemTable;
                                        break;
                                }
                        }

                        //set another portfolio as default 
                        if ($data != null) {
                                $result2 = $this->setDefaultPortfolio1();
                                //if result2 is false, there are no portfolios with status 1 in db table
                        }
                } else if ($result && $value == 1) {
                        $result3 = $this->setDefaultPortfolio2($pks[0]);
                }

                return $result;
        }

        /**
         * Method to set automatically a default portfolio when the current 
         * has been deleted.
         * 
         * @return  boolean  True if successful, false if an error occurs.
         */
        protected function setDefaultPortfolio1()
        {
                $this->populateState();
                $db = $this->getDbo();
                $user = Factory::getUser();

                $query = $db->getQuery(true);

                //unset all portfolios from default
                $query->update('#__phmoney_portfolios')
                        ->where('user_id  = ' . (int) $user->id)
                        ->set('user_default = 0');
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);

                $query->select('COUNT(*)')
                        ->from('#__phmoney_portfolios AS p')
                        ->where('p.published  = 1')
                        ->where('p.user_id  = ' . (int) $user->id);

                $db->setQuery($query);
                $numRecords = $db->loadResult();

                //check if table contains any portfolios with status 1
                if ($numRecords > 0) {
                        //find new portfolio to set as default
                        $query = $db->getQuery(true);

                        $query->select('p.id')
                                ->from('#__phmoney_portfolios AS p')
                                ->where('p.published = 1')
                                ->where('p.user_id  = ' . (int) $user->id);

                        $db->setQuery($query);
                        $portfolioId = $db->loadResult();

                        //set the portfolio as default 
                        $query = $db->getQuery(true);

                        $query->update('#__phmoney_portfolios')
                                ->where($db->qn('id') . ' = ' . $portfolioId)
                                ->set($db->qn('user_default') . ' = 1');
                        $db->setQuery($query);
                        $db->execute();

                        return true;
                }


                return false;
        }

        /**
         * Method to set automatically a default portfolio when all the others
         * have statuses different to 1.
         * 
         * @return  boolean  True if successful, false if an error occurs.
         */
        protected function setDefaultPortfolio2($id)
        {
                $this->populateState();
                $db = $this->getDbo();
                $user = Factory::getUser();

                $query = $db->getQuery(true);

                //check if a default portfolio already exists
                $query->select('p.id')
                        ->from('#__phmoney_portfolios AS p')
                        ->where('p.published = 1')
                        ->where('p.user_default = 1')
                        ->where('p.user_id  = ' . (int) $user->id);

                $db->setQuery($query);
                $result = $db->loadResult();

                if ($result == null) {
                        //set the portfolio as default 
                        $query = $db->getQuery(true);

                        $query->update('#__phmoney_portfolios')
                                ->where($db->qn('id') . ' = ' . $id)
                                ->set($db->qn('user_default') . ' = 1');
                        $db->setQuery($query);
                        $db->execute();
                }

                return true;
        }

        /**
         * Load accounts from database, convert and return them.
         * 
         * @param array $data Portfolio data
         * @return array List of accounts
         */
        public function import_gnucash_db_accounts($portfolio)
        {
                $this->populateState();

                $db_remote = $this->getDatabaseConnection($portfolio);
                $query_remote = $db_remote->getQuery(true);
                $query_remote->select('acc.account_type as type, acc.name as title, acc.code, acc.description, acc.guid, acc.parent_guid')
                        ->from('accounts as acc')
                        ->where('acc.account_type NOT LIKE ' . $db_remote->quote('ROOT'));
                $query_remote->select('com.guid as commodity_guid, com.namespace as commodity_type, com.mnemonic as commodity_mnemonic, com.cusip as commodity_cusip')
                        ->join('LEFT', $db_remote->quoteName('commodities') . ' AS com ON acc.commodity_guid = com.guid');
                $query_remote->select('acc2.name as parent_id')
                        ->join('LEFT', $db_remote->quoteName('accounts') . ' AS acc2 ON acc.parent_guid = acc2.guid');
                $db_remote->setQuery($query_remote);
                $accounts = $db_remote->loadAssocList();

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                foreach ($accounts as $key => &$account) {

                        //do not import already imported transactions
                        $query->clear();
                        $query->select('a.id')
                                ->where('a.params LIKE ' . $db->quote('%"gnucash_guid":"' . $account['guid'] . '"%'))
                                ->where('a.portfolio_id = ' . $db->quote($portfolio->id))
                                ->from('#__phmoney_accounts as a');
                        $db->setQuery($query);
                        if (!empty($db->loadResult())) {
                                unset($accounts[$key]);
                                continue;
                        }

                        $account = $this->convert_gnucash_db_account($account, $portfolio, $db_remote);
                }

                return $accounts;
        }

        /**
         * Convert db data to model data
         * 
         * @param array $data
         * @param array $portfolio_data
         * @param DatabaseDriver $db The gnucash database
         * @return array
         */
        protected function convert_gnucash_db_account($data, $portfolio_data, $db)
        {

                $result = array();

                $result['id'] = '0';

                $result['title'] = $data['title'];

                switch (strtolower($data['type'])) {
                        case 'stock':
                                $result['account_type_id'] = '2';
                                break;
                        case 'liability':
                        case 'credit':
                        case 'payable':
                                $result['account_type_id'] = '3';
                                break;
                        case 'equity':
                                $result['account_type_id'] = '4';
                                break;
                        case 'income':
                                $result['account_type_id'] = '5';
                                break;
                        case 'expense':
                                $result['account_type_id'] = '6';
                                break;

                        default:
                                $result['account_type_id'] = '1';
                                break;
                }

                //find currency
                $query = $db->getQuery(true);
                if ($data['commodity_type'] !== 'CURRENCY') {
                        if (!empty($data['commodity_mnemonic'])) {
                                $data['description'] .= ' - ' . $data['code'];
                                $result['code'] = $data['commodity_mnemonic'];
                        }
                        $query->select('com.mnemonic')
                                ->from('commodities as com');
                        $query->join('LEFT', $db->quoteName('prices') . ' AS pr ON pr.currency_guid = com.guid')
                                ->where('pr.commodity_guid LIKE ' . $db->quote($data['commodity_guid']));
                        $db->setQuery($query);
                        $currency_id = $db->loadResult();

                        if (is_null($currency_id)) {
                                $data['commodity_mnemonic'] = $portfolio_data->currency_id;
                        } else {
                                $data['commodity_mnemonic'] = $currency_id;
                        }
                }

                $db_local = $this->getDbo();
                $query_local = $db_local->getQuery(true);
                $query_local->select('a.id')
                        ->from('#__phmoney_currencys as a')
                        ->where('a.code LIKE ' . $db->q($data['commodity_mnemonic']));
                $db_local->setQuery($query_local);
                $currency_id = $db_local->loadResult();
                if (is_null($currency_id)) {
                        $result['currency_id'] = $portfolio_data->currency_id;
                } else {
                        $result['currency_id'] = $currency_id;
                }

                $result['parent_id'] = '1';

                $result['published'] = '1';

                $result['note'] = '';

                $result['alias'] = '';

                $result['portfolio_id'] = $this->getState('portfolio.id');

                $result['description'] = $data['description'];

                $result['asset_id'] = '';

                $result['lft'] = '';

                $result['rgt'] = '';

                $result['level'] = '';

                $result['path'] = '';

                $result['checked_out'] = null;

                $result['checked_out_time'] = null;

                $result['params'] = array(
                        'address' => '',
                        'country' => '',
                        'sector' => '',
                        'industry' => '',
                        'local_inde' => '',
                        'isin' => $data['commodity_cusip'],
                        'gnucash_guid' => $data['guid'],
                        'gnucash_parent_guid' => $data['parent_guid']
                );

                return $result;
        }

        /**
         * Get and test connection to the portfoliobase.
         * 
         * @param array $portfolio The portfolio portfolio
         * @return DatabaseDriver The portfoliobase driver connection
         */
        private function getDatabaseConnection($portfolio)
        {
                $options_db = [
                        'driver' => 'mysqli',
                        'host' => $portfolio->params['gnucash_database_host'],
                        'user' => $portfolio->params['gnucash_database_username'],
                        'password' => $portfolio->params['gnucash_database_password'],
                        'database' => $portfolio->params['gnucash_database_name'],
                        'prefix' => '',
                ];

                $db = DatabaseDriver::getInstance($options_db);

                return $db;
        }
        
        /**
         * Mass delete transactions having prices splits from selected portfolios
         * 
         * @param array $portfolio_id Portfolios id
         * @param string $task Type of task 
         * @return array $transaction_ids An array of the transaction ids
         */
        public function getAccounts($portfolio_id, $task = null)
        {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                //get split type if of price
                if ($task !== 'delete_accounts') {
                        return [];
                }

                $query->clear();
                $query->select('account.id')
                        ->from('#__phmoney_accounts as account')
                        ->where('account.portfolio_id = ' . $db->quote($portfolio_id));

                $db->setQuery($query);
                $transaction_ids = $db->loadColumn();

                return $transaction_ids;
        }

        /**
         * Mass delete transactions having prices splits from selected portfolios
         * 
         * @param array $portfolio_id Portfolios id
         * @param string $task Type of task 
         * @return array $transaction_ids An array of the transaction ids
         */
        public function getTransactions($portfolio_id, $task = null)
        {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                //get split type if of price
                if ($task == 'delete_prices') {
                        $query->select('id')
                                ->from('#__phmoney_split_types')
                                ->where('value LIKE ' . $db->quote('price'));
                        $db->setQuery($query);
                        $split_type_id = $db->loadResult();
                }

                $query->clear();
                $query->select('transaction.id')
                        ->from('#__phmoney_transactions as transaction')
                        ->where('transaction.portfolio_id = ' . $db->quote($portfolio_id));

                if ($task == 'delete_prices') {
                        $query->join('LEFT', '#__phmoney_splits AS split ON transaction.id = split.transaction_id')
                                ->where('split.split_type_id = ' . $db->quote($split_type_id));
                }

                $db->setQuery($query);
                $transaction_ids = $db->loadColumn();

                return $transaction_ids;
        }

}
