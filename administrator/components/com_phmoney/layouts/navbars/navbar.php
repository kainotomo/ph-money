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

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filter\OutputFilter;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

$params = \Joomla\CMS\Component\ComponentHelper::getParams('com_phmoney');
$under_iframe = $params->get('under_iframe');
$isSite = JFactory::getApplication()->isClient('site');

if (isset($displayData['navbar'])) {
        $navbar = $displayData['navbar'];
} else {
        $navbar = $displayData;
}

if (isset($displayData['account_name'])) {
        $account_name = $displayData['account_name'];
} else {
        $account_name = null;
}
?>

<nav class="navbar navbar-expand-sm navbar-light bg-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerNavbar" aria-controls="navbarTogglerNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerNavbar">
        <ul class="nav nav-pills mr-auto mt-2 mt-lg-0">
            <?php
            foreach ($navbar as $item) :

                    if ($isSite) { //hide selected views
                            if (($item[0] == "Emails") && (!$params->get('show_emails'))) {
                                    continue;
                            }
                            if (($item[0] == "Contacts") && (!$params->get('show_contacts'))) {
                                    continue;
                            }
                            if (($item[0] == "Events") && (!$params->get('show_events'))) {
                                    continue;
                            }
                            if (($item[0] == "Files") && (!$params->get('show_files'))) {
                                    continue;
                            }
                    }
                    ?>

                    <li class="nav-item">

                        <?php
                        if (strlen($item[1])) :
                                if (isset($item[2]) && $item[2] == 1) :
                                        ?>                            
                                        <a class="active nav-link" href="<?php echo OutputFilter::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a>
                                <?php else : ?>
                                        <a class="nav-link" href="<?php echo OutputFilter::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a>
                                <?php endif; ?>
                        <?php else : ?>
                                <?php echo $item[0]; ?>
                        <?php
                        endif;
                        ?>
                    </li>
            <?php endforeach; ?>
        </ul>        
    </div>
    <?php if (!is_null($account_name)) : ?>
            <div class="form-inline my-2 my-lg-0 text-secondary">
                <?php echo $account_name; ?>
            </div>
    <?php endif; ?>
</nav>


