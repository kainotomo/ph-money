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

namespace Joomla\Component\Phmoney\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Description of PortfolioController
 *
 */
class PortfolioController extends FormController
{

        /**
         * Constructor.
         *
         * @param   array                $config   An optional associative array of configuration settings.
         * Recognized key values include 'name', 'default_task', 'model_path', and
         * 'view_path' (this list is not meant to be comprehensive).
         * @param   MVCFactoryInterface  $factory  The factory.
         * @param   CmsApplication       $app      The JApplication for the dispatcher
         * @param   \JInput              $input    Input
         *
         */
        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
        {
                parent::__construct($config, $factory, $app, $input);

                // An portfolio edit form can come from the portfolios or featured view.
                // Adjust the redirect view on the value of 'return' in the request.
                if ($this->input->get('return') == 'portfolios') {
                        $this->view_list = 'portfolios';
                        $this->view_item = 'portfolio&return=portfolios';
                }
        }

        /**
         * Import GnuCash accounts from csv file.
         * Account names must be unique.
         */
        public function import_gnucash_csv_accounts()
        {

                $this->setMessage(Text::_('COM_PHMONEY_PROCESS_SUCCESS'));

                $data = $this->input->post->get('jform', array(), 'array');
                $files = $this->input->files->get('jform', '', 'array');
                $file = $files['import_file'];

                $model_portfolio = $this->getModel('Portfolio');
                $rows = $model_portfolio->import_gnucash_csv_accounts($file, $data);
                if ($rows === false) {
                        $this->setMessage(Text::_('COM_PHMONEY_PROCESS_FAILED'), 'error');
                }

                foreach ($rows as $value) {
                        if (!$this->save_account($value)) {
                                break;
                        }
                }

                // Set the record data in the session.
                $recordId = $this->input->getInt('id');
                $this->holdEditId($this->context, $recordId);
                Factory::getApplication()->setUserState($this->context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_item
                                . $this->getRedirectToItemAppend($recordId, 'id'), false
                        )
                );
        }

        /**
         * Save account
         * 
         * @param array $data
         * @return boolean
         */
        protected function save_account(&$data)
        {
                $app = Factory::getApplication();
                $model = $this->getModel('Account');
                $table = $model->getTable('Account');

                if ($data['parent_id'] != '1') {
                        $table2 = $model->getTable('Account');
                        if (!$table2->load(array('title' => $data['parent_id'], 'portfolio_id' => $data['portfolio_id']))) {
                                $data['parent_id'] = '1';
                        } else {
                                $data['parent_id'] = $table2->id;
                        }
                }

                // Determine the name of the primary key for the data.
                $key = $table->getKeyName();
                $recordId = 0;
                $data[$key] = $recordId;

                // Access check.
                if (!$this->allowSave($data, $key)) {
                        $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
                        return false;
                }

                // Validate the posted data.
                // Sometimes the form needs some posted data, such as for plugins and modules.
                $form = $model->getForm($data, false);

                if (!$form) {
                        $app->enqueueMessage($model->getError(), 'error');
                        return false;
                }

                // Test whether the data is valid.
                $validData = $model->validate($form, $data);

                // Check for validation errors.
                if ($validData === false) {
                        // Get the validation messages.
                        $errors = $model->getErrors();

                        // Push up to three validation messages out to the user.
                        for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                                if ($errors[$i] instanceof \Exception) {
                                        $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                                } else {
                                        $app->enqueueMessage($errors[$i], 'warning');
                                }
                        }

                        // Save the data in the session.
                        $app->setUserState($context . '.data', $data);
                        return false;
                }

                // Attempt to save the data.
                //if (!$model->save($validData)) {
                if (!$model->save($data)) {
                        $app->enqueueMessage($model->getError(), 'error');
                        return false;
                }

                $data['id'] = $model->getState('account.id');

                return true;
        }

        /**
         * Save account again to assign parents
         * 
         * @param array $data
         * @return boolean
         */
        protected function save_account_2($data)
        {

                $app = Factory::getApplication();
                $model = $this->getModel('Account');
                $table = $model->getTable('Account');
                $db = $model->getDbo();
                $query = $db->getQuery(true);

                $query->select('id')
                        ->from('#__phmoney_accounts')
                        ->where('params LIKE ' . $db->quote('%"gnucash_guid":"' . $data['params']['gnucash_parent_guid'] . '"%'));
                $db->setQuery($query);
                $data['parent_id'] = $db->loadResult();

                if (is_null($data['parent_id'])) {
                        return true;
                }

                $model->getItem($data['id']);
                // Attempt to save the data.
                if (!$model->save($data)) {
                        $app->enqueueMessage($model->getError(), 'error');
                        return false;
                }
                return true;
        }

        /**
         * Import GnuCash transactions from csv file.
         */
        public function import_gnucash_csv_trxns()
        {

                $this->setMessage(Text::_('COM_PHMONEY_PROCESS_SUCCESS'));

                $data = $this->input->post->get('jform', array(), 'array');
                $files = $this->input->files->get('jform', '', 'array');
                $file = $files['import_file'];

                $model_portfolio = $this->getModel('Portfolio');
                $rows = $model_portfolio->import_gnucash_csv_trxns($file, $data);
                if ($rows === false) {
                        $this->setMessage(Text::_('COM_PHMONEY_PROCESS_FAILED'), 'error');
                }

                foreach ($rows as $value) {
                        if (!$this->save_trxn($value)) {
                                break;
                        }
                }

                // Set the record data in the session.
                $recordId = $this->input->getInt('id');
                $this->holdEditId($this->context, $recordId);
                Factory::getApplication()->setUserState($this->context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_item
                                . $this->getRedirectToItemAppend($recordId, 'id'), false
                        )
                );
        }

        /**
         * Save transaction
         * 
         * @param array $transaction
         * @return boolean
         */
        protected function save_trxn($transaction)
        {
                $db = Factory::getDbo();

                $splits = $transaction['splits'];
                unset($transaction['splits']);

                //insert transaction
                $query = $db->getQuery(true);
                $query->insert('#__phmoney_transactions');
                foreach ($transaction as $key => $value) {
                        $query->set($db->qn($key) . ' = ' . $db->q($value));
                }
                $db->setQuery($query);
                $db->execute();

                //select transaction last id
                $query->clear();
                $query->select('a.id')
                        ->from('#__phmoney_transactions as a')
                        ->order('a.id DESC');
                $db->setQuery($query);
                $trasaction_id = $db->loadResult();

                //insert splits
                foreach ($splits as $split) {
                        $query->clear();
                        $split['transaction_id'] = $trasaction_id;
                        $split = array_filter($split);
                        $query = $db->getQuery(true);
                        $query->insert('#__phmoney_splits');
                        foreach ($split as $key => $value) {
                                $query->set($db->qn($key) . ' = ' . $db->q($value));
                        }
                        $db->setQuery($query);
                        $db->execute();
                }

                //delete from imports
                $guid = json_decode($transaction['attribs']);
                $query->clear();
                $query->delete('#__phmoney_imports')
                        ->where('num LIKE ' . $db->q($guid->gnucash_guid));
                $db->setQuery($query);
                $db->execute();

                return true;
        }

        /**
         * Import GnuCash accounts from database table.
         */
        public function import_gnucash_db_accounts()
        {
                $this->setMessage(Text::_('COM_PHMONEY_PROCESS_COMPLETED'));
                
                $model_portfolio = $this->getModel('Portfolio');
                $recordId = $this->input->getInt('id');
                $portfolio=$model_portfolio->getItem($recordId);
                try {
                        $accounts = $model_portfolio->import_gnucash_db_accounts($portfolio);
                } catch (\Throwable $exc) {
                        $this->setMessage($exc->getCode() . ' - ' . $exc->getMessage(), 'error');
                }

                // Save accounts
                foreach ($accounts as &$account) {
                        if (!$this->save_account($account)) {
                                break;
                        }
                }

                // Save them again to assign parents
                foreach ($accounts as &$account) {
                        if (!$this->save_account_2($account)) {
                                break;
                        }
                }

                // Set the record data in the session.
                $recordId = $this->input->getInt('id');
                $this->holdEditId($this->context, $recordId);
                Factory::getApplication()->setUserState($this->context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_item
                                . $this->getRedirectToItemAppend($recordId, 'id'), false
                        )
                );
        }

        /**
         * Import GnuCash transactions from database table.
         * Called directly from the user
         */
        public function import_gnucash_db_trxns()
        {
                $model_portfolio = $this->getModel('Portfolio'); 
                $recordId = $this->input->getInt('id');
                $portfolio=$model_portfolio->getItem($recordId);
                $total_trxns = $model_portfolio->getTotalTransactions($portfolio);

                // Set the record data in the session.
                $this->holdEditId($this->context, $recordId);
                Factory::getApplication()->setUserState($this->context . '.data', null);

                // Redirect back to the edit screen.
                $this->input->set('layout', 'import_gnucash_db_trxns', 'string');
                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_item
                                . $this->getRedirectToItemAppend($recordId, 'id') . '&total=' . $total_trxns, false
                        )
                );
        }

        /**
         * Import GnuCash transactions from database table.
         * It is called from Java Script as web service.
         */
        public function import_gnucash_db_trxns_web_service()
        {
                $limit = $this->input->getInt('limit', 100);
                $offset = $this->input->getInt('offset', 0);
                $portfolio_id=$this->input->getInt('id', 0);
                $model_portfolio = $this->getModel('Portfolio');
                $portfolio = $model_portfolio->getItem($portfolio_id);
                $transactions = $model_portfolio->import_gnucash_db_trxns($portfolio, $limit, $offset);

                if (count($transactions) > 0) {
                        foreach ($transactions as $transaction) {
                                if (!$this->save_trxn($transaction)) {
                                        break;
                                }
                        }
                        die;
                }

                $this->setMessage(Text::_('COM_PHMONEY_PROCESS_COMPLETED'));
                die;
        }

        /**
         * Method to export accounts of a portfolio.
         * 
         * @param   object  $model  The model.
         * 
         * @return  boolean   True if successful, false otherwise and internal error is set.
         */
        public function export_accounts($model = null)
        {

                $data = $this->input->post->get('jform', array(), 'array');
                $portfolio_id = $data['id'];

                // Set model
                $model = $this->getModel('Portfolio', 'Administrator', array());

                if ($model->export_accounts($portfolio_id) === false) {
                        $this->setMessage(Text::_('COM_PHMONEY_PROCESS_FAILED'), 'error');
                }

                // Preset the redirect
                $this->setRedirect(\JRoute::_('index.php/component/phmoney?view=portfolio&layout=edit&id=' . $portfolio_id, false));
        }

        /**
         * Method to export transactions of a portfolio.
         * 
         * @param   object  $model  The model.
         * 
         * @return  boolean   True if successful, false otherwise and internal error is set.
         */
        public function export_transactions($model = null)
        {

                $data = $this->input->post->get('jform', array(), 'array');
                $portfolio_id = $data['id'];

                // Set model
                $model = $this->getModel('Portfolio', 'Administrator', array());

                if ($model->export_transactions($portfolio_id) === false) {
                        $this->setMessage(Text::_('COM_PHMONEY_PROCESS_FAILED'), 'error');
                }

                // Preset the redirect
                $this->setRedirect(\JRoute::_('index.php/component/phmoney?view=portfolio&layout=edit&id=' . $portfolio_id, false));
        }

}
