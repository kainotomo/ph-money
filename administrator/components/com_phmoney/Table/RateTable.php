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

namespace Joomla\Component\Phmoney\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Contact Table class.
 *
 */
class RateTable extends Table {

        
        /**
         * Constructor
         *
         * @param   \JDatabaseDriver  $db  Database connector object
         *
         */
        public function __construct(\JDatabaseDriver $db) {
                $this->typeAlias = 'com_phmoney.rate';

                parent::__construct('#__phmoney_rates', 'id', $db);
        }

}
