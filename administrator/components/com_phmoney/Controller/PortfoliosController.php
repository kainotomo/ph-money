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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * Description of PortfoliosController
 *
 */
class PortfoliosController extends AdminController
{

        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
        {
                parent::__construct($config, $factory, $app, $input);

                $this->registerTask('delete_prices', 'delete_transactions');
                $this->registerTask('delete_accounts', 'delete_transactions');
        }

        public function getModel($name = 'Portfolio', $prefix = '', $config = array())
        {
                return parent::getModel($name, $prefix, $config);
        }

        /**
         * Method to set the default property for a list of items
         *
         * @return  void
         *
         */
        public function setDefault()
        {
                // Check for request forgeries
                Session::checkToken('request') or die(Text::_('JINVALID_TOKEN'));

                $app = $this->app;

                // Get items to publish from the request.
                $cid = $this->input->get('cid', array(), 'array');
                $data = array('setDefault' => 1, 'unsetDefault' => 0);
                $task = $this->getTask();
                $value = ArrayHelper::getValue($data, $task, 0, 'int');

                if (empty($cid)) {
                        $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
                } else {
                        // Get the model.
                        $model = $this->getModel();

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        // Publish the items.
                        if ($model->setDefault($cid[0], $value)) {
                                $this->setMessage(Text::_('COM_PHMONEY_SET_DEFAULT'));
                        }
                }

                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_list, false
                        )
                );
        }

        /**
         * Mass delete all transactions if task delete_transactions
         * or all prices transactions if task delete_prices
         * 
         * @return void
         */
        public function delete_transactions()
        {
                // Check for request forgeries
                Session::checkToken('request') or die(Text::_('JINVALID_TOKEN'));

                // Get items to publish from the request.
                $cid = $this->input->get('cid', array(), 'array');

                if (empty($cid)) {
                        $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
                } else {
                        // Get the model.
                        $model_portfolio = $this->getModel();
                        $model_transaction = $this->getModel('Transaction');
                        $model_account = $this->getModel('Account');

                        // Make sure the item ids are integers
                        $cid = ArrayHelper::toInteger($cid);

                        // Remove the items.
                        $deleted_num = 0;
                        foreach ($cid as $portfolio_id) {
                                try {
                                        $account_ids = $model_portfolio->getAccounts($portfolio_id, $this->getTask());
                                        $model_account->delete($account_ids);
                                        $deleted_num += count($account_ids);
                                        $transaction_ids = $model_portfolio->getTransactions($portfolio_id, $this->getTask());
                                        $model_transaction->delete($transaction_ids);
                                        $deleted_num += count($transaction_ids);
                                } catch (\Throwable $exc) {
                                        $this->setMessage($exc->getCode() . ' - ' . $exc->getMessage(), 'error');
                                }
                        }

                        $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', $deleted_num));
                }

                $this->setRedirect(
                        Route::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_list, false
                        )
                );
        }

}
