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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

/**
 * Implement Transaction Model
 *
 */
class TransactionModel extends AdminModel implements DispatcherAwareInterface {

        use DispatcherAwareTrait;

        /**
         * The type alias for this content type (for example, 'com_phmoney.transaction').
         *
         * @var    string
         */
        public $typeAlias = 'com_phmoney.transaction';

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
                $form = $this->loadForm('com_phmoney.transaction', 'transaction', array('control' => 'jform', 'load_data' => $loadData));

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
                if ($this->getState('transaction.id')) {
                        $id = $this->getState('transaction.id');

                        // Existing record. Can only edit in selected categories.
                        $form->setFieldAttribute('account_id', 'action', 'core.edit');

                        // Existing record. Can only edit own transactions in selected categories.
                        $form->setFieldAttribute('account_id', 'action', 'core.edit.own');
                } else {
                        // New record. Can only create in selected categories.
                        $form->setFieldAttribute('account_id', 'action', 'core.create');
                }

                $user = Factory::getUser();

                // Check for existing transaction.
                // Modify the form based on Edit State access controls.
                if ($id != 0 && (!$user->authorise('core.edit.state', 'com_phmoney.transaction.' . (int) $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_phmoney'))) {
                        // Disable fields for display.
                        $form->setFieldAttribute('state', 'disabled', 'true');

                        // Disable fields while saving.
                        // The controller has already verified this is an transaction you can edit.
                        $form->setFieldAttribute('state', 'filter', 'unset');
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
                $data = $app->getUserState('com_phmoney.edit.transaction.data', array());

                if (empty($data)) {
                        $data = $this->getItem();

                        // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Transaction Manager: Transactions
                        if ($this->getState('transaction.id') == 0) {

                                $filters = (array) $app->getUserState('com_phmoney.splits.filter', array('portfolio' => PhmoneyHelper::getDefaultPortfolio()));
                                $data->set(
                                        'state', $app->input->getInt(
                                                'state', ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                                        )
                                );
                                $data->set('portfolio_id', $app->input->getInt('portfolio', (!empty($filters['portfolio']) ? $filters['portfolio'] : null)));
                                $data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : \JFactory::getConfig()->get('access')))
                                );

                                $splits = array();
                                $split = array();
                                $split['id'] = null;
                                $split['currency_id'] = null;
                                $split['currency_name'] = null;
                                $split['currency_denom'] = null;
                                $split['account_id'] = null;
                                $split['value'] = null;
                                $split['description'] = null;
                                $split['reconcile_state'] = null;
                                $split['shares'] = null;
                                $split['split_type_id'] = null;
                                $splits[] = $split;
                                $splits[] = $split;
                                $data->set('splits', $splits);
                        }
                }

                // If there are params fieldsets in the form it will fail with a registry object
                if (isset($data->params) && $data->params instanceof Registry) {
                        $data->params = $data->params->toArray();
                }

                $this->preprocessData('com_phmoney.transaction', $data);

                return $data;
        }

        /**
         * Method to get a single record.
         *
         * @param   integer  $pk  The id of the primary key.
         *
         * @return  mixed  Object on success, false on failure.
         */
        public function getItem($pk = null) {
                $item = parent::getItem($pk);

                //get Portfolio currency name
                $jinput = Factory::getApplication()->input;
                $filters = (array) Factory::getApplication()->getUserState('com_phmoney.splits.filter');
                $portfolio_id = $jinput->getInt('portfolio', (!empty($filters['portfolio']) ? $filters['portfolio'] : null));
                $db = Factory::getDbo();
                $query = $db->getQuery(true);
                $query = $db->getQuery(true)
                        ->select('a.id')
                        ->from('#__phmoney_portfolios AS a');
                $query->select(
                                'cur.id as currency_id, cur.name as currency_name, cur.denom as currency_denom'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = a.currency_id');
                $query->where('(a.id = ' . $db->quote($portfolio_id) . ')');
                $db->setQuery($query);
                $row = $db->loadObject();
                $item->portfolio_currency_name = $row->currency_name;
                $item->portfolio_currency_id = $row->currency_id;
                $item->portfolio_currency_denom = $row->currency_denom;

                //get splits                
                $item->splits = $this->getSplits($item->id);

                if ($item) {
                        // Convert the params field to an array.
                        $registry = new Registry($item->attribs);
                        $item->attribs = $registry->toArray();

                        if (!empty($item->id)) {
                                $item->tags = new \Joomla\CMS\Helper\TagsHelper();
                                $item->tags->getTagIds($item->id, 'com_phmoney.split');
                        }
                }

                // Load associated phmoney items
                $assoc = \JLanguageAssociations::isEnabled();

                if ($assoc) {
                        $item->associations = array();

                        if ($item->id != null) {
                                $associations = \JLanguageAssociations::getAssociations('com_phmoney', '#__phmoney_transactions', 'com_phmoney.item', $item->id);

                                foreach ($associations as $tag => $association) {
                                        $item->associations[$tag] = $association->id;
                                }
                        }
                }

                return $item;
        }

        /**
         * Method to store new version of splits in ucm history.
         * 
         * @param type $table The split table.
         * @return boolean True on success, False on error.
         */
        protected function saveHistory($table) {
                $dispatcher = \JFactory::getApplication()->getDispatcher();
                $this->setDispatcher($dispatcher);

                $result = true;
                // Post-processing by observers
                $event = AbstractEvent::create(
                                'onTableAfterStore', [
                                'subject' => $table,
                                'result' => &$result,
                                ]
                );

                $this->getDispatcher()->dispatch('onTableAfterStore', $event);

                return $result;
        }

        /**
         * Method to save the form data.
         *
         * @param   array  $data  The form data.
         *
         * @return  boolean  True on success, False on error.
         *
         */
        public function save($data) {

                if ($data['version'] >= 1) {
                        $data['version'] ++;
                } else {
                        $data['version'] = 1;
                }

                $db = $this->getDbo();
                if (parent::save($data)) {

                        //delete obsolete splits
                        $splits = $this->getSplits($this->getState('transaction.id'));
                        foreach ($splits as $existing) {
                                $found = false;
                                foreach ($data['splits'] as $new) {
                                        if ($existing['id'] == $new['id']) {
                                                $found = true;
                                        }
                                }
                                if (!$found) {
                                        $query = $db->getQuery(true);
                                        $query->delete('#__phmoney_splits')
                                                ->where($db->qn('id') . '=' . $db->q($existing['id']));
                                        $db->setQuery($query);
                                        $db->execute();
                                }
                        }

                        foreach ($data['splits'] as $split) {
                                //add new split
                                if ($split['id'] == 0) {
                                        $query = $db->getQuery(true);
                                        $query->insert('#__phmoney_splits')
                                                ->set($db->qn('description') . ' = ' . $db->q($split['description']))
                                                ->set($db->qn('account_id') . ' = ' . $db->q($split['account_id']))
                                                ->set($db->qn('transaction_id') . ' = ' . $db->q($this->getState('transaction.id')))
                                                ->set($db->qn('value') . ' = ' . $db->q($split['value']))
                                                ->set($db->qn('reconcile_state') . ' = ' . $db->q($split['reconcile_state']))
                                                ->set($db->qn('shares') . ' = ' . $db->q($split['shares']))
                                                ->set($db->qn('split_type_id') . ' = ' . $db->q($split['split_type_id']))
                                                ->set($db->qn('version') . ' = ' . $db->q($data['version']));
                                        $db->setQuery($query);
                                        $db->execute();
                                } else {
                                        $transaction_id_old = (int) Factory::getApplication()->input->post->get('jform', array(), 'array')['id'];
                                        $transaction_id_new = $this->getState('transaction.id');
                                        if ($transaction_id_new === $transaction_id_old) {
                                                //update existing
                                                $query = $db->getQuery(true);
                                                $query->update('#__phmoney_splits')
                                                        ->where($db->qn('id') . ' = ' . $db->q($split['id']))
                                                        ->set($db->qn('description') . ' = ' . $db->q($split['description']))
                                                        ->set($db->qn('account_id') . ' = ' . $db->q($split['account_id']))
                                                        ->set($db->qn('transaction_id') . ' = ' . $transaction_id_new)
                                                        ->set($db->qn('value') . ' = ' . $db->q($split['value']))
                                                        ->set($db->qn('reconcile_state') . ' = ' . $db->q($split['reconcile_state']))
                                                        ->set($db->qn('shares') . ' = ' . $db->q($split['shares']))
                                                        ->set($db->qn('split_type_id') . ' = ' . $db->q($split['split_type_id']))
                                                        ->set($db->qn('version') . ' = ' . $db->q($data['version']));
                                                $db->setQuery($query);
                                                $db->execute();
                                        } else {
                                                //save2copy
                                                //add new
                                                $query = $db->getQuery(true);
                                                $query->insert('#__phmoney_splits')
                                                        ->set($db->qn('description') . ' = ' . $db->q($split['description']))
                                                        ->set($db->qn('account_id') . ' = ' . $db->q($split['account_id']))
                                                        ->set($db->qn('transaction_id') . ' = ' . $transaction_id_new)
                                                        ->set($db->qn('value') . ' = ' . $db->q($split['value']))
                                                        ->set($db->qn('reconcile_state') . ' = ' . $db->q($split['reconcile_state']))
                                                        ->set($db->qn('shares') . ' = ' . $db->q($split['shares']))
                                                        ->set($db->qn('split_type_id') . ' = ' . $db->q($split['split_type_id']))
                                                        ->set($db->qn('version') . ' = ' . $db->q($data['version']));
                                                $db->setQuery($query);
                                                $db->execute();
                                        }
                                }
                        }
                } else {
                        return false;
                }

                //add splits to ucm  history table
                $splits_tbl = $this->getSplits($this->getState('transaction.id'));
                $table = $this->getTable('Split');
                foreach ($splits_tbl as $single_split) {
                        $id = $single_split['id'];
                        $split = $table->load($id);
                        $flag = $this->saveHistory($table);
                }

                return true;
        }

        /**
         * Method to save the batch form data.
         *
         * @param   array  $data  The transaction data.
         * @param   array  $commands  The form data.
         *
         * @return  boolean  True on success, False on error.
         *
         */
        public function save2($data, $commands) {

                if ($data['version'] >= 1) {
                        $data['version'] ++;
                } else {
                        $data['version'] = 1;
                }

                if (parent::save($data)) {

                        $db = $this->getDbo();

                        if ($commands['action_batch'] == '1') { //copy
                                foreach ($data['splits'] as $split) {
                                        //add new split
                                        $query = $db->getQuery(true);
                                        $query->insert('#__phmoney_splits')
                                                ->set($db->qn('description') . ' = ' . $db->q($split['description']))
                                                ->set($db->qn('account_id') . ' = ' . $db->q($split['account_id']))
                                                ->set($db->qn('transaction_id') . ' = ' . $db->q($this->getState('transaction.id')))
                                                ->set($db->qn('value') . ' = ' . $db->q($split['value'] * 100))
                                                ->set($db->qn('reconcile_state') . ' = ' . $db->q($split['reconcile_state']))
                                                ->set($db->qn('shares') . ' = ' . $db->q($split['shares']))
                                                ->set($db->qn('split_type_id') . ' = ' . $db->q($split['split_type_id']))
                                                ->set($db->qn('version') . ' = ' . $db->q($data['version']));
                                        $db->setQuery($query);
                                        $db->execute();
                                }
                        } elseif ($commands['action_batch'] == '0') { //move
                                foreach ($data['splits'] as $split) {
                                        //update existing
                                        $query = $db->getQuery(true);
                                        $query->update('#__phmoney_splits')
                                                ->where($db->qn('id') . ' = ' . $db->q($split['id']))
                                                ->set($db->qn('description') . ' = ' . $db->q($split['description']))
                                                ->set($db->qn('account_id') . ' = ' . $db->q($split['account_id']))
                                                ->set($db->qn('transaction_id') . ' = ' . $db->q($this->getState('transaction.id')))
                                                ->set($db->qn('value') . ' = ' . $db->q($split['value'] * 100))
                                                ->set($db->qn('reconcile_state') . ' = ' . $db->q($split['reconcile_state']))
                                                ->set($db->qn('shares') . ' = ' . $db->q($split['shares']))
                                                ->set($db->qn('split_type_id') . ' = ' . $db->q($split['split_type_id']))
                                                ->set($db->qn('version') . ' = ' . $db->q($data['version']));
                                        $db->setQuery($query);
                                        $db->execute();
                                }
                        }
                } else {
                        return false;
                }

                //add splits to ucm  history table
                $splits_tbl = $this->getSplits($this->getState('transaction.id'));
                $table = $this->getTable('Split');
                foreach ($splits_tbl as $single_split) {
                        $id = $single_split['id'];
                        $split = $table->load($id);
                        $flag = $this->saveHistory($table);
                }

                return true;
        }

        /**
         * Get transaction splits
         * 
         * @param type $transaction_id Transaction id
         * @return array list with splits
         */
        protected function getSplits($transaction_id) {
                if ($transaction_id > 0) {
                        $db = $this->getDbo();
                        $query = $db->getQuery(true);
                        $query->select('a.id, a.description, a.account_id, transaction_id, a.value, a.shares, a.split_type_id, a.reconcile_state, a.version')
                                ->from('#__phmoney_splits as a')
                                ->where('a.transaction_id=' . $db->q($transaction_id));
                        $query->select(
                                        'ac.currency_id as currency_id'
                                )
                                ->join('LEFT', '#__phmoney_accounts AS ac ON ac.id = a.account_id');
                        $query->select(
                                        'cur.name as currency_name, cur.denom as currency_denom'
                                )
                                ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = ac.currency_id');
                        $db->setQuery($query);
                        $splits = $db->loadAssocList();

                        foreach ($splits as $key => $split) {
                                $splits[$key]['value'] /= $split['currency_denom'];
                        }
                        return $splits;
                } else {
                        return array();
                }
        }

        /**
         * Method to validate the form data.
         *
         * @param   \JForm  $form   The form to validate against.
         * @param   array   $data   The data to validate.
         * @param   string  $group  The name of the field group to validate.
         *
         * @return  array|boolean  Array of filtered data if valid, false otherwise.
         *
         * @see     \JFormRule
         * @see     \JFilterInput
         */
        public function validate($form, $data, $group = null) {

                $db = $this->getDbo();

                $version = $data['version'];

                $data = parent::validate($form, $data, $group);

                $data['version'] = $version;

                //set rate to 1 if same currency as portfolio                
                $portfolio_currency_id = $data['portfolio_currency_id'];
                $portfolio_currency_denom = $data['portfolio_currency_denom'];
                $sum = 0;
                foreach ($data['splits'] as $ref => $split) {

                        if (empty($split['currency_id'])) {
                                $query = $db->getQuery(true);
                                $query->select('a.currency_id')
                                        ->from('#__phmoney_accounts AS a')
                                        ->where('a.id = ' . (int) $split['account_id']);
                                $query->select('c.denom AS currency_denom')
                                        ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id');
                                $db->setQuery($query);
                                $result = $db->loadObject();
                                $data['splits'][$ref]['currency_denom'] = $result->currency_denom;
                        }

                        if ($portfolio_currency_id == $split['currency_id']) {
                                $data['splits'][$ref]['rate'] = 1;
                        }
                        $data['splits'][$ref]['portfolio_value'] = $data['splits'][$ref]['value'] * $data['splits'][$ref]['rate'];
                        $sum += $data['splits'][$ref]['portfolio_value'];
                        $data['splits'][$ref]['value'] *= $data['splits'][$ref]['currency_denom'];
                }

                //validate sum to be zero
                $sum = round($sum, strlen($portfolio_currency_denom) - 1);
                if ($sum != 0) {
                        $this->setError(Text::_('COM_PHMONEY_SUM_ERROR'));
                        return false;
                }

                return $data;
        }

        /**
         * Method to run batch operations.
         * 
         * @param array $commands The data of the batch form.
         * @param array $pks The transactions and splits ids.
         * @param array $contexts Full name of split.
         * 
         * @retur boolean True if successful, false otherwise and internal error is set.
         */
        public function batch($commands, $pks, $contexts) {
                $this->populateState();

                $db = $this->getDbo();
                foreach ($pks as $pk) {
                        $query = $db->getQuery(true);

                        $substr = "_";
                        $arr = explode($substr, $pk);
                        $transaction_id = $arr[0];
                        $this->getState('transaction.id', $transaction_id);
                        $split_id = $arr[1];
                        $item = $this->getItem($transaction_id);

                        $data = ArrayHelper::fromObject($item);
                        unset($data['typeAlias']);
                        unset($data['tagsHelper']);
                        unset($data['contenthistoryHelper']);
                        $data['tags'] = array();

                        if ($commands['account_source'][0] != null) {
                                $query->select('a.currency_id')
                                        ->from('#__phmoney_accounts AS a')
                                        ->where('a.id = ' . (int) $commands['account_source']);

                                $db->setQuery($query);
                                $result1 = $db->loadResult();

                                $query = $db->getQuery(true);

                                $query->select('a.currency_id')
                                        ->from('#__phmoney_splits AS s')
                                        ->join('LEFT', $db->quoteName('#__phmoney_accounts') . ' AS a ON a.id = s.account_id')
                                        ->where('s.id = ' . (int) $split_id);

                                $db->setQuery($query);
                                $result2 = $db->loadResult();

                                if ($result1 != $result2) { //different accounts currencies
                                        $this->setMessage(\JText::sprintf('COM_PHMONEY_DIFFERENT_CURRENCIES', $this->getError()), 'warning');
                                        break;
                                }

                                //change split's data
                                $i = 0;
                                foreach ($data['splits'] as $split) {
                                        if ($split['id'] == $split_id) {
                                                $data['splits'][$i]['account_id'] = (int) $commands['account_source'][0];
                                        }
                                        $i++;
                                }
                        }

                        if ($commands['batch_post_date'] != null) {
                                $data['post_date'] = $commands['batch_post_date'];
                                $data['modified_date'] = $commands['batch_post_date'];
                        }

                        if (($commands['batch_post_date'] == null) && ($commands['account_source'][0] == null) && ($commands['action_batch'] == '0')) {
                                return true;
                        }

                        if ($commands['action_batch'] == '1') {
                                $data['id'] = 0;
                        }
                        $flag = $this->save2($data, $commands);

                        if (!flag) {
                                return false;
                        }
                }
                return true;
        }

        /**
         * Sets the internal message that is passed with a redirect
         *
         * @param   string  $text  Message to display on redirect.
         * @param   string  $type  Message type. Optional, defaults to 'message'.
         *
         * @return  string  Previous message
         *
         */
        public function setMessage($text, $type = 'message') {
                $previous = $this->message;
                $this->message = $text;
                $this->messageType = $type;

                return $previous;
        }

        /**
         * Method to restore a specific version of a transaction. 
         * 
         * @param type $version_id The version id of the transaction to be restored.
         * @param Table $table The transaction table. 
         * @return boolean True on success, False on error.
         */
        public function loadHistory($version_id, \Joomla\CMS\Table\Table &$table) {

                $result = parent::loadHistory($version_id, $table);
                if ($result) {
                        $db = $this->getDbo();

                        //get splits that match with the transaction 
                        $query = $db->getQuery(true);
                        $query->select('*')
                                ->from('#__ucm_history')
                                ->where($query->qn('version_data') . ' LIKE ' . $query->q('%"transaction_id":' . $table->id . '%'))
                                ->where($query->qn('version_data') . ' LIKE ' . $query->q('%"version":' . $table->version . '%'));
                        $db->setQuery($query);
                        $splits_hist = $db->loadObjectList();

                        if (empty($splits_hist)) {
                                $this->setMessage('empty splits.', 'error');
                                return false;
                        }
                        //splits from ucm history table
                        $splits_tbl = ArrayHelper::fromObject($splits_hist);

                        //version_data of each split of the ucm history table put in splitsArray
                        $splitsArray = array();
                        foreach ($splits_tbl as $splitTemp) {
                                $split = ArrayHelper::fromObject(json_decode($splitTemp['version_data']));
                                array_push($splitsArray, $split);
                        }

                        //get existing splits of the specific transaction id from the splits table
                        $splits = $this->getSplits($table->id);

                        //delete the existing splits
                        foreach ($splits as $existing) {
                                $query = $db->getQuery(true);
                                $query->delete('#__phmoney_splits')
                                        ->where($db->qn('id') . '=' . $db->q($existing['id']));
                                $db->setQuery($query);
                                $db->execute();
                        }

                        //create the new splits that will be restored 
                        foreach ($splitsArray as $new) {
                                $query = $db->getQuery(true);
                                $query->insert('#__phmoney_splits')
                                        ->set($db->qn('description') . ' = ' . $db->q($new['description']))
                                        ->set($db->qn('account_id') . ' = ' . $db->q($new['account_id']))
                                        ->set($db->qn('transaction_id') . ' = ' . $db->q($new['transaction_id']))
                                        ->set($db->qn('value') . ' = ' . $db->q($new['value']))
                                        ->set($db->qn('reconcile_state') . ' = ' . $db->q($new['reconcile_state']))
                                        ->set($db->qn('shares') . ' = ' . $db->q($new['shares']))
                                        ->set($db->qn('split_type_id') . ' = ' . $db->q($new['split_type_id']))
                                        ->set($db->qn('version') . ' = ' . $db->q($new['version']));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $exc) {
                                        $this->setMessage($exc->getMessage(), 'error');
                                        return false;
                                }
                        }
                }
                return $result;
        }

        /**
         * Method to delete one or more records.
         *
         * @param   array  &$pks  An array of record primary keys.
         *
         * @return  boolean  True if successful, false if an error occurs.
         *
         */
        public function delete(&$pks) {

                $result = parent::delete($pks);

                if ($result) {
                        foreach ($pks as $i => $pk) {
                                $db = $this->getDbo();
                                $query = $db->getQuery(true);
                                $query->delete('#__ucm_history')
                                        ->where($query->qn('version_data') . ' LIKE ' . $query->q('%"transaction_id":' . $pk . '%'));
                                $db->setQuery($query);
                                try {
                                        $db->execute();
                                } catch (\RuntimeException $exc) {
                                        $this->setMessage($exc->getMessage(), 'error');
                                        return false;
                                }
                        }
                }

                return true;
        }

}
