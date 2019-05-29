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
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\CMS\HTML\HTMLHelper;

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
$report_type = $this->escape($this->state->get('filter.report_type', 'default'));

$print = false;
if (Factory::getApplication()->input->getString('tmpl') == 'component') {
        $print = true;
}

$showWatermark = PhmoneyHelper::showWatermark();
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

    <form action="<?php echo Route::_('index.php?option=com_phmoney&view=accounts'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

        <?php // Load the batch processing form. ?>
        <?php
        if ($user->authorise('core.create', 'com_phmoney') && $user->authorise('core.edit', 'com_phmoney') && $user->authorise('core.edit.state', 'com_phmoney')) :
                ?>
                <?php
                echo HTMLHelper::_(
                        'bootstrap.renderModal', 'collapseModal', array(
                        'title' => JText::_(''),
                        'footer' => $this->loadTemplate('batch_footer'),
                        ), $this->loadTemplate('batch_body')
                );
                ?>
        <?php endif; ?>

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

        <?php if (empty($this->items)) : ?>

                <div class="alert alert-info" role="alert">
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
        <?php else : ?>
                <?php if ($print && $showWatermark) : ?>
                        <div style="background:white url(../../media/com_phmoney/images/watermark.png) repeat">
                    <?php endif; ?>
                    <?php echo $this->loadTemplate($report_type); ?>
            <?php endif; ?>
            <?php if ($print && $showWatermark) : ?>
                </div>
        <?php endif; ?>

        <?php if ($report_type == "balances" || $report_type == "shares_portfolio"): ?>
                <div class="card-footer">
                    <p class="counter float-right pt-3 pr-2">
                        <?php echo $this->pagination->getPagesCounter(); ?>
                    </p>
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>                 

    </form>            
</div>

<div class="clearfix"> </div>