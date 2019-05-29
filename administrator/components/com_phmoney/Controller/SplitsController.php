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

namespace Joomla\Component\Phmoney\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

/**
 * Splits list controller class.
 *
 */
class SplitsController extends AdminController
{

        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
        {

                parent::__construct($config, $factory, $app, $input);

                // Value = 0
                $this->registerTask('unreconcile', 'reconcile');

                $this->registerTask('nan', 'set_split_type');
                $this->registerTask('buy', 'set_split_type');
                $this->registerTask('sell', 'set_split_type');
                $this->registerTask('dividend', 'set_split_type');
                $this->registerTask('fee', 'set_split_type');
                $this->registerTask('price', 'set_split_type');
        }

        /**
         * Proxy for getModel.
         *
         * @param   string  $name    The model name. Optional.
         * @param   string  $prefix  The class prefix. Optional.
         * @param   array   $config  The array of possible config values. Optional.
         *
         * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
         *
         */
        public function getModel($name = 'Split', $prefix = 'Administrator', $config = array('ignore_request' => true))
        {
                return parent::getModel($name, $prefix, $config);
        }

        /**
         * Method to publish a list of items
         *
         * @return  void
         *
         */
        public function publish()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                // Get items to publish from the request.
                $ids = $this->input->get('cid', array(), 'array');
                $cid = array();
                foreach ($ids as $value) {
                        $id_explode = explode('_', $value);
                        $cid[] = $id_explode[0];
                }
                $cid = array_unique($cid);
                $data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
                $task = $this->getTask();
                $value = ArrayHelper::getValue($data, $task, 0, 'int');

                if (empty($cid)) {
                        $this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
                } else {

                        // Get the model.
                        $model = $this->getModel('Transaction');

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        try {
                                // Publish the tranasction items.

                                $model->publish($cid, $value);
                                $errors = $model->getErrors();
                                $ntext = null;

                                if ($value === 1) {
                                        if ($errors) {
                                                Factory::getApplication()->enqueueMessage(Text::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
                                        } else {
                                                $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                                        }
                                } elseif ($value === 0) {
                                        $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                                } elseif ($value === 2) {
                                        $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                                } else {
                                        $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                                }

                                if ($ntext !== null) {
                                        $this->setMessage(Text::plural($ntext, count($cid)));
                                }

                                //publish the split items
                                $cid_in = ArrayHelper::toInteger($cid);
                                $cid_in = implode(',', $cid_in);
                                $db = Factory::getDbo();
                                $query = $db->getQuery(true);
                                $query->update('#__phmoney_splits as a')
                                        ->set('a.state = ' . (int) $value)
                                        ->where($db->quoteName('a.transaction_id') . ' IN (' . $cid_in . ')');
                                $db->setQuery($query);
                                $db->execute();
                        } catch (\Exception $e) {
                                $this->setMessage($e->getMessage(), 'error');
                        }
                }

                $extension = $this->input->get('extension');
                $extensionURL = $extension ? '&extension=' . $extension : '';
                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
        }
        
        /**
         * Removes an item.
         *
         * @return  void
         *
         */
        public function delete()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                // Get items to remove from the request.
                $ids = $this->input->get('cid', array(), 'array');
                $cid = array();
                foreach ($ids as $value) {
                        $id_explode = explode('_', $value);
                        $cid[] = $id_explode[0];
                }

                if (!is_array($cid) || count($cid) < 1) {
                        $this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
                } else {
                        $cid = array_unique($cid);

                        // Get the model.
                        $model = $this->getModel('Transaction');

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        // Remove the items.
                        if ($model->delete($cid)) {
                                $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
                        } else {
                                $this->setMessage($model->getError(), 'error');
                        }

                        // Invoke the postDelete method to allow for the child class to access the model.
                        $this->postDeleteHook($model, $cid);
                }

                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        /**
         * Method to reconcile a list of splits
         *
         * @return  void
         *
         */
        public function reconcile()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                // Get items to publish from the request.                               
                $ids = $this->input->get('cid', array(), 'array');
                $cid = array();
                foreach ($ids as $value) {
                        $id_explode = explode('_', $value);
                        $cid[] = $id_explode[1];
                }
                $data = array('reconcile' => 1, 'unreconcile' => 0);
                $task = $this->getTask();
                $value = ArrayHelper::getValue($data, $task, 0, 'int');

                if (empty($cid)) {
                        $this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
                } else {

                        // Get the model.
                        $model = $this->getModel();

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        // Publish the items.
                        try {
                                $model->publish($cid, $value);
                                $errors = $model->getErrors();
                                $ntext = null;

                                if ($value === 1) {
                                        if ($errors) {
                                                Factory::getApplication()->enqueueMessage(Text::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
                                        } else {
                                                $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                                        }
                                } elseif ($value === 0) {
                                        $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                                } elseif ($value === 2) {
                                        $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                                } else {
                                        $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                                }

                                if ($ntext !== null) {
                                        $this->setMessage(Text::plural($ntext, count($cid)));
                                }
                        } catch (\Exception $e) {
                                $this->setMessage($e->getMessage(), 'error');
                        }
                }

                $extension = $this->input->get('extension');
                $extensionURL = $extension ? '&extension=' . $extension : '';
                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
        }

        /**
         * Change status of dividend
         */
        public function set_split_type()
        {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                // Get items to publish from the request.
                $ids = $this->input->get('cid', array(), 'array');
                $cid = array();
                foreach ($ids as $value) {
                        $id_explode = explode('_', $value);
                        $cid[] = $id_explode[0];
                }
                $cid = array_unique($cid);

                if (empty($cid)) {
                        $this->app->getLogger()->warning(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), array('category' => 'jerror'));
                } else {

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        try {
                                $db = Factory::getDbo();
                                $query = $db->getQuery(true);
                                
                                //get split_type
                                $task = $this->getTask();
                                $query->select('a.id')
                                        ->from('#__phmoney_split_types as a')
                                        ->where('a.value LIKE ' . $db->quote($task));
                                $db->setQuery($query);
                                $split_type_id = $db->loadResult();
                                
                                //publish the split items
                                $cid_in = ArrayHelper::toInteger($cid);
                                $cid_in = implode(',', $cid_in);
                                $query->clear();
                                $query->update('#__phmoney_splits as a')
                                        ->set('a.split_type_id = ' . $db->quote($split_type_id))
                                        ->where($db->quoteName('a.transaction_id') . ' IN (' . $cid_in . ')')
                                        ->join('LEFT', $db->quoteName('#__phmoney_accounts') . ' AS ac ON ac.id = a.account_id')
                                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = ac.account_type_id')
                                        ->where('at.value = ' . $db->q('share'))
                                        ->where('a.shares = 0');
                                $db->setQuery($query);
                                $db->execute();
                        } catch (\Exception $e) {
                                $this->setMessage($e->getMessage(), 'error');
                        }
                }

                $this->setMessage(Text::_('COM_PHMONEY_SUCCESS'));
                $extension = $this->input->get('extension');
                $extensionURL = $extension ? '&extension=' . $extension : '';
                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
        }

        /**
         * Set account filter and redirect to splits view
         */
        public function setAccount()
        {
                $account_id = $this->input->getInt("account_id", 0);
                $model = $this->getModel('Splits');
                $model->setAccount($account_id);
                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

        public function print_view()
        {
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
        }

}
