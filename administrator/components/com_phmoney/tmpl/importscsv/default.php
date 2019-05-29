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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

$app = Factory::getApplication();
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 't.ordering';
$assoc = JLanguageAssociations::isEnabled();
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;

$accounts_field = new \Joomla\Component\Phmoney\Administrator\Field\AccountsField();
$accounts_options = $accounts_field->getOptions();

if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = 'index.php?option=com_phmoney&task=importscsv.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
        HTMLHelper::_('draggablelist.draggable');
}

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


    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=importscsv'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

        <?php if (!$print) : ?>
                <div class="card-header-pills">
                    <nav class="navbar">  
                        <div class="mr-auto">
                            <?php echo $this->searchbar; ?>
                        </div>  
                        <div class="my-2 my-lg-0">                                      
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
                    <table class="table table-hover" id="splitList">
                        <thead>
                            <tr>
                                <th style="width:1%" class="text-center">
                                    <?php echo Text::_('COM_PHMONEY_ROW'); ?>
                                </th>                                
                                <?php foreach ($this->items[0] as $key => $value) : ?>
                                        <?php if ($key > 0) : ?>
                                                <th style="min-width:170px">                                            
                                                    <select name="headers[]" class="custom-select" id="header<?php echo $key; ?>">
                                                        <option value="nun"><?php echo Text::_('COM_PHMONEY_SELECT_HEADER'); ?></option>
                                                        <option value="post_date"><?php echo Text::_('JDATE'); ?></option>
                                                        <option value="title"><?php echo Text::_('JGLOBAL_TITLE'); ?></option>
                                                        <option value="num"><?php echo Text::_('COM_PHMONEY_NUM'); ?></option>                                                        
                                                        <option value="description"><?php echo Text::_('JGLOBAL_DESCRIPTION'); ?></option>
                                                        <option value="credit"><?php echo Text::_('COM_PHMONEY_CREDIT'); ?></option>
                                                        <option value="debit"><?php echo Text::_('COM_PHMONEY_DEBIT'); ?></option>
                                                    </select>
                                                </th>
                                        <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>                                            
                        </thead>
                        <tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                            <?php foreach ($this->items as $i => $item) : ?>
                                    <tr class="row<?php echo $i % 2; ?>">   
                                        <td class="text-center">
                                            <?php echo $item[0]; ?>
                                        </td>                                   
                                        <?php foreach ($item as $key => $value) : ?>
                                                <?php if ($key > 0) : ?>
                                                        <td class="text-center">
                                                            <?php echo $value; ?>
                                                        </td>
                                                <?php endif; ?>
                                        <?php endforeach; ?>
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
