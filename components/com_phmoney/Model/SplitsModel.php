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

namespace Joomla\Component\Phmoney\Site\Model;

use Joomla\Component\Phmoney\Administrator\Model\SplitsModel as PhmoneyModelAdmin;
use Joomla\CMS\Form\FormHelper;

/**
 * Inherits administrator Model
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class SplitsModel extends PhmoneyModelAdmin{
        
        public function __construct($config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {
                parent::__construct($config, $factory);
                FormHelper::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        }   
}
