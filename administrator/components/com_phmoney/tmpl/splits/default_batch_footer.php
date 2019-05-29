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

JFactory::getDocument()->addScriptDeclaration("
	jQuery('#exampleModal').on('hide.bs.modal', function (e) {
		document.getElementById('batch-category-id').value = '';
		document.getElementById('batch-access').value = '';
		document.getElementById('batch-language-id').value = '';
		document.getElementById('batch-user-id').value = '';
		document.getElementById('batch-tag-id').value = '';
	});
");

?>
<a class="btn btn-secondary" type="button" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<?php echo $this->batch_submit; ?>
