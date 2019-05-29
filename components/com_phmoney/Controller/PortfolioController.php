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

namespace Joomla\Component\Phmoney\Site\Controller;

use Joomla\Component\Phmoney\Administrator\Controller\PortfolioController as PhmoneyAdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Factory;

/**
 * Inherits administrator controller
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class PortfolioController extends PhmoneyAdminController{
        
        public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null, FormFactoryInterface $formFactory = null) {
                parent::__construct($config, $factory, $app, $input, $formFactory);
                Factory::getLanguage()->load('com_phmoney', JPATH_ADMINISTRATOR);
                Factory::getLanguage()->load('', JPATH_ADMINISTRATOR);
        }
}
