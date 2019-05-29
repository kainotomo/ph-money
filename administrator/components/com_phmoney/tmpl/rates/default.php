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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');

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
if (Factory::getApplication()->input->getString('print') == 'true' && Factory::getApplication()->input->getString('tmpl') == 'component') {
        $print = true;
}

$class_table = ComponentHelper::getParams('com_phmoney')->get('class_table');
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


    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=rates'); ?>" method="post" name="adminForm" id="adminForm">

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
                    <table class="<?php echo $class_table; ?>" id="emailList">
                        <caption>
                            My test caption
                        </caption>
                        <thead>
                            <tr>
                                <th style="width:1%" class="nowrap text-center hidden-sm-down">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 't.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                                </th>
                                <th style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th style="min-width:100px" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHMONEY_CURRENCY', 'p.title', $listDirn, $listOrder); ?>
                                </th>
                                <th style="min-width:100px" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHMONEY_RATE', 'a.value', $listDirn, $listOrder); ?>
                                </th>                                
                                <th style="min-width:100px" class="nowrap hidden-sm-down text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 't.post_date', $listDirn, $listOrder); ?>
                                </th>
                                <th style="width:3%" class="nowrap hidden-sm-down text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                            <?php
                            foreach ($this->items as $i => $item) :
                                    $item->max_ordering = 0;
                                    $ordering = ($listOrder == 't.ordering');
                                    ?>
                                    <tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->id; ?>">
                                        <td class="order nowrap text-center hidden-sm-down">                                            
                                        </td>
                                        <td class="text-center">
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                        </td>                                        
                                        <td class="has-context">
                                            <div class="break-word">                                                
                                                <?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
                                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_phmoney&task=rate.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->currency_name)); ?>">
                                                    <?php echo $editIcon; ?><?php echo $this->escape($item->id . ' - ' . $item->currency_name); ?></a>                                                
                                            </div>
                                        </td>
                                        <td class="nowrap small">
                                            <?php echo '1 ' . $item->portfolio_currency . ' = ' . $item->inverse_value . ' ' . $item->currency_name; ?>
                                            <br/>
                                            <?php echo '1 ' . $item->currency_name . ' = ' . $item->value . ' ' . $item->portfolio_currency; ?>                                               
                                        </td>                                                                                
                                        <td class="nowrap small hidden-sm-down text-center">
                                            <?php
                                            echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC5'));
                                            ?>
                                        </td>
                                        <td class="hidden-sm-down text-center">
                                            <?php echo (int) $item->id; ?>
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