<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$btnClass = $displayData['class'];

$btnAlt = Text::_($displayData['alt']);
if (!empty($btnAlt)) {
        $btnAlt .= '&nbsp;';
}
?>
<button
	type="button"
	class="btn <?php echo $btnClass; ?> dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
	aria-haspopup="true"
	aria-expanded="false"><?php echo $btnAlt;?></button>
<div class="dropdown-menu dropdown-menu-right">
