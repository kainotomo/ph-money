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

use Joomla\CMS\Language\Text;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\HTML\HTMLHelper;

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
    <div class="row row-title">  
        <div class="col-1 font-weight-bold">
            <?php echo HTMLHelper::_('grid.checkall'); ?>
        </div>        
        <div class="col font-weight-bold">
            <?php echo Text::_('JGLOBAL_TITLE'); ?>
        </div>                               
        <div class="col-2 font-weight-bold">
            <?php echo Text::_('COM_PHMONEY_VALUE'); ?>
        </div>
        <div class="col-2 font-weight-bold">
            <?php echo $this->items[0]->portfolio_currency_name; ?>
        </div>
        <div class="col-1 font-weight-bold">
            <?php echo Text::_('COM_PHMONEY_TYPE'); ?>
        </div>  
        <div class="col-1 font-weight-bold text-left">
            <?php echo Text::_('JSTATUS'); ?>
        </div>
    </div>
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
            <?php
            if (isset($this->items[$i - 1])) {
                    if ($item->level > $this->items[$i - 1]->level) {
                            echo '<div class="collapse multi-collapse" id="account-' . $item->id . '">';
                    }
                    if ($item->level < $this->items[$i - 1]->level) {
                            $divs = - $item->level + $this->items[$i - 1]->level;
                            for ($j = 0; $j < $divs; $j++) {
                                    echo '</div>';
                            }
                    }
            }
            ?>
            <div class="row">
                <div class="col-1">
                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                </div>                
                <div class="col">                    
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
                </div>     
                <div class="col-2">
                    <?php echo PhmoneyHelper::showMoney($item->value, $item->currency_symbol, $item->currency_denom, $item->account_type_value); ?>
                </div>
                <div class="col-2">
                    <?php echo PhmoneyHelper::showMoney($item->value_portfolio_total, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </div>
                <div class="col-1">
                    <?php echo Text::_($item->account_type_name); ?>                       
                </div>   
                <div class="col-1 text-center">
                    <div class="float-left">
                        <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'accounts.', true); ?>
                    </div>
                    <?php if (isset($this->items[$i + 1])) : ?>
                            <?php if ($item->level < $this->items[$i + 1]->level) : ?>
                                    <a id="account-btn-<?php echo $this->items[$i + 1]->id; ?>" 
                                       href="#account-<?php echo $this->items[$i + 1]->id; ?>"
                                       data-toggle="collapse" 
                                       data-toggle="button" 
                                       class="btn btn-xs float-right" >
                                        <span class="fa fa-plus" aria-hidden="true"></span>
                                    </a>
                            <?php endif; ?>                                
                    <?php endif; ?>
                </div>
            </div>   

    <?php endforeach; ?>
</div>

<script language="javascript">
        jQuery(function ($) {
            $('.card-body').find('[id^=account-btn-]').each(function (index, btn) {
                var btn = $(btn);
                btn.on('click', function () {
                    btn.find('span').toggleClass('fa fa-plus');
                    btn.find('span').toggleClass('fa fa-minus');
                });
            });
        });
</script>