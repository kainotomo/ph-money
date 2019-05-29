<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_phmoney
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Phmoney\Administrator\View\Import;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;

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
                $canDo = $this->canDo;

                $html = array();
                $standarButtonLayout = new FileLayout('toolbar.buttons.standardbutton', null, Array('client' => 'admin'));

                // For new records, check the create permission.
                if ($isNew) {

                        $toolbarButtons = [];
                        $toolbarButtons[] = Array('task' => 'import.save', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-check', 'list' => false, 'group' => false);
                        $toolbarButtons[] = Array('task' => 'import.apply', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-save', 'list' => false, 'group' => true);
                        $toolbarButtons[] = Array('task' => 'import.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);

                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-success'
                        );

                        $html[] = $standarButtonLayout->render(Array('task' => 'import.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CANCEL', 'icon' => 'fa-close', 'list' => false, 'group' => false));
                } else {
                        // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
                        $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

                        $toolbarButtons = [];

                        if ($itemEditable) {
                                $toolbarButtons[] = Array('task' => 'import.save', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-save', 'list' => false, 'group' => false);
                                $toolbarButtons[] = Array('task' => 'import.apply', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-check', 'list' => false, 'group' => true);

                                // We can save this record, but check the create permission to see if we can return to make a new one.
                                if ($canDo->get('core.create')) {
                                        $toolbarButtons[] = Array('task' => 'import.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);
                                }
                        }

                        $html[] = PhmoneyHelper::saveButtonGroup(
                                        $toolbarButtons, 'btn-success'
                        );

                        $html[] = $standarButtonLayout->render(Array('task' => 'import.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CLOSE', 'icon' => 'fa-close', 'list' => false, 'group' => false));
                }

                $this->buttonbar = implode('', $html);
        }

}
