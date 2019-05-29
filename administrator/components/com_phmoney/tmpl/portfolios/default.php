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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));

$app = Factory::getApplication();
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$trashed = $this->state->get('filter.published') == -2 ? true : false;

$print = false;
if (Factory::getApplication()->input->getString('print') == 'true' && Factory::getApplication()->input->getString('tmpl') == 'component') {
        $print = true;
}
?>


<div class="card">
    <?php if (!$print) : ?>
            <div class="card-header">
                <?php echo $this->navbar; ?>
            </div>
            <div class="card-header-pills">
                <nav class="navbar">
                    <div class="mr-auto">
                        <?php echo $this->buttonbar; ?>                       
                    </div>  
                    <div class=" my-2 my-lg-0">
                    </div>
                </nav>     
            </div>
    <?php endif; ?>


    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=portfolios'); ?>" method="post" name="adminForm" id="adminForm">

        <?php if (!$print) : ?>
                <div class="card-header-pills">
                    <nav class="navbar">  
                        <div class="mr-auto">
                        </div>  
                        <div class=" my-2 my-lg-0">
                            <?php echo $this->searchbar; ?>              
                        </div>
                    </nav>     
                </div>
        <?php endif; ?>

        <div class="card-body">
            <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
            <?php else : ?>
                    <table class="table" id="portfolioList">                        
                        <thead>
                            <tr>
                                <th style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>                                
                                <th style="width:1%" class="nowrap text-center">
                                    <?php echo Text::_('JSTATUS'); ?>
                                </th>
                                <th style="min-width:100px" class="nowrap">
                                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                                </th>
                                <th style="width:10%" class="nowrap hidden-sm-down text-center">
                                    <?php echo Text::_('COM_PHMONEY_CURRENCY'); ?>
                                </th>
                                <th style="width:1%" class="nowrap text-center">
                                    <?php echo Text::_('JTOOLBAR_DEFAULT'); ?>
                                </th>
                                <th style="width:3%" class="nowrap hidden-sm-down text-center">
                                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($this->items as $i => $item) :
                                    ?>
                                    <tr class="row<?php echo $i % 2; ?>">
                                        <td class="text-center">
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                        </td>                                        
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'portfolios.', true, 'cb', true, true); ?>
                                            </div>
                                        </td>
                                        <td class="has-context">
                                            <div class="break-word">
                                                <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_phmoney&task=portfolio.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
                                                    <span class="fa fa-pencil-square mr-2" aria-hidden="true"></span></a>
                                                <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_phmoney&task=accounts.setPortfolio&portfolio_id=' . $item->id); ?>" title="<?php echo JText::_('COM_PHMONEY_TRANSACTIONS'); ?>">
                                                    <?php echo $this->escape($item->title); ?></a>
                                            </div>
                                        </td>
                                        <td class="hidden-sm-down text-center">
                                            <?php echo $item->currency_name; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($item->user_default) : ?>
                                                    <span class="fa fa-check-circle" aria-hidden="true"></span>                                            
                                            <?php endif; ?>
                                        </td>
                                        <td class="hidden-sm-down text-center">
                                            <?php echo (int) $item->id; ?>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>        
                    </table>
            <?php endif; ?>
            <?php if (!$print): ?>
                    <div class="w-100">
                        <p class="counter float-right pt-3 pr-2">
                            <?php echo $this->pagination->getPagesCounter(); ?>
                        </p>
                        <?php echo $this->pagination->getListFooter(); ?>
                    </div>
            <?php endif; ?>

            <input type="hidden" name="task" value="">
            <input type="hidden" name="boxchecked" value="0">
            <?php echo HTMLHelper::_('form.token'); ?>         
        </div>
    </form>            
</div>

<div class="clearfix"> </div>