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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Implement transaction Controller
 *
 */
class TransactionController extends FormController {

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
        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null) {
                parent::__construct($config, $factory, $app, $input);

                // An split edit form can come from the splits or featured view.
                // Adjust the redirect view on the value of 'return' in the request.
                //if ($this->input->get('return') == 'splits') {
                        $this->view_list = 'splits';
                        $this->view_item = 'transaction&return=splits';
                //}

        }

        /**
         * Method override to check if you can add a new record.
         *
         * @param   array  $data  An array of input data.
         *
         * @return  boolean
         *
         */
        protected function allowAdd($data = array()) {
                $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
                $allow = null;

                if ($categoryId) {
                        // If the category has been passed in the data or URL check it.
                        $allow = Factory::getUser()->authorise('core.create', 'com_phmoney.category.' . $categoryId);
                }

                if ($allow === null) {
                        // In the absense of better information, revert to the component permissions.
                        return parent::allowAdd();
                }

                return $allow;
        }

        /**
         * Method override to check if you can edit an existing record.
         *
         * @param   array   $data  An array of input data.
         * @param   string  $key   The name of the key for the primary key.
         *
         * @return  boolean
         *
         */
        protected function allowEdit($data = array(), $key = 'id') {
                $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
                $user = Factory::getUser();

                // Zero record (id:0), return component edit permission by calling parent controller method
                if (!$recordId) {
                        return parent::allowEdit($data, $key);
                }

                // Check edit on the record asset (explicit or inherited)
                if ($user->authorise('core.edit', 'com_phmoney.transaction.' . $recordId)) {
                        return true;
                }

                // Check edit own on the record asset (explicit or inherited)
                if ($user->authorise('core.edit.own', 'com_phmoney.transaction.' . $recordId)) {
                        // Existing record already has an owner, get it
                        $record = $this->getModel()->getItem($recordId);

                        if (empty($record)) {
                                return false;
                        }

                        // Grant if current user is owner of the record
                        return $user->id == $record->created_by;
                }

                return false;
        }

        /**
         * Method to run batch operations.
         *
         * @param   object  $model  The model.
         *
         * @return  boolean   True if successful, false otherwise and internal error is set.
         *
         */
        public function batch($model = null) {
                Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

                // Set the model
                /** @var \Joomla\Component\Phmoney\Administrator\Model\SplitModel $model */
                $model = $this->getModel('Transaction', 'Administrator', array());

                // Preset the redirect
                $this->setRedirect(\JRoute::_('index.php?option=com_phmoney&view=splits' . $this->getRedirectToListAppend(), false));

                return parent::batch($model);
        }

}
