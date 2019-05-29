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

defined('JPATH_BASE') or die;

$params = \Joomla\CMS\Component\ComponentHelper::getParams('com_phmoney');
$under_iframe = $params->get('under_iframe');
$isSite = JFactory::getApplication()->isClient('site');


// Load the form filters
$filters = $displayData->getGroup('filter');
$list = $displayData->getGroup('list');
$fieldSets = $displayData->getFieldsets();
?>
<p>
    <a class="btn btn-secondary dropdown-toggle" data-toggle="collapse" href="#phFilter" role="button" aria-expanded="false" aria-controls="collapseExample">
        <span class="fa-fw fa fa-filter"></span>
    </a>
</p>
<div class="collapse" id="phFilter">
    <?php foreach ($fieldSets as $fieldset_name => $fieldSet) : ?>
            <div class="card">
                <div class="card-header">
                    <?php echo Text::_($fieldSet->label); ?>
                </div>
                <div class="card-body">
                    <?php echo $displayData->renderFieldSet($fieldset_name); ?>
                </div>
                
                <?php if ($fieldset_name === 'import') : ?>
                        <span class="input-group-append">
				<button type="submit" class="btn btn-outline-primary"
					onclick="if (confirm('<?php echo Text::_('COM_PHMONEY_ARE_YOU_SURE');?>')) { Joomla.submitbutton('imports.import_csv_file_splits'); }"
					aria-label="<?php echo Text::_('JSUBMIT'); ?>">
					<?php echo Text::_('JSUBMIT'); ?>
				</button> 
                        </span>
                <?php endif; ?>
                
            </div>
            <br/>
    <?php endforeach; ?>
</div>
