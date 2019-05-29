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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');

HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

$app = JFactory::getApplication();
$input = $app->input;
$item = $this->item;
$fieldSets = $this->form->getFieldsets();
?>

<form action="<?php echo JRoute::_('index.php?option=com_phmoney&view=price&layout=edit&id=' . $item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">

    <div class="card">

        <div class="card-body">
            <?php echo $this->buttonbar; ?>
        </div>

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

        <div class="card-body">            
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
                        $html[] = '" id="nav-' . $fieldset_name . '" role="tabpanel" aria-labelledby="nav-' . $fieldset_name . '-tab">' . $this->form->renderFieldSet($fieldset_name) . '</div>';
                        echo implode('', $html);
                }
                ?>
            </div>
        </div>

    </div>

    <div id="hidden-input">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
<div class="clearfix"> </div>