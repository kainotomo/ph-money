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

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Form\Field;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;

/**
 * Description of AccountEditField
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class AccountEditField extends Field\ListField {

        /**
         * To allow creation of new categories.
         *
         * @var    integer
         */
        protected $allowAdd;

        /**
         * A flexible category list that respects access controls
         *
         * @var    string
         */
        public $type = 'AccountEdit';

        /**
         * Method to attach a JForm object to the field.
         *
         * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
         * @param   mixed              $value    The form field value to validate.
         * @param   string             $group    The field name group control value. This acts as an array container for the field.
         *                                       For example if the field has name="foo" and the group value is set to "bar" then the
         *                                       full field name would end up being "bar[foo]".
         *
         * @return  boolean  True on success.
         *
         * @see     FormField::setup()
         */
        public function setup(\SimpleXMLElement $element, $value, $group = null) {
                $return = parent::setup($element, $value, $group);

                if ($return) {
                        $this->allowAdd = $this->element['allowAdd'] ?? '';
                }

                return $return;
        }

        /**
         * Method to get certain otherwise inaccessible properties from the form field object.
         *
         * @param   string  $name  The property name for which to get the value.
         *
         * @return  mixed  The property value or null.
         *
         */
        public function __get($name) {
                switch ($name) {
                        case 'allowAdd':
                                return $this->$name;
                }

                return parent::__get($name);
        }

        /**
         * Method to set certain otherwise inaccessible properties of the form field object.
         *
         * @param   string  $name   The property name for which to set the value.
         * @param   mixed   $value  The value of the property.
         *
         * @return  void
         *
         */
        public function __set($name, $value) {
                $value = (string) $value;

                switch ($name) {
                        case 'allowAdd':
                                $value = (string) $value;
                                $this->$name = ($value === 'true' || $value === $name || $value === '1');
                                break;
                        default:
                                parent::__set($name, $value);
                }
        }

        /**
         * Method to get a list of categories that respects access controls and can be used for
         * either category assignment or parent category assignment in edit screens.
         * Use the parent element to indicate that the field will be used for assigning parent categories.
         *
         * @return  array  The field option objects.
         *
         */
        protected function getOptions() {
                $options = array();
                $published = $this->element['published'] ?: array(0, 1);
                $name = (string) $this->element['name'];

                // Let's get the id for the current item, either category or content item.
                $jinput = Factory::getApplication()->input;

                // Load the category options for a given extension.
                // For categories the old category is the category id or 0 for new category.
                if ($this->element['parent']) {
                        $oldCat = $jinput->get('id', 0);
                        $oldParent = $this->form->getValue($name, 0);
                } else {
                        // For items the old category is the category they are in when opened or 0 if new.
                        $oldCat = $this->form->getValue($name, 0);
                }

                $view = $jinput->get('view', 'transaction');
                if ($view == 'transaction') {
                        $view = 'split';
                }
                $portfolio_id = Factory::getApplication()->getUserStateFromRequest('com_phmoney.' . $view . 's.filter.portfolio', 'filter_portfolio');
                if (is_null($portfolio_id)) {
                        $portfolio_id = PhmoneyHelper::getDefaultPortfolio();
                }

                // Account for case that a submitted form has a multi-value category id field (e.g. a filtering form), just use the first category
                $oldCat = is_array($oldCat) ? (int) reset($oldCat) : (int) $oldCat;

                $db = Factory::getDbo();
                $user = Factory::getUser();

                $query = $db->getQuery(true)
                        ->select('a.id AS value, a.title AS text, a.level, a.published, a.lft')
                        ->from('#__phmoney_accounts AS a');

                //join over currency
                $query->select(
                                'cur.name as currency_name'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS cur ON cur.id = a.currency_id');
                
                //join over account type
                $query->select(
                                'typ.name as account_type_name'
                        )
                        ->join('LEFT', '#__phmoney_account_types AS typ ON typ.id = a.account_type_id');

                // Filter by the portfolio
                $query->where('(a.portfolio_id = ' . $db->quote($portfolio_id) . ')');

                // Filter on the published state
                if (is_numeric($published)) {
                        $query->where('a.published = ' . (int) $published);
                } elseif (is_array($published)) {
                        $query->where('a.published IN (' . implode(',', ArrayHelper::toInteger($published)) . ')');
                }

                $query->order('a.lft ASC');

                // Get the options.
                $db->setQuery($query);

                try {
                        $options = $db->loadObjectList();
                } catch (\RuntimeException $e) {
                        Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }
                
                // Pad the option text with spaces using depth level as a multiplier.
                for ($i = 0, $n = count($options); $i < $n; $i++) {
                        // Translate ROOT
                        if ($this->element['parent'] == true) {
                                if ($options[$i]->level == 0) {
                                        $options[$i]->text = Text::_('JGLOBAL_ROOT_PARENT');
                                }
                        }

                        if ($options[$i]->published == 1) {
                                $options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . $options[$i]->text . ' ~ ' . $options[$i]->currency_name . ' ~ ' . Text::_($options[$i]->account_type_name);
                        } else {
                                $options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . '[' . $options[$i]->text . ' ~ ' . $options[$i]->currency_name . ' ~ ' . Text::_($options[$i]->account_type_name) . ']';
                        }
                }

                // For new items we want a list of categories you are allowed to create in.
                if ($oldCat != 0) {
                        /*
                         * If you are only allowed to edit in this category but not edit.state, you should not get any
                         * option to change the category parent for a category or the category for a content item,
                         * but you should be able to save in that category.
                         */
                        foreach ($options as $i => $option) {

                                if ($option->value == $this->form->getValue('id', 0)) {
                                        unset($options[$i]);
                                        continue;
                                }
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
                return array_merge(parent::getOptions(), $options);
        }

}
