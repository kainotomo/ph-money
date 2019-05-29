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

namespace Joomla\Component\Phmoney\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

/**
 * Description of PortfoliosField
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class PortfoliosField  extends Field\ListField{
        
        /**
         * The form field type.
         *
         * @var    string
         */
        protected $type = 'Portfolios';

        public function __construct($form = null) {
                parent::__construct($form);
                $this->default = (string) PhmoneyHelper::getDefaultPortfolio();
        }
        
        public function setup(\SimpleXMLElement $element, $value, $group = null) {
                $element['default'] = PhmoneyHelper::getDefaultPortfolio(); 
                return parent::setup($element, $value, $group);
        }
        
        /**
         * Method to get the field options.
         *
         * @return array The field option objects.
         *
         * @throws \Exception
         *
         */
        public function getOptions() {
                // Merge any additional options in the XML definition.
                $options = array_merge(parent::getOptions(), PhmoneyHelper::getPortfolios());
                return $options;
        }
        
}
