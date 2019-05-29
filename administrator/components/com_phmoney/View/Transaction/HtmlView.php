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

namespace Joomla\Component\Phmoney\Administrator\View\Transaction;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Cms\Table\Table;

/**
 * View to edit an transaction.
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
     * The toolbar title
     *
     * @var string
     */
    protected $toolbarTitle;

    /**
     * The toolbar icon
     *
     * @var string
     */
    protected $toolbarIcon;

    /**
     * The preview link
     *
     * @var string
     */
    protected $previewLink;

    /**
     * The help link
     *
     * @var string
     */
    protected $helpLink;

    public function __construct(array $config) {
        $config = array_merge($config, array('help_link' => "http://www.kainotomo.com/products/ph-money/documentation"));
        parent::__construct($config);
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     *
     
     */
    public function display($tpl = null) {

        // Prepare view data
        $this->initializeView();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \JViewGenericdataexception(implode("\n", $errors), 500);
        }

        // Build toolbar
        $this->addToolbar();
        $this->addButtonbar();

        $document = Factory::getDocument();
        $document->addScript(\Joomla\CMS\Uri\Uri::root() . 'media/com_phmoney/js/transaction_edit.js');

        // Render the view
        return parent::display($tpl);
    }

    /**
     * Prepare view data
     *
     * @return  void
     */
    protected function initializeView() {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = PhmoneyHelper::getActions('com_phmoney', 'transaction', $this->item->id);

        // Set default toolbar title
        if ($this->item->id) {
            $this->toolbarTitle = \JText::_(strtoupper($this->option . '_MANAGER_' . $this->getName() . '_EDIT'));
        } else {
            $this->toolbarTitle = \JText::_(strtoupper($this->option . '_MANAGER_' . $this->getName() . '_NEW'));
        }
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
                ToolbarHelper::preferences('com_phmoney');
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
        if ($isNew && (count($user->getAuthorisedCategories('com_phmoney', 'core.create')) > 0)) {

            $toolbarButtons = [];
                        $toolbarButtons[] = Array('task' => 'transaction.save', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-check', 'list' => false, 'group' => false);
                        $toolbarButtons[] = Array('task' => 'transaction.apply', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-save', 'list' => false, 'group' => true);
            $toolbarButtons[] = Array('task' => 'transaction.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);

            $html[] = PhmoneyHelper::saveButtonGroup(
                            $toolbarButtons, 'btn-success'
            );

            $html[] = $standarButtonLayout->render(Array('task' => 'transaction.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CANCEL', 'icon' => 'fa-close', 'list' => false, 'group' => false));
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            $toolbarButtons = [];

            if (!$checkedOut && $itemEditable) {
                                $toolbarButtons[] = Array('task' => 'transaction.save', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE', 'icon' => 'fa-check', 'list' => false, 'group' => false);
                                $toolbarButtons[] = Array('task' => 'transaction.apply', 'class' => 'btn-outline-success', 'alt' => 'JTOOLBAR_APPLY', 'icon' => 'fa-save', 'list' => false, 'group' => true);

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    $toolbarButtons[] = Array('task' => 'transaction.save2new', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AND_NEW', 'icon' => 'fa-plus', 'list' => false, 'group' => true);
                }
            }

            if ($canDo->get('core.create')) {
                $toolbarButtons[] = Array('task' => 'transaction.save2copy', 'class' => 'btn-outline-secondary', 'alt' => 'JTOOLBAR_SAVE_AS_COPY', 'icon' => 'fa-copy', 'list' => false, 'group' => true);
            }

            $html[] = PhmoneyHelper::saveButtonGroup(
                            $toolbarButtons, 'btn-success'
            );
                
            $html[] = $standarButtonLayout->render(Array('task' => 'transaction.cancel', 'class' => 'btn-outline-danger', 'alt' => 'JTOOLBAR_CLOSE', 'icon' => 'fa-close', 'list' => false, 'group' => false));
        
            if (\JComponentHelper::isEnabled('com_contenthistory') && $itemEditable && $this->state->params->get('save_history', 0)) {
                
                $versionButtonLayout = new FileLayout('toolbar.buttons.versions', null, Array('client' => 'admin'));
                
                $lang = \JFactory::getLanguage();
		$lang->load('com_contenthistory', JPATH_ADMINISTRATOR, $lang->getTag(), true);

		/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
                $typeAlias = 'com_phmoney.transaction';
		$contentTypeTable = Table::getInstance('Contenttype');
                                $typeId = $contentTypeTable->getTypeId($typeAlias);
                
                // Options array for JLayout
                $options = array();
                $options['title'] = Text::_(' Versions');
                $options['itemId'] = $this->item->id;
                $options['typeId'] = $typeId;
                $options['typeAlias'] = $typeAlias;
                
                $html[] = $versionButtonLayout->render($options);
            }
            }

        $this->buttonbar = implode('', $html);
    }

}
