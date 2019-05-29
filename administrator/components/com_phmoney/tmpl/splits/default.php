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
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
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

if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = 'index.php?option=com_phmoney&task=newsfeeds.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
        HTMLHelper::_('draggablelist.draggable');
}

$print = false;
if (Factory::getApplication()->input->getString('tmpl') == 'component') {
        $print = true;
}

$showBalance = false;
$account_id = $this->state->get('filter.account');
if (!empty($account_id)) {
        if (is_numeric($account_id)) {
                $showBalance = true;
        } elseif (is_array($account_id) && count($account_id) == 1) {
                $showBalance = true;
        }
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

    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=splits'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

        <?php // Load the batch processing form. ?>
        <?php
        if ($user->authorise('core.create', 'com_phmoney') && $user->authorise('core.edit', 'com_phmoney') && $user->authorise('core.edit.state', 'com_phmoney')) :
                ?>
                <?php
                echo HTMLHelper::_(
                        'bootstrap.renderModal', 'collapseModal', array(
                        'title' => JText::_(''),
                        'footer' => $this->loadTemplate('batch_footer'),
                        ), $this->loadTemplate('batch_body')
                );
                ?>
        <?php endif; ?>

        <?php if (!$print) : ?>
                <div class="card-header-pills">
                    <nav class="navbar">  
                        <div class="mr-auto">
                        </div>  
                        <div class=" my-2 my-lg-0">
                            <?php echo $this->searchbar; ?>              
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
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>                                
                                <th style="min-width:100px" class="nowrap">
                                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                                </th>
                                <?php if (!$showBalance) : ?>
                                        <th style="min-width:50px" class="nowrap">
                                            <?php echo Text::_('COM_PHMONEY_ACCOUNT'); ?>
                                        </th>
                                <?php endif; ?>
                                <th style="min-width:20px" class="nowrap">
                                    <?php echo Text::_('JDATE'); ?>
                                </th>                                                    
                                <th style="width:10%" class="nowrap hidden-sm-down text-center">
                                    <?php echo Text::_('COM_PHMONEY_DEBIT'); ?>
                                </th>   
                                <th style="width:10%" class="nowrap hidden-sm-down text-center">
                                    <?php echo Text::_('COM_PHMONEY_CREDIT'); ?>
                                </th> 
                                <?php if ($showBalance) : ?>
                                        <th style="width:10%" class="nowrap hidden-sm-down text-center">
                                            <?php echo Text::_('COM_PHMONEY_BALANCE'); ?>
                                        </th> 
                                <?php endif; ?>
                                <th style="width:3%" class="nowrap hidden-sm-down text-center">
                                    <?php echo Text::_('COM_PHMONEY_RECONCILED'); ?>
                                </th> 
                                <th style="width:6%" class="nowrap text-center">
                                    <?php echo Text::_('JSTATUS'); ?>
                                </th>
                                <th style="width:1%" class="nowrap hidden-sm-down text-center hidden">
                                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                                </th> 
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                            <?php
                            foreach ($this->items as $i => $item) :
                                    $item->max_ordering = 0;
                                    $ordering = ($listOrder == 't.ordering');
                                    ?>
                                    <tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->account_id; ?>">                                        
                                        <td class="text-center">
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->transaction_id . '_' . $item->id); ?>
                                        </td>                                        
                                        <td class="has-context">
                                            <div class="break-word">                                             
                                                <?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
                                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_phmoney&task=transaction.edit&return=splits&id=' . $item->transaction_id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
                                                    <?php //echo $editIcon;  ?><?php echo $this->escape($item->title); ?></a>
                                                <div class="small">
                                                    <?php echo $item->num; ?>                                                    
                                                </div>   
                                                <?php if ($item->split_type_value !== 'nan') : ?>
                                                        <div class="small text-info">
                                                            <?php echo Text::_($item->split_type_name); ?>
                                                        </div>
                                                <?php endif; ?>
                                                <?php if ($item->shares != 0) : ?>
                                                        <div class="small">
                                                            <?php echo PhmoneyHelper::showShares($item->shares, $item->price, $item->currency_symbol, $item->denom); ?>
                                                        </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php if (!$showBalance) : ?>
                                                <td class="has-context">
                                                    <span>                                             
                                                        <?php echo $this->escape($item->account_title); ?>
                                                        </div>
                                                        <span class="small">
                                                            <?php echo '(' . Text::_($item->account_type_name) . ')'; ?>
                                                        </span>
                                                </td>
                                        <?php endif; ?>
                                        <td class="nowrap small hidden-sm-down">
                                            <?php
                                            echo HTMLHelper::_('date', $item->post_date, Text::_('Y-m-d'));
                                            ?>
                                        </td>                                                                               
                                        <td class="text-center">
                                            <?php
                                            if ($item->value >= 0) {
                                                    echo PhmoneyHelper::showMoney2($item->value, $item->currency_symbol, $item->denom, $item->account_type_value);
                                            }
                                            ?>
                                        </td> 
                                        <td class="text-center">
                                            <?php
                                            if ($item->value < 0) {
                                                    echo PhmoneyHelper::showMoney2($item->value, $item->currency_symbol, $item->denom, $item->account_type_value);
                                            }
                                            ?>
                                        </td>
                                        <?php if ($showBalance) : ?>
                                                <td class="text-center">
                                                    <?php
                                                    echo PhmoneyHelper::showMoney($item->balance, $item->currency_symbol, $item->denom, $item->account_type_value);
                                                    ?>
                                                </td> 
                                        <?php endif; ?>
                                        <td class="hidden-sm-down text-center">
                                            <div class="btn-group">
                                                <?php echo HTMLHelper::_('split.reconciled', $item->reconcile_state, $i, true); ?>
                                            </div>                                            
                                        </td> 
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'splits.', true, 'cb', true, true); ?>
                                            </div>
                                        </td>
                                        <td class="small hidden-sm-down text-center hidden">
                                            <div class="break-word">  
                                                <?php echo $item->transaction_id; ?>
                                            </div>
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
