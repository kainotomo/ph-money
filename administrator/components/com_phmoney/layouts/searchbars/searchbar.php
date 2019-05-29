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
?>
<p>
    <a class="btn btn-secondary dropdown-toggle" data-toggle="collapse" href="#phFilter" role="button" aria-expanded="false" aria-controls="collapseExample">
        <span class="fa-fw fa fa-filter"></span>
    </a>
</p>
<div class="collapse" id="phFilter">
    <div class="card">
        <div class="card-header">
            <?php echo Text::_('COM_PHMONEY_FILTER'); ?>
        </div>
        <div class="card-body">
            <?php if ($filters) : ?>
                    <div class="navbar-nav mr-auto">
                        <?php foreach ($filters as $fieldName => $field) : ?>
                                <?php if ($fieldName !== 'filter_search') : ?>                                    
                                        <div class="js-stools-field-filter">
                                            <?php echo $field->input; ?>
                                        </div>
                                <?php endif; ?>
                                <?php if ($fieldName === 'filter_tag') : ?>
                                        <br/>
                                <?php endif; ?>
                                <?php if ($fieldName === 'filter_end_date') : ?>
                                        <button type="submit" class="btn btn-outline-primary my-2 my-sm-0 hasTooltip" 
                                                onclick="this.form.submit();"
                                                title="<?php echo HTMLHelper::_('tooltipText', 'JSUBMIT'); ?>"  
                                                aria-label="<?php echo Text::_('JSUBMIT'); ?>">
                                                    <?php echo Text::_('JSUBMIT'); ?>
                                        </button>
                                        <br/>
                                <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
            <?php endif; ?>            
        </div>
        <div class="card-divider"></div>
        <div class="card-body">
            <?php if ($list) : ?>
                    <div class="navbar-nav mr-auto">
                        <?php foreach ($list as $fieldName => $field) : ?>                                
                                <?php if ($fieldName !== 'filter_search') : ?>                                    
                                        <div class="js-stools-field-filter">
                                            <?php echo $field->input; ?>
                                        </div>
                                <?php endif; ?>                                
                        <?php endforeach; ?>
                    </div>
            <?php endif; ?>            
        </div>
        <div class="card-divider"></div>
        <div class="card-body">
            <?php if (!is_null($filters['filter_search']->input)) : ?>
                    <div class="form-inline my-2 my-lg-0">            
                        <div class="input-group">                    
                            <?php echo $filters['filter_search']->input; ?>
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-outline-success my-2 my-sm-0 hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"  aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                                    <span class="fa fa-search" aria-hidden="true"></span>
                                </button>
                                <button type="submit" class="btn btn-outline-secondary my-2 my-sm-0 hasTooltip js-stools-btn-clear" 
                                        title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>"
                                        onclick="document.getElementById('filter_search').value = '';">
                                            <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                                </button>
                            </span>
                        </div>
                    </div>		
            <?php endif; ?>          
        </div>
    </div>
</div>

