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
use Joomla\CMS\Filesystem\File;

/**
 * Description of ImportsModel
 *
 */
class ImportscsvModel extends ListModel {

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
        protected function populateState($ordering = null, $direction = null) {
                parent::populateState($ordering, $direction);

                $app = Factory::getApplication();
                $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
                if (!isset($filters['portfolio'])) {
                        $this->setState('filter.portfolio', PhmoneyHelper::getDefaultPortfolio());
                }
        }

        /**
         * Read the csv file and returns the total number of lines
         *
         * @return  mixed  An array of data items on success, false on failure.
         *
         */
        protected function getLinesFromFile() {

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
        protected function getItemsFromFile($limitstart = 0, $limit = 0) {

                $delimiter = $this->getState('filter.delimiter', ',');
                if (empty($delimiter)) {
                        $delimiter = ',';
                }
                $enclosure = $this->getState('filter.enclosure', '"');
                if (empty($enclosure)) {
                        $enclosure = '"';
                }

                $config = Factory::getApplication()->getConfig();
                $filename = $config->get('tmp_path') . '/' . File::makeSafe('import_splits.csv');

                $items = array();
                if (!file_exists($filename)) {
                        return $items;
                }
                $row = 1;
                if (($handle = fopen($filename, "r")) !== FALSE) {
                        while (($line = fgetcsv($handle, 0, $delimiter, $enclosure)) !== FALSE) {
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
                        $this->cache[$store] = $this->getItemsFromFile($this->getStart(), $this->getState('list.limit'));
                } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                }

                return $this->cache[$store];
        }

        /**
         * Method to get the total number of items for the data set.
         *
         * @return  integer  The total number of items available in the data set.
         *
         */
        public function getTotal() {
                // Get a storage key.
                $store = $this->getStoreId('getTotal');

                // Try to load the data from internal storage.
                if (isset($this->cache[$store])) {
                        return $this->cache[$store];
                }

                try {
                        // Load the total and add the total to the internal cache.
                        $this->cache[$store] = $this->getLinesFromFile();
                } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                }

                return $this->cache[$store];
        }

        /**
         * Import data from xml file to database.
         *
         * @param array $headers The headers of the columns to import
         */
        public function Import2Db($headers) {

                //get portfolio currency denom
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

                //get db
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                //get accounts possibilities
                $query->select('COUNT(*) AS appearances, title')
                        ->from('#__phmoney_transactions')
                        ->group('title')
                        ->order('appearances desc');
                $db->setQuery($query);
                $this->accounts_freq = $db->loadAssocList();

                $items = $this->getItemsFromFile();
                $transactions = array();
                foreach ($items as $item) {
                        $transaction = array();

                        $transaction['portfolio_id'] = $portfolio_id;
                        $transaction['status'] = 0;
                        $transaction["value"] = "";
                        foreach ($headers as $label => $header) {
                                if ($header !== false) {                                        
                                        $transaction[$label] = $item[$header + 1];
                                        if ($label == "credit") {
                                                if (is_numeric($transaction[$label])) {
                                                        $transaction["value"] += $transaction[$label];
                                                } 
                                                unset($transaction[$label]);
                                        }
                                        if ($label == "debit") {
                                                if (is_numeric($transaction[$label])) {
                                                        $transaction["value"] -= $transaction[$label];
                                                }
                                                unset($transaction[$label]);
                                        }
                                }
                        }

                        $transactions[] = $transaction;
                }

                //truncate imports table
                $db->setQuery("TRUNCATE #__phmoney_imports;");
                $db->execute();

                //add data in db
                foreach ($transactions as $transaction) {
                        $query->clear();
                        $query->insert('#__phmoney_imports');
                        foreach ($transaction as $label => $value) {
                                $query->set($query->qn($label) . '=' . $query->q($value));
                        }
                        $db->setQuery($query);
                        $db->execute();
                }
                
                parent::cleanCache();
        }
        
        /**
	 * Clean the cache access public for controller
	 *
	 * @param   string  $group  The cache group
	 *
	 * @return  void
	 *
	 */
        public function cleanCachePublic($group = null) {
                parent::cleanCache();
        }

}
