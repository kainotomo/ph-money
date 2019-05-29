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
        $title = Text::_('COM_PHMONEY_INCOME_STATEMENT');
}

$net_for_period = 0;
$start_date = new DateTime($this->escape($this->state->get('filter.start_date')));
$end_date = new DateTime($this->escape($this->state->get('filter.end_date')));
$date_str = Text::plural('COM_PHMONEY_PERIOD_COVERING', $start_date->format('F j, Y'), $end_date->format('F j, Y'));
?>

<div class="card-header">
    <p>
        <?php if (isset($this->portfolio->params->company_name)) {echo $this->portfolio->params->company_name;} else {echo '-';} ?>
        <span class="pull-right"><i><?php echo $date_str;?></i></span>
    </p>
    <h3 class="text-center">
        <?php echo $title; ?>
    </h3>    
</div>
<div class="card-body">
    <table class="table">
        <caption><?php echo date('r'); ?></caption>
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
                    <tr>
                        <td>
                            <?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
                            <?php if ($item->level == 1) : ?>
                                    <?php echo '<strong>' . $this->escape($item->title) . '</strong>'; ?>
                            <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>                            
                            <span class="small" title="<?php echo $this->escape($item->path); ?>">
                                <?php if (!empty($item->note)) : ?>
                                        <?php echo $this->escape($item->note); ?>
                                <?php endif; ?>
                            </span>
                            <span class="small">
                                <?php if (!empty($item->code)) : ?>
                                        <?php echo $this->escape($item->code); ?>
                                <?php endif; ?>
                            </span>
                        </td>                                        
                        <td>
                            <?php if ($item->level != 1) : ?>
                                    <?php echo PhmoneyHelper::showMoney($item->value_portfolio_total, $item->currency_symbol, $item->currency_denom, $item->account_type_value); ?>
                            <?php endif; ?>
                            <?php if ($item->level == 1 && $item->value_portfolio > 0) : ?>
                                    <?php echo PhmoneyHelper::showMoney($item->value_portfolio, $item->currency_symbol, $item->currency_denom, $item->account_type_value); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            if ($item->level == 1) {
                                    $net_for_period -= $item->value_portfolio_total;                                    
                                    echo '<strong>' . PhmoneyHelper::showMoney($item->value_portfolio_total, $item->portfolio_currency_symbol, $item->portfolio_currency_denom, $item->account_type_value) . '</strong>';
                            }
                            ?>                           
                        </td>                                                                                                   
                    </tr>
            <?php endforeach; ?>
                    <tr>
                <th colspan="2">
                    <?php echo Text::_('COM_PHMONEY_NET_FOR_PERIOD'); ?>
                </th>
                <th>
                    <?php echo PhmoneyHelper::showMoney($net_for_period, $this->items[0]->portfolio_currency_symbol, $this->items[0]->portfolio_currency_denom); ?>
                </th>
            </tr>
        </tbody> 
    </table>
</div>