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

$title = $this->escape(Text::_($this->state->get('filter.title')));
if (empty($title)) {
        $title = Text::_('COM_PHMONEY_TYPE_BALANCE');
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
                <th class="nowrap">
                    <?php echo Text::_('COM_PHMONEY_TYPE'); ?>
                </th>                               
                <th class="nowrap">
                    <?php echo $this->items[0]->portfolio_currency_name; ?>
                </th>                                             
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items2 as $i => $item) : ?>
                    <?php if (isset($item->portfolio_currency_symbol)) : ?>
                            <tr>                                        
                                <td class="text-left">
                                    <?php echo Text::_($item->name); ?>     
                                </td>                                        
                                <td>
                                    <?php echo PhmoneyHelper::showMoney($item->balance, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value); ?>
                                </td>                                                                                                 
                            </tr>
                    <?php endif; ?>
            <?php endforeach; ?>
        </tbody>       
    </table>
</div>