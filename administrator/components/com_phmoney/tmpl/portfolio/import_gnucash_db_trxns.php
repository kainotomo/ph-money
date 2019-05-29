<?php
/*
 * Copyright (C) 2019 KAINOTOMO PH LTD <info@kainotomo.com>
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
?>

<div class="card">
    <div class="card-body">
        <p class="card-text" id="message"><?php echo Text::_('COM_PHMONEY_WAIT_MESSAGE'); ?></p>
        <p class="card-text"><progress id="progress-bar" value="0" max="0" ></progress> <span id="offset">0</span> %</p>
        <p class="card-text" id="status"></p>
        <button onclick="window.history.back();" id="button-back" class="btn btn-primary" style="display: none"><?php echo Text::_('JTOOLBAR_CLOSE'); ?></button>
    </div>
</div>
<br/>