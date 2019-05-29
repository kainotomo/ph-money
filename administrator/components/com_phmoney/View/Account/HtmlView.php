<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_phmoney
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Phmoney\Administrator\View\Account;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;

/**
 * View to edit an split.
 *
 */
class HtmlView extends BaseHtmlView {

        /**
         * The buttonbar markup
         * @var string
         */
        protected $buttonbar;

        /**
         * The \JForm object
         *
         * @var  \JForm
         */
        protected $form;

        /**
         * The active item
         *
         * @var  object
         */
        protected $item;

        /**
         * The model state
         *
         * @var  object
         */
        protected $state;

        /**
         * The actions the user is authorised to perform
         *
         * @var  \JObject
         */
        protected $canDo;

        /**
         * Pagebreak TOC alias
         *
         * @var  string
         */
        protected $eName;

        /**
         * Execute and display a template script.
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  mixed  A string if successful, otherwise an Error object.
         *
         
         */
        public function display($tpl = null) {
                
                Factory::getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);
                
                if ($this->getLayout() == 'pagebreak') {
                        return parent::display($tpl);
                }

                $this->form = $this->get('Form');
                $this->item = $this->get('Item');
                $this->state = $this->get('State');
                $this->canDo = PhmoneyHelper::getActions('com_phmoney', 'split', $this->item->id);

                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                        throw new \JViewGenericdataexception(implode("\n", $errors), 500);
                }

                // If we are forcing a language in modal (used for associations).
                if ($this->getLayout() === 'modal' && $forcedLanguage = \JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd')) {
                        // Set the language field to the forcedLanguage and disable changing it.
                        $this->form->setValue('language', null, $forcedLanguage);
                        $this->form->setFieldAttribute('language', 'readonly', 'true');

                        // Only allow to select categories with All language or with the forced language.
                        $this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

                        // Only allow to select tags with All language or with the forced language.
                        $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
                }

                $this->addToolbar();
                $this->addButtonbar();

                return parent::display($tpl);
        }

        /**
         * Add the page title and toolbar.
         *
         * @return  void
         */
        protected function addToolbar() {
                Factory::getApplication()->input->set('hidemainmenu', true);

                // Since we don't track these assets at the item level, use the category id.
                $canDo = $this->canDo;

                if (Factory::getApplication()->isClient('administrator')) {
                        if ($canDo->get('core.admin')) {
                                ToolbarHelper::preferences('com_phcloud');
                        }
                        ToolbarHelper::help(NULL, FALSE, "http://www.kainotomo.com/products/ph-money/documentation");
                }

                //only for front end
                if (Factory::getApplication()->isClient('site')) {
                        $this->bar = Toolbar::getInstance('toolbar')->render();
                }
        }

        /**
         * Add buttonbar markup
         */
        protected function addButtonbar() {
                Factory::getApplication()->input->set('hidemainmenu', true);
                $user = \JFactory::getUser();
                $userId = $user->id;
                $isNew = ($this->item->id == 0);
                $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
                $canDo = $this->canDo;

                $html = array();
                $standarButtonLayout = new FileLayout('toolbar.buttons.standardbutton', null, Array('client' => 'admin'));

                // For new records, check the create permission.
                if ($isNew) {

                        $toolbarButtons = [];
                        $toolbarButtons[] = Array('task' => 'account.apply', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-save', 'list' => false, 'group' => false);
                        $toolbarButtons[] = Array('task' => 'account.save', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-check', 'list' => false, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'account.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);

                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-success'
                        );

                        $html[] = $standarButtonLayout->render(Array('task' => 'account.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CANCEL', 'icon' => 'fa-close', 'list' => false, 'group' => false));
                } else {
                        // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
                        $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

                        $toolbarButtons = [];

                        if (!$checkedOut && $itemEditable) {
                                $toolbarButtons[] = Array('task' => 'account.apply', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-save', 'list' => false, 'group' => false);
                                $toolbarButtons[] = Array('task' => 'account.save', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-check', 'list' => false, 'group' => true);

                                // We can save this record, but check the create permission to see if we can return to make a new one.
                                if ($canDo->get('core.create')) {
                                        $toolbarButtons[] = Array('task' => 'account.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);
                                }
                        }

                        if ($canDo->get('core.create')) {
                                $toolbarButtons[] = Array('task' => 'account.save2copy', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AS_COPY', 'icon' => 'fa-copy', 'list' => false, 'group' => true);
                        }

                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-success'
                        );
                        
                        if ($this->item->account_type_id == 2 && !empty($this->item->code)) {
                                $html[] = $standarButtonLayout->render(Array('task' => 'account.calculate', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_CALCULATE', 'icon' => 'fa-cog', 'list' => false, 'group' => false, 'confirm' => false));
                                $html[] = $standarButtonLayout->render(Array('task' => 'account.download', 'class' => 'btn-outline-secondary', 'alt' => 'COM_PHMONEY_DOWNLOAD', 'icon' => 'fa-download', 'list' => false, 'group' => false, 'confirm' => false));
                        }
                        $html[] = $standarButtonLayout->render(Array('task' => 'account.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CLOSE', 'icon' => 'fa-close', 'list' => false, 'group' => false));
                }

                $this->buttonbar = implode('', $html);
        }

        /**
         * Add the page title and toolbar.
         *
         * @return  void
         *
         
         */
        protected function addToolbarOLD() {
                \JFactory::getApplication()->input->set('hidemainmenu', true);
                $user = \JFactory::getUser();
                $userId = $user->id;
                $isNew = ($this->item->id == 0);
                $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

                // Built the actions for new and existing records.
                $canDo = $this->canDo;

                \JToolbarHelper::title(
                        \JText::_('COM_PHMONEY_PAGE_' . ($checkedOut ? 'VIEW_SPLIT' : ($isNew ? 'ADD_SPLIT' : 'EDIT_SPLIT'))), 'pencil-2 split-add'
                );

                // For new records, check the create permission.
                if ($isNew && (count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0)) {
                        \JToolbarHelper::saveGroup(
                                [
                                ['apply', 'split.apply'],
                                ['save', 'split.save'],
                                ['save2new', 'split.save2new']
                                ], 'btn-success'
                        );

                        \JToolbarHelper::cancel('split.cancel');
                } else {
                        // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
                        $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

                        $toolbarButtons = [];

                        // Can't save the record if it's checked out and editable
                        if (!$checkedOut && $itemEditable) {
                                $toolbarButtons[] = ['apply', 'split.apply'];
                                $toolbarButtons[] = ['save', 'split.save'];

                                // We can save this record, but check the create permission to see if we can return to make a new one.
                                if ($canDo->get('core.create')) {
                                        $toolbarButtons[] = ['save2new', 'split.save2new'];
                                }
                        }

                        // If checked out, we can still save
                        if ($canDo->get('core.create')) {
                                $toolbarButtons[] = ['save2copy', 'split.save2copy'];
                        }

                        \JToolbarHelper::saveGroup(
                                $toolbarButtons, 'btn-success'
                        );

                        if (\JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable) {
                                \JToolbarHelper::versions('com_phmoney.split', $this->item->id);
                        }

                        if (!$isNew) {
                                \JLoader::register('PhmoneyHelperPreview', JPATH_ADMINISTRATOR . '/components/com_phmoney/helpers/preview.php');
                                $url = \PhmoneyHelperPreview::url($this->item);
                                \JToolbarHelper::preview($url, \JText::_('JGLOBAL_PREVIEW'), 'eye', 80, 90);
                        }

                        \JToolbarHelper::cancel('split.cancel', 'JTOOLBAR_CLOSE');
                }

                \JToolbarHelper::divider();
                \JToolbarHelper::help('JHELP_PHMONEY_SPLIT_MANAGER_EDIT');
        }

}
