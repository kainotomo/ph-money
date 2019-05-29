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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;

/**
 * Description of PortfolioModel
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class PortfolioTable extends Table{
        
        /**
         * Constructor
         *
         * @param   \JDatabaseDriver  $db  Database connector object
         *
         */
        public function __construct(\JDatabaseDriver $db) {
                $this->typeAlias = 'com_phmoney.portfolio';

                parent::__construct('#__phmoney_portfolios', 'id', $db);
                
        }
        
        public function check() {
                
                // Generate a valid alias
		$this->generateAlias();
                
                return parent::check();
        }
        
        /**
	 * Generate a valid alias from title / date.
	 * Remains public to be able to check for duplicated alias before saving
	 *
	 * @return  string
	 */
	public function generateAlias()
	{
		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = \JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		return $this->alias;
	}
}
