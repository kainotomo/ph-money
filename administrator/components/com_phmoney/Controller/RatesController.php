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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

/**
 * Rates list controller class.
 *
 */
class RatesController extends AdminController {

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
        public function getModel($name = 'Rate', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
                return parent::getModel($name, $prefix, $config);
        }

        /**
         * Download currency exchange rates
         */
        public function download() {
                // Check for request forgeries
                Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

                $model = $this->getModel("Rates");
                try {
                        $model->download();
                } catch (\Exception $exc) {
                        $this->setMessage($exc->getMessage(), 'error');
                }

                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }

}
