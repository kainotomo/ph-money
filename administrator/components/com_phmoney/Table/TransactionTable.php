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

namespace Joomla\Component\Phmoney\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

/**
 * Implement Transaction Table
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class TransactionTable extends Table {

        /**
         * Constructor
         *
         * @param   \JDatabaseDriver  $db  Database connector object
         *
         */
        public function __construct(\JDatabaseDriver $db) {
                $this->typeAlias = 'com_phmoney.transaction';

                parent::__construct('#__phmoney_transactions', 'id', $db);
                
                $this->setColumnAlias('published', 'state');
        }

        /**
         * Method to store a row in the database from the Table instance properties.
         *
         * If a primary key value is set the row with that primary key value will be updated with the instance property values.
         * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
         *
         * @param   boolean  $updateNulls  True to update fields even if they are null.
         *
         * @return  boolean  True on success.
         *
         */
        public function store($updateNulls = false) {
                $date = Factory::getDate();

                $this->modified_date = $date->toSql();

                if (!$this->id) {
                        // New article. An article created and created_by field can be set by the user,
                        // so we don't touch either of these if they are set.
                        if (empty($this->post_date)) {
                                $this->post_date = $date->toSql();
                        }
                }

                return parent::store($updateNulls);
        }

}
