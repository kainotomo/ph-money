<?php
/*
 * Copyright (C) 2017 KAINOTOMO PH LTD <info@kainotomo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
?>

<div class="card">
    <div class="card-header">
        <?php echo $this->navbar; ?>
    </div>    
    <div class="card-body">
        <table class="table">
            <caption><a href="https://www.kainotomo.com/products/ph-money/documentation" target="_blank"><span class="fa fa-question-circle mr-2" aria-hidden="true"></span><?php echo Text::_('JHELP'); ?></a></caption>
            <thead>
                <tr>
                    <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                    <th><?php echo Text::_('JGLOBAL_DESCRIPTION'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_phmoney&view=imports'); ?>"><?php echo Text::_('COM_PHMONEY_IMPORT'); ?></a></td>
                    <td><?php echo Text::_('COM_PHMONEY_IMPORT_DESC'); ?></td>
                </tr>
                <tr>
                    <td><a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_phmoney&view=rates'); ?>"><?php echo Text::_('COM_PHMONEY_RATES'); ?></a></td>
                    <td><?php echo Text::_('COM_PHMONEY_RATES_DESC'); ?></td>
                </tr>
                <tr>
                    <td><a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_phmoney&view=splits'); ?>"><?php echo Text::_('COM_PHMONEY_TRANSACTIONS'); ?></a></td>
                    <td><?php echo Text::_('COM_PHMONEY_TRANSACTIONS_DESC'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <h2 class="text text-info text-center"><i class="fa fa-money" style="color: #5cb85c;"></i> <?php echo Text::_('COM_PHMONEY'); ?></h2>
        <small class="text text-muted hidden"><?php echo $this->manifest->get('creationDate'); ?></small>
        <small class="text text-muted"><?php echo 'v' . $this->manifest->get('version'); ?></small>
        <div class="text text-muted text-center hidden"><?php echo $this->manifest->get('authorUrl'); ?></div>
        <div class="text text-muted text-center hidden"><?php echo $this->manifest->get('copyright'); ?></div>
    </div>
</div>
<div class="clearfix"> </div>