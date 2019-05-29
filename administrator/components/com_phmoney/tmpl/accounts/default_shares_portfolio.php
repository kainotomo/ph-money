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
        $title = Text::_('COM_PHMONEY_PORTFOLIO');
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

$total_money_in = 0;
$total_money_out = 0;
$total_basis = 0;
$total_value = 0;
$total_dividends = 0;
$total_unrealized_gain = 0;
$total_realized_gain = 0;
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
                    <?php echo Text::_('COM_PHMONEY_MONEY_IN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_MONEY_OUT'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_BASIS'); ?>
                </th>  
                <th>
                    <?php echo Text::_('COM_PHMONEY_VALUE'); ?>
                </th>                                
                <th>
                    <?php echo Text::_('COM_PHMONEY_UNREALIZED_GAIN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_REALIZED_GAIN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_DIVIDENDS') . ' &<br/>' . Text::_('COM_PHMONEY_FEES'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_TOTAL_RETURN'); ?>
                </th>
                <th>
                    <?php echo Text::_('COM_PHMONEY_RATE_RETURN'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    $total_money_in += $item->money_in_portfolio;
                    $total_money_out += $item->money_out_portfolio;
                    $total_basis += $item->basis_portfolio;
                    $total_value += $item->value_portfolio;
                    $total_dividends += $item->dividends_portfolio;
                    $total_unrealized_gain += $item->unrealized_gain_portfolio;
                    $total_realized_gain += $item->realized_gain_portfolio;
                    $total_total_return += $item->total_return_portfolio;
                    ?>
                    <tr>
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
                            <?php echo PhmoneyHelper::showMoney($item->money_in_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->money_in, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->money_out_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->money_out, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->basis_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->basis, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
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
                            <?php echo PhmoneyHelper::showMoney($item->unrealized_gain_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->unrealized_gain, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td> 
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->realized_gain_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->realized_gain, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
                                    echo '</small>';
                            }
                            ?>
                        </td>  
                        <td>
                            <?php echo PhmoneyHelper::showMoney($item->dividends_portfolio, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>                            
                            <?php
                            if ($item->currency_symbol != $item->portfolio_currency_symbol) {
                                    echo '<br/><small>';
                                    echo PhmoneyHelper::showMoney($item->dividends, $item->currency_symbol, $item->currency_denom, $item->account_type_value);
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
                    </tr>
            <?php endforeach; ?>
        </tbody>   
        <tfoot>
            <tr>
                <th>
                    <?php echo Text::_('COM_PHMONEY_TOTAL'); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_money_in, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_money_out, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_basis, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_value, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>                
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_unrealized_gain, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_realized_gain, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_dividends, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($total_total_return, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                </th>
                <th>
                    <?php 
                    $total_rate_of_return = $total_total_return / $total_money_in * 100;
                    echo number_format($total_rate_of_return, 2) . '%'; 
                    ?>
                </th>
            </tr>

        </tfoot>
    </table>
</div>