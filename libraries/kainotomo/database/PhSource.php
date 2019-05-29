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

namespace Joomla\Library\Kainotomo;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * SPGeneral is a class that handle a source site.
 *
 * @package     spcyend.utilities.source
 * @subpackage  Database
 * @since       1.0.0
 */
class PhSource {

        /**
         * The source JDatabase object
         *
         * @var    JDatabase
         * @since  1.0.0
         */
        public $source_db;

        /**
         * The source path where Joomla is installed
         *
         * @var    string
         * @since  1.0.0
         */
        public $source_path;

        /**
         * Constructor.
         *
         * @param   string  $component  String indicating the current component
         *
         * @since   1.0.0
         *
         */
        public function __construct($component_name = null) {
                JFactory::getLanguage()->load('lib_spcyend', JPATH_SITE); //Load library language

                if (is_null($component_name)) {
                        $component_name = JFactory::getApplication()->input->getCmd('option');
                }

                $params = JComponentHelper::getParams($component_name);
                $option = array(); //prevent problems 
                $option['driver'] = $params->get("driver", 'mysqli');            // Local Database driver name     
                $option['host'] = $params->get("host", 'localhost');    // Database host name
                $option['user'] = $params->get("source_user_name", '');       // User for database authentication
                $option['password'] = $params->get("source_password", '');   // Password for database authentication
                $option['database'] = $params->get("source_database_name", '');      // Database name
                $option['prefix'] = $this->modPrefix($params->get("source_db_prefix", ''));             // Database prefix (may be empty)

                $this->source_db = JDatabase::getInstance($option);
                $this->source_path = $params->get("source_path", '');      // source directory path
        }

        /**
         * Set DBO.
         *
         * @option   array  $option  Array with database credentials
         *
         * @since   2.0.2
         *
         */
        public function set($option) {
                JFactory::getLanguage()->load('lib_spcyend', JPATH_SITE); //Load library language

                $option['prefix'] = $this->modPrefix($option['prefix']);             // Database prefix (may be empty)

                $this->source_db = JDatabase::getInstance($option);
        }

        /**
         * Refresh and get again the connection
         *
         * @param   string  $component  String indicating the current component
         *
         * @return  JDatabase  The JDatabase object
         * 
         * @since   1.0.0    
         */
        public function getDbo() {
                if (is_null($component_name)) {
                        $component_name = JFactory::getApplication()->input->getCmd('option');
                }

                $params = JComponentHelper::getParams($component_name);
                $option = array(); //prevent problems 
                $option['driver'] = $params->get("driver", 'mysqli');            // Local Database driver name     
                $option['host'] = $params->get("host", 'localhost');    // Database host name
                $option['user'] = $params->get("source_user_name", '');       // User for database authentication
                $option['password'] = $params->get("source_password", '');   // Password for database authentication
                $option['database'] = $params->get("source_database_name", '');      // Database name
                $option['prefix'] = $this->modPrefix($params->get("source_db_prefix", ''));             // Database prefix (may be empty)

                $this->source_db = JDatabase::getInstance($option);
                $this->source_path = $params->get("source_path", '');      // source directory path

                return $this->source_db;
        }

        /**
         * Put underscore in the prfix if not present
         *
         * @param   string  $prefix  The prefix name
         *
         * @return  string  The prefix name
         *
         * @since   1.0.0
         */
        public static function modPrefix($prefix) {
                if (!strpos($prefix, '_')) {
                        $prefix = $prefix . '_';
                }
                return $prefix;
        }

        /**
         * Test if the connection is live
         *
         * @return  boolean True in success, or false in failure
         *
         * @since   1.0.0
         */
        public function testConnection() {
                //Check connection        
                $query = "SELECT id from #__users";
                $this->source_db->setQuery($query);
                return CYENDFactory::execute($this->source_db);
        }

        /**
         * Test if the path is accessible
         *
         * @return  boolean True in success, or false in failure
         *
         * @since   1.0.0
         */
        public function testPathConnection() {
                $source_path = $this->source_path;
                if (empty($source_path)) {
                        return false;
                }
                return JFile::exists($source_path . '/index.php');
        }

}
