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

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Router\Route;

/**
 * Description of ImportsController
 *
 */
class ImportsController extends AdminController
{

        /**
         * Removes an item.
         *
         * @return  void
         *
         */
        public function delete()
        {

                // Check for request forgeries
                \JSession::checkToken() or die(\JText::_('JINVALID_TOKEN'));

                // Get items to remove from the request.
                $cids = $this->input->get('cid', array(), 'array');

                if (!is_array($cids) || count($cids) < 1) {
                        $this->app->getLogger()->warning(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
                } else {

                        // Get the model.
                        $model = $this->getModel();

                        // Make sure the item ids are integers
                        $cid = array();
                        foreach ($cids as $value) {
                                $explodes = explode('_', $value);
                                $cid[$explodes[0]] = $explodes[1];
                        }
                        $cid = ArrayHelper::toInteger($cid);

                        // Remove the items.
                        if ($model->delete($cid)) { //gets Imports model instead of Import
                                $this->setMessage(\JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
                        } else {
                                $this->setMessage($model->getError(), 'error');
                        }

                        // Invoke the postDelete method to allow for the child class to access the model.
                        $this->postDeleteHook($model, $cid);
                }

                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Method to get a model object, loading it if required.
         *
         * @param   string  $name    The model name. Optional.
         * @param   string  $prefix  The class prefix. Optional.
         * @param   array   $config  Configuration array for model. Optional.
         *
         * @return  BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
         *
         */
        public function getModel($name = 'Import', $prefix = '', $config = array())
        {
                return parent::getModel($name, $prefix, $config);
        }

        /**
         * Upload import file and redirect to import csv view
         */
        public function import_csv_file_splits()
        {

                $files = $this->input->files->get('filter', '', 'array');
                $file = $files['import_file'];

                $file_name = PhmoneyHelper::upload($file, 'import_splits.csv');

                if ($file_name === false) {
                        $this->setMessage(Text::_(COM_PHMONEY_PROCESS_FAILED), 'error');
                }

                $model = $this->getModel('Importscsv');
                $model->cleanCachePublic();

                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=importscsv', false));
        }

        /**
         * Import from csv file to database and redirect to imports view
         */
        public function import_csv_columns_splits()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                //load and check headers
                $headers = array();
                $headers_arr = $this->input->get('headers', array(), 'array');
                $headers['post_date'] = array_search('post_date', $headers_arr);
                $headers['title'] = array_search('title', $headers_arr);
                $headers['num'] = array_search('num', $headers_arr);
                $headers['description'] = array_search('description', $headers_arr);
                $headers['credit'] = array_search('credit', $headers_arr);
                $headers['debit'] = array_search('debit', $headers_arr);
                if ($headers['credit'] === false && $headers['debit'] === false) {
                        $this->setMessage(Text::_($this->text_prefix . '_NO_AMOUNT_SELECTED'), 'warning');
                        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=importscsv', false));
                        return;
                }
                $model = $this->getModel("Importscsv");
                $model->Import2Db($headers);

                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Import selected items
         *
         * @return boolean
         */
        public function import_selected()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                // Get items to publish from the request.
                $cids = $this->input->get('cid', array(), 'array');
                if (empty($cids)) {
                        $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
                } else {
                        //Check data
                        $accounts = array();

                        $rows = array();
                        foreach ($cids as $value) {
                                $explodes = explode('_', $value);
                                $rows[$explodes[0]] = $explodes[1];
                        }
                        $rows = ArrayHelper::toInteger($rows);

                        $source_accounts = $this->input->get('source_account', array(), 'array');
                        $destination_accounts = $this->input->get('destination_account', array(), 'array');
                        $counter = 0;
                        foreach ($rows as $key => $value) {

                                if ($source_accounts[$key] === "0" || $destination_accounts[$key] === "0" || empty($source_accounts[$key]) || empty($destination_accounts[$key])) {
                                        $this->setMessage(Text::_($this->text_prefix . '_NO_ACCOUNT_SELECTED'), 'warning');
                                        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
                                        return false;
                                }
                                $accounts[$counter]['id'] = $value;
                                $accounts[$counter]['source_account'] = $source_accounts[$key];
                                $accounts[$counter]['destination_account'] = $destination_accounts[$key];
                                $counter++;
                        }

                        $model = $this->getModel('Imports');
                        $result = $model->ImportSelected($accounts);
                        $this->setMessage($result);
                }

                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Estimate source and destination accounts based on existing splits
         */
        public function estimate()
        {
                $model = $this->getModel("Imports");
                $model->estimate();
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Import all items
         */
        public function import_all()
        {
                $model = $this->getModel("Imports");
                $result = $model->ImportAll();
                $this->setMessage($result);
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Download prices from Yahoo and save in imports table
         */
        public function download_prices()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                $model = $this->getModel("Imports");
                try {
                        $model->download_prices();
                } catch (\Exception $exc) {
                        $this->setMessage($exc->getMessage(), 'error');
                }

                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }
                }
