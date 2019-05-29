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

namespace Joomla\Component\Phmoney\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * Prices list controller class.
 *
 */
class PricesController extends AdminController {

        /**
         * Proxy for getModel.
         *
         * @param   string  $name    The model name. Optional.
         * @param   string  $prefix  The class prefix. Optional.
         * @param   array   $config  The array of possible config values. Optional.
         *
         * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
         *
         */
        public function getModel($name = 'Price', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
                return parent::getModel($name, $prefix, $config);
        }

        /**
         * Retrieve prices for active portfolio
         */
        public function retrieve() {

                $this->setMessage(Text::_('COM_PHMONEY_RETRIEVED'));
                
                $model = $this->getModel('Prices');
                try {
                        $model->retrieve();
                } catch (\Exception $exc) {
                        $this->setMessage($exc->getMessage(), 'error');
                } 
                
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

}
