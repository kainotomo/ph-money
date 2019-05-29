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

use Joomla\CMS\HTML\HTMLHelper;

$app = JFactory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_split/helpers/route.php');

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_split/admin-articles-modal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));
HTMLHelper::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_ACCESS')));
HTMLHelper::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')));

$function  = $app->input->getCmd('function', 'jSelectSplit');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	JFactory::getDocument()->addScriptOptions('xtd-articles', array('editor' => $editor));
	$onclick = "jSelectSplit";
}
?>
<div class="container-popup">

	<form action="<?php echo JRoute::_('index.php?option=com_split&view=articles&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-warning alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-sm">
				<thead>
					<tr>
						<th style="width:1%" class="text-center nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th style="width:10%" class="nowrap hidden-sm-down">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th style="width:15%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th style="width:5%" class="nowrap hidden-sm-down">
							<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap hidden-sm-down">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				$iconStates = array(
					-2 => 'icon-trash',
					0  => 'icon-unpublish',
					1  => 'icon-publish',
					2  => 'icon-archive',
				);
				?>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php if ($item->language && JLanguageMultilang::isEnabled())
					{
						$tag = strlen($item->language);
						if ($tag == 5)
						{
							$lang = substr($item->language, 0, 2);
						}
						elseif ($tag == 6)
						{
							$lang = substr($item->language, 0, 3);
						}
						else {
							$lang = '';
						}
					}
					elseif (!JLanguageMultilang::isEnabled())
					{
						$lang = '';
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
						</td>
						<td>
							<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
								. ' data-id="' . $item->id . '"'
								. ' data-title="' . $this->escape(addslashes($item->title)) . '"'
								. ' data-cat-id="' . $this->escape($item->catid) . '"'
								. ' data-uri="' . $this->escape(ContentHelperRoute::getSplitRoute($item->id, $item->catid, $item->language)) . '"'
								. ' data-language="' . $this->escape($lang) . '"';
							?>
							<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
								<?php echo $this->escape($item->title); ?>
							</a>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
							</div>
						</td>
						<td class="small hidden-sm-down">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="nowrap small hidden-sm-down">
							<?php echo HTMLHelper::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="nowrap small hidden-sm-down">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
