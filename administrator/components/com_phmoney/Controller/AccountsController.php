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

namespace Joomla\Component\Phmoney\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

/**
 * Description of AccountsController
 *
 */
class AccountsController extends AdminController {

        public function getModel($name = 'Account', $prefix = '', $config = array()) {
                return parent::getModel($name, $prefix, $config);
        }

        public function print_view() {
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&tmpl=component', false));
        }
        
        public function setPortfolio() {
                $portfolio_id = $this->input->getInt("portfolio_id", 0);
                $model = $this->getModel('Accounts');
                $model->setPortfolio($portfolio_id);
                $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }
        
        /**
         * Save accounts list view as json report
         */
        public function save_report() {
                $model = $this->getModel('Accounts');
                $model->save_report();
        }
        
        /**
         * Open accounts list report
         */
        public function open_report() {
                $files = $this->input->files->get('filter', '', 'array');
                $file = $files['report_file'];
                $model = $this->getModel('Accounts');
                $model->open_report($file);
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
        }
}
