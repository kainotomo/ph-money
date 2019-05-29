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
use Joomla\CMS\Session\Session;

/**
 * Description of AccountController
 */
class AccountController extends FormController {

        /**
         * Constructor.
         *
         * @param   array                 $config       An optional associative array of configuration settings.
         *                                              Recognized key values include 'name', 'default_task', 'model_path', and
         *                                              'view_path' (this list is not meant to be comprehensive).
         * @param   MVCFactoryInterface   $factory      The factory.
         * @param   CMSApplication        $app          The JApplication for the dispatcher
         * @param   \JInput               $input        Input
         * @param   FormFactoryInterface  $formFactory  The form factory.
         *
         */
        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null, \Joomla\CMS\Form\FormFactoryInterface $formFactory = null) {
                parent::__construct($config, $factory, $app, $input, $formFactory);
                $this->registerTask('download', 'calculate');
        }

        /**
         * Method to download a share statistic data.
         *
         * @param   string  $key     The name of the primary key of the URL variable.
         * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
         *
         * @return  boolean  True if successful, false otherwise.
         *
         */
        public function calculate($key = null, $urlVar = null) {
                // Check for request forgeries.
                Session::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

                $app = \JFactory::getApplication();
                $model = $this->getModel();
                $table = $model->getTable();
                $data = $this->input->post->get('jform', array(), 'array');
                $checkin = property_exists($table, $table->getColumnAlias('checked_out'));
                $context = "$this->option.edit.$this->context";
                $task = $this->getTask();

                // Determine the name of the primary key for the data.
                if (empty($key)) {
                        $key = $table->getKeyName();
                }

                // To avoid data collisions the urlVar may be different from the primary key.
                if (empty($urlVar)) {
                        $urlVar = $key;
                }

                $recordId = $this->input->getInt($urlVar);

                // Populate the row id from the session.
                $data[$key] = $recordId;

                //download share statistics
                $quote = null;
                if ($task == 'download') {
                        $quote = $model->download($data['code']);
                }

                // Check for validation errors.
                if ($model->calculateIntrinsicValue($data, $quote) === false) {
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

                        // Redirect back to the edit screen.
                        $this->setRedirect(
                                \JRoute::_(
                                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                                )
                        );

                        return false;
                }

                // Access check.
                if (!$this->allowSave($data, $key)) {
                        $this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

                        $this->setRedirect(
                                \JRoute::_(
                                        'index.php?option=' . $this->option . '&view=' . $this->view_list
                                        . $this->getRedirectToListAppend(), false
                                )
                        );

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

                        // Redirect back to the edit screen.
                        $this->setRedirect(
                                \JRoute::_(
                                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                                )
                        );

                        return false;
                }

                if (!isset($validData['tags'])) {
                        $validData['tags'] = array();
                }

                // Attempt to save the data.
                if (!$model->save($validData)) {
                        // Save the data in the session.
                        $app->setUserState($context . '.data', $validData);

                        // Redirect back to the edit screen.
                        $this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

                        $this->setRedirect(
                                \JRoute::_(
                                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                                )
                        );

                        return false;
                }

                // Save succeeded, so check-in the record.
                if ($checkin && $model->checkin($validData[$key]) === false) {
                        // Save the data in the session.
                        $app->setUserState($context . '.data', $validData);

                        // Check-in failed, so go back to the record and display a notice.
                        $this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');

                        $this->setRedirect(
                                \JRoute::_(
                                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                                        . $this->getRedirectToItemAppend($recordId, $urlVar), false
                                )
                        );

                        return false;
                }

                $langKey = $this->text_prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
                $prefix = \JFactory::getLanguage()->hasKey($langKey) ? $this->text_prefix : 'JLIB_APPLICATION';

                $this->setMessage(\JText::_($prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));

                // Redirect the user and adjust session state based on the chosen task.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);
                $model->checkout($recordId);

                // Redirect back to the edit screen.
                $this->setRedirect(
                        \JRoute::_(
                                'index.php?option=' . $this->option . '&view=' . $this->view_item
                                . $this->getRedirectToItemAppend($recordId, $urlVar), false
                        )
                );

                // Invoke the postSave method to allow for the child class to access the model.
                $this->postSaveHook($model, $validData);

                return true;
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
                \JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

                // Set the model
                /** @var \Joomla\Component\Phmoney\Administrator\Model\TransactionModel $model */
                $model = $this->getModel('Account', 'Administrator', array());

                // Preset the redirect
                $this->setRedirect(\JRoute::_('index.php?option=com_phmoney&view=accounts' . $this->getRedirectToListAppend(), false));

                return parent::batch($model);
        }

}
