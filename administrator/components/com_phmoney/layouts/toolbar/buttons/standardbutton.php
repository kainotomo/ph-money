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

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$task = $displayData['task'];
$class = $displayData['class'];
$alt = Text::_($displayData['alt']);
$icon = $displayData['icon'];
$list = $displayData['list'];
$group = $displayData['group'];
if (isset($displayData['confirm'])) {
        $confirm = $displayData['confirm'];
} else {
        $confirm = false;
}
if (isset($displayData['custom'])) {
        $custom = $displayData['custom'];
} else {
        $custom = false;
}

Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
Text::script('ERROR');

if ($custom) {
        $cmd = $task;
} else {
        $cmd = "Joomla.submitbutton('" . $task . "');";
}

if ($list) {
        $messages = "{'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
        $alert = "Joomla.renderMessages(" . $messages . ")";
        $cmd = "if (document.adminForm.boxchecked.value == 0) { " . $alert . " } else { " . $cmd . " }";
}

if ($confirm) {
        $cmd = "if (confirm('" . Text::_('COM_PHMONEY_ARE_YOU_SURE') . "')) { $cmd }";
}
?>

<?php if ($group) : ?>
        <button type="button" onclick="<?php echo $cmd; ?>" class="dropdown-item">
            <?php if (!empty($icon)) : ?>
                    <span class="fa <?php echo $icon; ?>" aria-hidden="true">&nbsp;</span>
            <?php endif; ?>
            <?php echo $alt; ?>
        </button>
<?php else : ?>
        <button type="button" class="btn <?php echo $class; ?>" onclick="<?php echo $cmd; ?>">
            <?php if (!empty($icon)) : ?>
                    <span class="fa <?php echo $icon; ?>" aria-hidden="true">&nbsp;</span>
            <?php endif; ?>
            <?php echo $alt; ?>
        </button>
<?php endif; ?>
