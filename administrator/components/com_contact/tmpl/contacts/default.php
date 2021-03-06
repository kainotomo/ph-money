<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$assoc     = Associations::isEnabled();

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_contact&task=contacts.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_contact'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) : ?>
            <div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
            </div>
		<?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="contactList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_CONTACT_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JFEATURED', 'a.featured', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:1%; min-width:85px" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTACT_FIELD_LINKED_USER_LABEL', 'ul.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
								</th>
								<?php if ($assoc) : ?>
									<th scope="col" style="width:10%">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTACT_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<?php if (Multilanguage::isEnabled()) : ?>
									<th scope="col" style="width:10%" class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:5%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php
						$n = count($this->items);
						foreach ($this->items as $i => $item) :
							$canCreate  = $user->authorise('core.create',     'com_contact.category.' . $item->catid);
							$canEdit    = $user->authorise('core.edit',       'com_contact.category.' . $item->catid);
							$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canEditOwn = $user->authorise('core.edit.own',   'com_contact.category.' . $item->catid) && $item->created_by == $userId;
							$canChange  = $user->authorise('core.edit.state', 'com_contact.category.' . $item->catid) && $canCheckin;

							$item->cat_link = Route::_('index.php?option=com_categories&extension=com_contact&task=edit&type=other&id=' . $item->catid);
							?>
							<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->catid; ?>">
								<td class="order text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass; ?>">
										<span class="icon-menu" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5"
											value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center">
									<div class="btn-group">
									        <?php echo HTMLHelper::_('contactadministrator.featured', $item->featured, $i, $canChange); ?>
								   	</div>
								</td>	
								<td class="text-center">
									<div class="btn-group">
										<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'contacts.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									</div>	
								</td>
								
								<th scope="row" class="has-context">
									<div>
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'contacts.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit || $canEditOwn) : ?>
											<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pen-square mr-2" aria-hidden="true"></span>'; ?>
											<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_contact&task=contact.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->name)); ?>">
												<?php echo $editIcon; ?><?php echo $this->escape($item->name); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->name); ?>
										<?php endif; ?>
										<span class="small">
											<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
										</span>
										<div class="small">
											<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
										</div>
									</div>
								</th>
								<td class="small">
									<?php if (!empty($item->linked_user)) : ?>
										<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . $item->user_id); ?>"><?php echo $item->linked_user; ?></a>
										<div class="small"><?php echo $item->email; ?></div>
									<?php endif; ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo $item->access_level; ?>
								</td>
								<?php if ($assoc) : ?>
								<td class="d-none d-md-table-cell">
									<?php if ($item->association) : ?>
										<?php echo HTMLHelper::_('contactadministrator.association', $item->id); ?>
									<?php endif; ?>
								</td>
								<?php endif; ?>
								<?php if (Multilanguage::isEnabled()) : ?>
									<td class="small d-none d-md-table-cell">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>
								<td class="d-none d-md-table-cell">
									<?php echo $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_contact')
						&& $user->authorise('core.edit', 'com_contact')
						&& $user->authorise('core.edit.state', 'com_contact')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title'  => Text::_('COM_CONTACT_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
