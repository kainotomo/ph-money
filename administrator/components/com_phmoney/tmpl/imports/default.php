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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

$app = Factory::getApplication();
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 't.ordering';
$assoc = JLanguageAssociations::isEnabled();
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;

$accounts_field = new \Joomla\Component\Phmoney\Administrator\Field\AccountsField();
$accounts_options = $accounts_field->getOptions();

if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = 'index.php?option=com_phmoney&task=newsfeeds.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
        HTMLHelper::_('draggablelist.draggable');
}

$print = false;
if (Factory::getApplication()->input->getString('print') == 'true' && Factory::getApplication()->input->getString('tmpl') == 'component') {
        $print = true;
}
?>

<div class="card">
    <?php if (!$print) : ?>
            <div class="card-header">
                <?php echo $this->navbar; ?>
            </div>
            <div class="card-header-pills">
                <nav class="navbar">
                    <div class="mr-auto">
                        <?php echo $this->buttonbar; ?>                       
                    </div>  
                    <div class=" my-2 my-lg-0">
                    </div>
                </nav>     
            </div>
    <?php endif; ?>


    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=imports'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

        <?php if (!$print) : ?>
                <div class="card-header-pills">
                    <nav class="navbar">  
                        <div class="mr-auto">
                            <?php echo $this->searchbar; ?>
                        </div>  
                        <div class="my-2 my-lg-0">                                      
                        </div>
                    </nav>     
                </div>
        <?php endif; ?>

        <div class="card-body">
            <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
            <?php else : ?>
                    <table class="table table-hover" id="splitList">
                        <thead>
                            <tr>
                                <th style="width:1%" class="text-center">
                                    <?php echo Text::_('COM_PHMONEY_ROW'); ?>
                                </th>
                                <th style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th>
                                    <select name="source_account_all" class="custom-select advancedSelectXXX" 
                                            id="source_account_all" style="display: block;" onchange="PHMONEY.choose_all_accounts(this);">
                                        <option value="0"><?php echo Text::_('COM_PHMONEY_SELECT_SOURCE_ACCOUNT'); ?></option>
                                        <?php foreach ($accounts_options as $option) : ?>
                                                <option value="<?php echo $option->value; ?>"><?php echo $option->text; ?></option>
                                        <?php endforeach; ?>                                                    
                                    </select>
                                </th>
                                <th>
                                    <select name="destination_account_all" class="custom-select advancedSelectXXX" 
                                            id="destination_account_all" style="display: block;" onchange="PHMONEY.choose_all_accounts(this);">
                                        <option value="0"><?php echo Text::_('COM_PHMONEY_SELECT_DESTINATION_ACCOUNT'); ?></option>
                                        <?php foreach ($accounts_options as $option) : ?>
                                                <option value="<?php echo $option->value; ?>"><?php echo $option->text; ?></option>
                                        <?php endforeach; ?>                                                    
                                    </select>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_PHMONEY_CONFIDENCE'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('JDATE'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_PHMONEY_NUM'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('JGLOBAL_DESCRIPTION'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_PHMONEY_AMOUNT'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('JSTATUS'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('MESSAGE'); ?>
                                </th>                                
                            </tr>                                            
                        </thead>
                        <tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                            <?php foreach ($this->items as $i => $item) : ?>
                                    <tr class="row<?php echo $i % 2; ?>">   
                                        <td class="text-center">
                                            <?php echo $item->id; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo HTMLHelper::_('grid.id', $i, $i . '_' . $item->id); ?>
                                        </td>
                                        <td>
                                            <input 
                                                name="source_account[]"
                                                id="source_account<?php echo $item->id; ?>"
                                                type="hidden" 
                                                value="<?php echo $item->account_id_source; ?>"/>  
                                            <input 
                                                name="source_account_name[]"
                                                id="source_account_name<?php echo $item->id; ?>"
                                                class="form-control"
                                                type="text" 
                                                readonly="true" 
                                                value="<?php echo $item->account_name_source; ?>"/>
                                        </td>
                                        <td>
                                            <input 
                                                name="destination_account[]"
                                                id="destination_account<?php echo $item->id; ?>"
                                                type="hidden" 
                                                value="<?php echo $item->account_id_destination; ?>"/>
                                            <input 
                                                name="destination_account_name[]"
                                                id="destination_account_name<?php echo $item->id; ?>"
                                                class="form-control"
                                                type="text" 
                                                readonly="true" 
                                                value="<?php echo $item->account_name_destination; ?>"/>
                                        </td>
                                        <td>
                                            <?php
                                            if (!is_null($item->percent)) {
                                                    echo number_format($item->percent, 0) . '%';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $item->post_date; ?>
                                        </td>
                                        <td>
                                            <?php echo $item->title; ?>
                                        </td>
                                        <td>
                                            <?php echo $item->num; ?>
                                        </td>
                                        <td>
                                            <?php echo $item->description; ?>
                                        </td>
                                        <td>
                                            <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_phmoney&task=import.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->value)); ?>">
                                                <span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($item->value); ?></a>                                    
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <?php echo HTMLHelper::_('imports.status', $item->status, $i, false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $item->message; ?>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>  
                    </table>
            <?php endif; ?>            
            <?php if (!$print): ?>
                    <div class="w-100">
                        <p class="counter float-right pt-3 pr-2">
                            <?php echo $this->pagination->getPagesCounter(); ?>
                        </p>
                        <?php echo $this->pagination->getListFooter(); ?>
                    </div>
            <?php endif; ?>

            <input type="hidden" name="task" value="">
            <input type="hidden" name="boxchecked" value="0">
            <?php echo HTMLHelper::_('form.token'); ?>         
        </div>
    </form>            
</div>

<div class="clearfix"> </div>
