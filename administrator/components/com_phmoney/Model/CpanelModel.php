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

namespace Joomla\Component\Phmoney\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Description of CpanelModel
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class CpanelModel extends BaseDatabaseModel {

        /**
         * Get the component's manifest
         * 
         * @return string The PH Money component manifest
         */
        public function getManifest() {
                
                $component = ComponentHelper::getComponent('com_phmoney');
                $extension = new \Joomla\CMS\Table\Extension($this->getDbo());
                $extension->load($component->id);
                $manifest = new \Joomla\Registry\Registry($extension->manifest_cache);

                return $manifest;
        }

}
