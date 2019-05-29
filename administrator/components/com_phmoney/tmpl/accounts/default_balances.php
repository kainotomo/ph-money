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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

$title = $this->escape(Text::_($this->state->get('filter.title')));
if (empty($title)) {
        $title = Text::_('COM_PHMONEY_ACCOUNTS_SUMMARY');
}

$date_str = '';
$start_date_str = $this->escape($this->state->get('filter.start_date'));
if (!empty($start_date_str)) {
        $start_date = new DateTime($start_date_str);
}

$end_date_str = $this->escape($this->state->get('filter.end_date'));
if (!empty($end_date_str)) {
        $end_date = new DateTime($end_date_str);
}

if (empty($start_date_str) && empty($end_date_str)) {
        $date_str = date('r');
}
if (!empty($start_date_str) && !empty($end_date_str)) {
        $date_str = Text::plural('COM_PHMONEY_PERIOD_COVERING', $start_date->format('F j, Y'), $end_date->format('F j, Y'));
}
if (!empty($start_date_str) && empty($end_date_str)) {
        $date_str = Text::plural('COM_PHMONEY_PERIOD_COVERING', $start_date->format('F j, Y'), date('F j, Y'));
}
if (empty($start_date_str) && !empty($end_date_str)) {
        $date_str = $end_date->format('F j, Y');
}
?>

<div class="card-header">
    <p>
        <?php
        if (isset($this->portfolio->params->company_name)) {
                echo $this->portfolio->params->company_name;
        } else {
                echo '-';
        }
        ?>
        <span class="pull-right"><i><?php echo $date_str; ?></i></span>
    </p>
    <h3 class="text-center">
        <?php echo $title; ?>
    </h3>    
</div>
<div class="card-body">
    <table class="table">               
        <caption><?php echo date('r'); ?></caption>
        <thead>
            <tr>                                
                <th style="width:1%" class="text-center">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <th style="width:7%" class="nowrap text-center">
                    <?php echo Text::_('JSTATUS'); ?>
                </th>
                <th class="nowrap">
                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                </th>                               
                <th class="nowrap">
                    <?php echo Text::_('COM_PHMONEY_VALUE'); ?>
                </th>
                <th class="nowrap">
                    <?php echo $this->items[0]->portfolio_currency_name; ?>
                </th>
                <th class="text-right">
                    <?php echo Text::_('COM_PHMONEY_TYPE'); ?>
                </th>                                   
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    // Get the parents of item for sorting
                    if ($item->level > 1) {
                            $parentsStr = '';
                            $_currentParentId = $item->parent_id;
                            $parentsStr = ' ' . $_currentParentId;
                            for ($i2 = 0; $i2 < $item->level; $i2++) {
                                    foreach ($this->ordering as $k => $v) {
                                            $v = implode('-', $v);
                                            $v = '-' . $v . '-';
                                            if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                                                    $parentsStr .= ' ' . $k;
                                                    $_currentParentId = $k;
                                                    break;
                                            }
                                    }
                            }
                    } else {
                            $parentsStr = '';
                    }
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">                                        
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'accounts.', true); ?>
                            </div>
                        </td>
                        <td>
                            <?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
                            <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'accounts.', true); ?>
                            <?php endif; ?>
                            <?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
                            <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_phmoney&task=account.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
                                <?php echo $editIcon; ?></a>
                            <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_phmoney&task=splits.setAccount&account_id=' . $item->id); ?>" title="<?php echo $this->escape(addslashes($item->title)); ?> <?php echo JText::_('COM_PHMONEY_TRANSACTIONS'); ?>">
                                <?php echo $this->escape($item->title); ?></a>
                            <span class="small" title="<?php echo $this->escape($item->path); ?>">
                                <?php if (!empty($item->note)) : ?>
                                        <?php echo $this->escape($item->note); ?>
                                <?php endif; ?>
                            </span>
                            <span class="small">
                                <?php if (!empty($item->code)) : ?>
                                        <?php echo $this->escape($item->code); ?>
                                <?php endif; ?>
                                <?php if ($item->shares != 0) : ?>
                                        <span class="text text-info">
                                            <i>
                                                <?php echo ' (' . (float) $item->shares . ')'; ?>
                                            </i>        
                                        </span>
                                <?php endif; ?>
                            </span>
                        </td>                                        
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->value, $item->currency_symbol, $item->currency_denom, $item->account_type_value); ?>
                        </td>
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->value_portfolio_total, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                        </td>
                        <td class="text-right">
                            <?php echo Text::_($item->account_type_name); ?>     
                        </td>                                                                             
                    </tr>
            <?php endforeach; ?>
        </tbody>       
    </table>
</div>