<?php
/*
 * Copyright (C) 2017 KAINOTOMO PH LTD <info@kainotomo.com>
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

use Joomla\CMS\Language\Text;

$fieldSets = $this->batchForm->getFieldsets();
?>

<div class="container">
	<div class="nav nav-tabs card-header" id="nav-tab" role="tablist">
        <?php
        $first = true;
        foreach ($fieldSets as $fieldset_name => $fieldSet) {
            $html = array();
            $html[] = '<a class="nav-item nav-link';
            if ($first) {
                $html[] = ' active';
                $first = false;
            }
        $html[] = '" id="nav-' . $fieldset_name . '-tab" data-toggle="tab" href="#nav-' . $fieldset_name . '" role="tab" aria-controls="nav-' . $fieldset_name . '" aria-selected="true">' . Text::_($fieldSet->label) . '</a>';
        echo implode('', $html);
        }
        ?>
    </div>
    <div class="tab-content" id="nav-tabContent">
        <?php
        $first = true;
        foreach ($fieldSets as $fieldset_name => $fieldSet) {
            $html = array();
            $html[] = '<div class="tab-pane fade';
            if ($first) {
                $html[] = ' show active';
                $first = false;
            }
                        $html[] = '" id="nav-' . $fieldset_name . '" role="tabpanel" aria-labelledby="nav-' . $fieldset_name . '-tab">' . $this->batchForm->renderFieldSet($fieldset_name) . '</div>';
            echo implode('', $html);
        }
        ?>
    </div>
</div>
