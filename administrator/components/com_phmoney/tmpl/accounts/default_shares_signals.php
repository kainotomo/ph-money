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
        $title = Text::_('COM_PHMONEY_SIGNALS');
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

$total_basis = 0;
$total_value = 0;
$total_dividends = 0;
$total_unrealized_return = 0;
$total_realized_return = 0;
$total_total_return = 0;
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
                <th>
                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                </th>                                  
                <th>
                    <?php echo Text::_('COM_PHMONEY_VALUE'); ?>
                </th>                                
                <th>
                    <?php echo Text::_('COM_PHMONEY_TOTAL_RETURN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_RATE_RETURN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_PRICE'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_INTRINSIC_VALUE'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_SIGNAL'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    $total_basis += $item->basis_portfolio;
                    $total_value += $item->value_portfolio;
                    $total_dividends += $item->dividends_portfolio;
                    $total_unrealized_return += $item->unrealized_return_portfolio;
                    $total_realized_return += $item->realized_return_portfolio;
                    $total_total_return += $item->total_return_portfolio;
                    $class = 'text-muted';
                    if (isset($item->params->signal)) {
                            if ($item->params->signal == Text::_('COM_PHMONEY_BUY')) {
                                    $class = 'text-success';
                            } else {
                                    $class = 'text-danger';
                            }
                    }
                    ?>
                    <tr class="<?php echo $class; ?>">
                        <td>
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
                            <?php echo PhmoneyHelper::showMoney($item->value_portfolio_total, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->value, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td>                           
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->total_return_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->total_return, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td>  
                        <td>
                            <?php echo number_format($item->rate_of_return, 2) . '%'; ?>
                        </td>
                        <td>
                            <?php echo $item->currency_symbol . $item->params->price;?> 
                        </td>  
                        <td>
                            <?php if (isset($item->params->intrinsic_value)) 
                            {
                                    echo $item->currency_symbol . $item->params->intrinsic_value;
                            }
                            ?>                             
                        </td> 
                        <td>
                            <?php
                            if (isset($item->params->signal) && isset($item->params->intrinsic_value)) {
                                    if ($item->params->signal == Text::_('COM_PHMONEY_BUY')) {
                                            echo '<span class="text-success">';
                                    } else {
                                            echo '<span class="text-danger">';
                                    }
                                    echo $item->params->signal;
                                    echo '</span>';
                            }
                            ?>
                        </td> 
                    </tr>
            <?php endforeach; ?>
        </tbody>   
        <tfoot>
            <tr>
                <th>
                    <?php echo Text::_('COM_PHMONEY_TOTAL'); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_value, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_total_return, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th colspan="4">
                    <?php
                    $total_rate_of_return = $total_total_return / $total_basis * 100;
                    echo number_format($total_rate_of_return, 2) . '%';
                    ?>
                </th>
            </tr>
        </tfoot>
    </table>
</div>