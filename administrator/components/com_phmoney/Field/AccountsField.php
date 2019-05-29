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
 * Description of AccountsField
 *
 */
class AccountsField  extends Field\ListField{
        
        /**
         * The form field type.
         *
         * @var    string
         */
        protected $type = 'Accounts';

        /**
         * Method to get the field options.
         *
         * @return array The field option objects.
         *
         * @throws \Exception
         *
         */
        public function getOptions() {
                
                $options = PhmoneyHelper::getAccounts();
                
                // Pad the option text with spaces using depth level as a multiplier.
                foreach ($options as $key => $value) {
                        // Translate ROOT
                        if ($this->element['parent'] == true) {
                                if ($options[$key]->level == 0) {
                                        $options[$key]->text = Text::_('JGLOBAL_ROOT_PARENT');
                                }
                        }

                        if ($options[$key]->published == 1) {
                                $options[$key]->text = str_repeat('- ', !$options[$key]->level ? 0 : $options[$key]->level - 1) . $options[$key]->text;
                        } else {
                                $options[$key]->text = str_repeat('- ', !$options[$key]->level ? 0 : $options[$key]->level - 1) . '[' . $options[$key]->text . ']';
                        }
                }

                if (($this->element['parent'] == true ) && (isset($row) && !isset($options[0])) && isset($this->element['show_root'])) {
                        if ($row->parent_id == '1') {
                                $parent = new \stdClass;
                                $parent->text = Text::_('JGLOBAL_ROOT_PARENT');
                                array_unshift($options, $parent);
                        }

                        array_unshift($options, \JHtml::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
                }

                // Merge any additional options in the XML definition.
                if (isset($this->element)) {
                        $options = array_merge(parent::getOptions(), $options);
                }
                return $options;
        }
        
}
