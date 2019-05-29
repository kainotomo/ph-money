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
 * Method accessing table imports
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class ImportTable extends Table
{
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param \JDatabaseDriver $db JDatabaseDriver object.
	 */
	public function __construct(\JDatabaseDriver $db){
		$this->typeAlias = 'com_phmoney.import';

                parent::__construct('#__phmoney_imports', 'id', $db);
                
                $this->setColumnAlias('published', 'state');
	}
        
        /**
	 * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 */
        public function check() {
                $post_date = Factory::getDate($this->post_date);
                $this->post_date = $post_date->format('Y-m-d');
                return parent::check();
        }
}
