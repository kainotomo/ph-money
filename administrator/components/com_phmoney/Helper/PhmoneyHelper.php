<?php

/*
 * Copyright (C) 2017 KAINOTOMO PH LTD <info@kainotomo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Joomla\Component\Phmoney\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

/**
 * Phmoney component helper.
 *
 */
class PhmoneyHelper extends ContentHelper {

        /**
         * Configure the Linkbar.
         *
         * @param   string  $vName  The name of the active view.
         *
         * @return  void
         *
         */
        public static function addSubmenu($vName) {
                \JHtmlSidebar::addEntry(
                        Text::_('COM_PHMONEY_PORTFOLIOS'), 'index.php?option=com_phmoney&view=portfolios', $vName == 'portfolios'
                );
                \JHtmlSidebar::addEntry(
                        Text::_('COM_PHMONEY_ACCOUNTS'), 'index.php?option=com_phmoney&view=accounts', $vName == 'accounts'
                );
                if ($vName == 'splits') {
                        \JHtmlSidebar::addEntry(
                                Text::_('COM_PHMONEY_TRANSACTIONS'), 'index.php?option=com_phmoney&view=splits', $vName == 'splits'
                        );
                }
                if ($vName == 'imports') {
                        \JHtmlSidebar::addEntry(
                                Text::_('COM_PHMONEY_IMPORT'), 'index.php?option=com_phmoney&view=imports', $vName == 'imports'
                        );
                }
                \JHtmlSidebar::addEntry(
                        Text::_('COM_PHMONEY_EXTRAS'), 'index.php?option=com_phmoney&view=cpanel', $vName == 'cpanel'
                );

                /*
                  if (!Factory::getApplication()->isClient('site')) {
                  if (ComponentHelper::isEnabled('com_fields')) {
                  \JHtmlSidebar::addEntry(
                  Text::_('JGLOBAL_FIELDS'), 'index.php?option=com_fields&context=com_phmoney.categories', $vName == 'fields.fields'
                  );

                  \JHtmlSidebar::addEntry(
                  Text::_('JGLOBAL_FIELD_GROUPS'), 'index.php?option=com_fields&view=groups&context=com_phmoney.categories', $vName == 'fields.groups'
                  );
                  }
                  }
                 * 
                 */
                
                self::addPathway($vName);
        }
        

        /**
         * Add the breadcrumbs if on front end
         *
         * @param   string  $vName  The name of the active view.
         *
         */
        public static function addPathway($vName) {
                
                $app = Factory::getApplication();
                if ($app->isClient('site')) {
                        $pathway = $app->getPathway();
                        $pathway->addItem(Text::_('COM_PHMONEY_' . $vName), 'index.php?option=com_phmoney&view=' . $vName);
                }                
        }

        /**
         * Applies the phmoney tag filters to arbitrary text as per settings for current user group
         *
         * @param   text  $text  The string to filter
         *
         * @return  string  The filtered string
         *
         * @deprecated  4.0  Use ComponentHelper::filterText() instead.
         */
        public static function filterText($text) {
                try {
                        JLog::add(
                                sprintf('%s() is deprecated. Use ComponentHelper::filterText() instead', __METHOD__), JLog::WARNING, 'deprecated'
                        );
                } catch (RuntimeException $exception) {
                        // Informational log only
                }

                return ComponentHelper::filterText($text);
        }

        /**
         * Adds Count Items for Category Manager.
         *
         * @param   stdClass[]  &$items  The banner category objects
         *
         * @return  stdClass[]
         *
         */
        public static function countItems(&$items) {
                $db = \JFactory::getDbo();

                foreach ($items as $item) {
                        $item->count_trashed = 0;
                        $item->count_archived = 0;
                        $item->count_unpublished = 0;
                        $item->count_published = 0;
                        /*
                          $query = $db->getQuery(true);
                          $query->select('state, count(*) AS count')
                          ->from($db->qn('#__phmoney_splits'))
                          ->where('catid = ' . (int) $item->id)
                          ->group('state');
                          $db->setQuery($query);
                          $splits = $db->loadObjectList();

                          foreach ($splits as $split) {
                          if ($split->state == 1) {
                          $item->count_published = $split->count;
                          }

                          if ($split->state == 0) {
                          $item->count_unpublished = $split->count;
                          }

                          if ($split->state == 2) {
                          $item->count_archived = $split->count;
                          }

                          if ($split->state == -2) {
                          $item->count_trashed = $split->count;
                          }
                          }
                         * 
                         */
                }

                return $items;
        }

        /**
         * Adds Count Items for Tag Manager.
         *
         * @param   stdClass[]  &$items     The phmoney objects
         * @param   string      $extension  The name of the active view.
         *
         * @return  stdClass[]
         *
         */
        public static function countTagItems(&$items, $extension) {
                $db = \JFactory::getDbo();
                $parts = explode('.', $extension);
                $section = null;

                if (count($parts) > 1) {
                        $section = $parts[1];
                }

                $join = $db->qn('#__phmoney_splits') . ' AS c ON ct.content_item_id=c.id';
                $state = 'state';

                if ($section === 'category') {
                        $join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
                        $state = 'published as state';
                }

                foreach ($items as $item) {
                        $item->count_trashed = 0;
                        $item->count_archived = 0;
                        $item->count_unpublished = 0;
                        $item->count_published = 0;
                        $query = $db->getQuery(true);
                        $query->select($state . ', count(*) AS count')
                                ->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
                                ->where('ct.tag_id = ' . (int) $item->id)
                                ->where('ct.type_alias =' . $db->q($extension))
                                ->join('LEFT', $join)
                                ->group('state');
                        $db->setQuery($query);
                        $contents = $db->loadObjectList();

                        foreach ($contents as $content) {
                                if ($content->state == 1) {
                                        $item->count_published = $content->count;
                                }

                                if ($content->state == 0) {
                                        $item->count_unpublished = $content->count;
                                }

                                if ($content->state == 2) {
                                        $item->count_archived = $content->count;
                                }

                                if ($content->state == -2) {
                                        $item->count_trashed = $content->count;
                                }
                        }
                }

                return $items;
        }

        /**
         * Returns a valid section for splits. If it is not valid then null
         * is returned.
         *
         * @param   string  $section  The section to get the mapping for
         *
         * @return  string|null  The new section
         *
         */
        public static function validateSection($section) {
                if (Factory::getApplication()->isClient('site')) {
                        // On the front end we need to map some sections
                        switch ($section) {
                                // Editing an split
                                case 'form':

                                // Category list view
                                case 'featured':
                                case 'category':
                                        $section = 'split';
                        }
                }

                if ($section != 'split' && $section != 'categories') {
                        // We don't know other sections
                        return null;
                }

                return $section;
        }

        /**
         * Returns valid contexts
         *
         * @return  array
         *
         */
        public static function getContexts() {
                \JFactory::getLanguage()->load('com_phmoney', JPATH_ADMINISTRATOR);

                $contexts = array(
                        'com_phmoney.split' => Text::_('COM_PHMONEY'),
                        'com_phmoney.categories' => Text::_('JCATEGORY')
                );

                return $contexts;
        }

        /**
         * Writes a save button for a given option, with an additional dropdown
         *
         * @param   array   $buttons  An array of buttons
         * @param   string  $class    The button class
         *
         * @return  void
         *
         */
        public static function saveButtonGroup($buttons = array(), $class = 'btn-success') {
                // Options array for JLayout
                $options = array();
                $options['class'] = $buttons[0]['class'];
                $options['alt'] = '';

                $bar = Toolbar::getInstance('buttonbar');
                $html = array();
                $standarButtonLayout = new FileLayout('toolbar.buttons.standardbutton', null, Array('client' => 'admin'));

                $layout = new FileLayout('toolbar.group.groupopen', null, Array('client' => 'admin'));
                $bar->appendButton('Custom', $layout->render($options));
                $html[] = $layout->render($options);
                $firstItem = false;

                foreach ($buttons as $button) {

                        $options['group'] = true;
                        if (count($button) > 2) {
                                //$bar->appendButton('Standard', $button[1], $button[2], $button[0], $button[3], $firstItem);
                                $html[] = $standarButtonLayout->render($button);
                        } else {
                                $options['alt'] = $button['alt'];
                        }

                        if (!$firstItem) {
                                $layout = new FileLayout('toolbar.group.groupmid', null, Array('client' => 'admin'));
                                $bar->appendButton('Custom', $layout->render($options));
                                $html[] = $layout->render($options);
                                $firstItem = true;
                        }
                }

                $layout = new FileLayout('toolbar.group.groupclose', null, Array('client' => 'admin'));
                $bar->appendButton('Custom', $layout->render());
                $html[] = $layout->render($options);
                return implode('', $html);
        }

        /**
         *
         * @var array of stdclass with account types
         */
        public static $account_types;

        /**
         * Define account types
         * @return $account_types
         */
        public static function getAccountTypes() {

                if (!isset(PhmoneyHelper::$account_types)) {

                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('*')
                                ->from($db->quoteName('#__phmoney_account_types'))
                                ->order('id');
                        $db->setQuery($query);
                        try {
                                $rows = $db->loadObjectlist();
                        } catch (\RuntimeException $e) {
                                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        }

                        $account_types = array();
                        foreach ($rows as $row) {
                                $row->text = Text::_($row->name);
                                $row->value = $row->id;
                                $account_types[$row->id] = $row;
                        }

                        PhmoneyHelper::$account_types = $account_types;
                }

                return PhmoneyHelper::$account_types;
        }
        
        /**
         *
         * @var array of stdclass with split types
         */
        public static $split_types;

        /**
         * Define split types
         * @return $split_types
         */
        public static function getSplitTypes() {

                if (!isset(PhmoneyHelper::$split_types)) {

                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('*')
                                ->from($db->quoteName('#__phmoney_split_types'))
                                ->order('id');
                        $db->setQuery($query);
                        try {
                                $rows = $db->loadObjectlist();
                        } catch (\RuntimeException $e) {
                                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        }

                        $split_types = array();
                        foreach ($rows as $row) {
                                $row->text = Text::_($row->name);
                                $row->value = $row->id;
                                $split_types[$row->id] = $row;
                        }

                        PhmoneyHelper::$split_types = $split_types;
                }

                return PhmoneyHelper::$split_types;
        }

        /**
         *
         * @var array of currencys stdclass
         */
        public static $currencys;

        /**
         * Read from database currencys
         * @return $currencys
         */
        public static function getCurrencys() {

                if (!isset(PhmoneyHelper::$currencys)) {

                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('*')
                                ->from($db->quoteName('#__phmoney_currencys'))
                                ->order('name');
                        $db->setQuery($query);
                        try {
                                $rows = $db->loadObjectlist();
                        } catch (\RuntimeException $e) {
                                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        }

                        $currencys = array();
                        foreach ($rows as $row) {
                                $row->text = $row->name;
                                $row->value = $row->id;
                                $currencys[$row->id] = $row;
                        }

                        PhmoneyHelper::$currencys = $currencys;
                }

                return PhmoneyHelper::$currencys;
        }

        /**
         *
         * @var array of portfolios stdclass
         */
        public static $portfolios;

        /**
         * Read from database portfolios
         * @return $portfolios
         */
        public static function getPortfolios() {

                if (!isset(PhmoneyHelper::$portfolios)) {

                        $user = Factory::getUser();
                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('a.id, a.title, a.params')
                                ->from($db->quoteName('#__phmoney_portfolios') . ' as a')
                                ->where('published = 1')
                                ->where('user_id = ' . $user->id);
                        // Join over the currencys
                        $query->select('c.name AS currency_name')
                                ->join('LEFT', $db->quoteName('#__phmoney_currencys') . ' AS c ON c.id = a.currency_id');
                        $db->setQuery($query);
                        try {
                                $rows = $db->loadObjectlist();
                        } catch (\RuntimeException $e) {
                                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        }

                        $portfolios = array();
                        foreach ($rows as $row) {
                                $row->text = $row->title . ' ~ ' . $row->currency_name;
                                $row->value = $row->id;
                                $row->params = json_decode($row->params);
                                $portfolios[$row->id] = $row;
                        }

                        PhmoneyHelper::$portfolios = $portfolios;
                }

                return PhmoneyHelper::$portfolios;
        }

        /**
         * 
         * @return StdClass portfolio
         */
        public static function getDefaultPortfolio() {

                $user = Factory::getUser();
                $db = Factory::getDbo();
                $query = $db->getQuery(true);
                $query->select('id')
                        ->from($db->quoteName('#__phmoney_portfolios'))
                        ->where('user_default = 1')
                        ->where('user_id = ' . $user->id);
                $db->setQuery($query);
                try {
                        $portfolio = $db->loadResult();
                } catch (\RuntimeException $e) {
                        Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        return false;
                }
                return $portfolio;
        }

        /**
         *
         * @var array of accounts stdclass
         */
        public static $accounts;

        /**
         * Read from database portfolios
         * @return $portfolios
         */
        public static function getAccounts() {

                if (!isset(PhmoneyHelper::$accounts)) {

                        $filters = Factory::getApplication()->getUserStateFromRequest('com_phmoney.splits.filter', 'filter', array(), 'array');
                        if (!isset($filters['portfolio'])) {
                                $portfolio_id = PhmoneyHelper::getDefaultPortfolio();
                        } else {
                                $portfolio_id = (int) $filters['portfolio'];
                        }

                        $db = Factory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('*')
                                ->from($db->quoteName('#__phmoney_accounts'))
                                ->where('published = 1')
                                ->where('portfolio_id = ' . $portfolio_id)
                                ->order('lft ASC');
                        $db->setQuery($query);
                        try {
                                $rows = $db->loadObjectlist();
                        } catch (\RuntimeException $e) {
                                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                        }

                        $accounts = array();
                        foreach ($rows as $row) {
                                $row->text = $row->title;
                                $row->value = $row->id;
                                $accounts[$row->id] = $row;
                        }

                        PhmoneyHelper::$accounts = $accounts;
                }

                return PhmoneyHelper::$accounts;
        }

        /**
         * Round a value based on currency denom
         * 
         * @param type $value
         * @param type $denom
         * @return type
         */
        public static function roundMoney($value, $denom) {
                $precision = strlen($denom) - 1;
                return round($value, $precision);
        }

        /**
         * Presents a money value based on currency and denom
         * 
         * @param int $value
         * @param string $currency_symbol
         * @param int $denom
         * @return string The money 
         */
        public static function showMoney($value, $currency_symbol, $denom, $type = 'asset') {

                $money = $value / $denom;

                if ($type == 'income' || $type == 'liability' || $type == 'equity') {
                        $money *= -1;
                }

                if ($money < 0) {
                        $money *= -1;
                        $money = '(' . $currency_symbol . number_format($money, strlen($denom) - 1) . ')';
                } else {
                        $money = $currency_symbol . number_format($money, strlen($denom) - 1);
                }

                return $money;
        }

        /**
         * Presents a money value based on currency and denom only positive
         * 
         * @param int $value
         * @param string $currency_symbol
         * @param int $denom
         * @return string The money 
         */
        public static function showMoney2($value, $currency_symbol, $denom, $type = "asset") {

                $money = $value / $denom;

                if ($money < 0) {
                        $money *= -1;
                }
                $money = $currency_symbol . number_format($money, strlen($denom) - 1);

                return $money;
        }

        /**
         * Presents a money value based on currency and denom.
         * Used in charts
         * 
         * @param int $value
         * @param string $currency_symbol
         * @param int $denom
         * @return string The money 
         */
        public static function showMoney3($value, $denom, $type = 'asset') {

                $money = $value / $denom;

                if ($type == 'income' || $type == 'liability' || $type == 'equity') {
                        $money *= -1;
                }

                return $money;
        }

        /**
         * Presents the shares vs price of stock transaction
         * 
         * @param float $shares
         * @param float $price
         * @param string $currency_symbol
         * @param int $denom
         * @return string The text to present
         */
        public static function showShares($shares, $price, $currency_symbol, $denom) {

                $money = abs($price) / $denom;
                $money = $currency_symbol . number_format($money, strlen($denom) - 1);
                $text = '(' . (float) $shares . ' x ' . $money . ')';

                return $text;
        }

        /**
         * Upload a file in tmp folder
         * 
         * @param file $file  
         * @param string $new_name New file name       
         */
        public static function upload($file, $new_name = '') {
                $config = Factory::getApplication()->getConfig();
                if (empty($new_name)) {
                        $tmp_dest = $config->get('tmp_path') . '/' . File::makeSafe($file['name']);
                } else {
                        $tmp_dest = $config->get('tmp_path') . '/' . File::makeSafe($new_name);
                }
                $tmp_src = $file['tmp_name'];

                jimport('joomla.filesystem.file');
                if (!File::upload($tmp_src, $tmp_dest, false, true)) {
                        return false;
                }

                return $tmp_dest;
        }

        /**
         * Gets a list of the actions that can be performed.
         *
         * @param   string   $component  The component name.
         * @param   string   $section    The access section name.
         * @param   integer  $id         The item ID.
         *
         */
        public static function getActions($component = '', $section = '', $id = 0) {
                //override to return always true
                //need fixing if decide to use ACL
                //
                $actions = parent::getActions($component, $section, $id);

                foreach ($actions as $key => $value) {
                        if (is_bool($value)) {
                                $actions->{$key} = true;
                        }
                }

                return $actions;
        }

        /**
         * Add required entry in table #__content_types
         * to enable tags
         */
        public static function enableTags() {

                $content_type = new \stdClass();

                $content_type->type_id = 0;
                $content_type->type_title = 'Account';
                $content_type->type_alias = 'com_phmoney.account';

                $table = new \stdClass();
                $table->special = new \stdClass();
                $table->special->dbtable = '#__phmoney_accounts';
                $table->special->key = 'id';
                $table->special->type = 'Account';
                $table->special->prefix = 'JTable';
                $table->special->config = 'array()';
                $table->common = new \stdClass();
                $table->common->dbtable = '#__ucm_content';
                $table->common->key = 'ucm_id';
                $table->common->type = 'Corecontent';
                $table->common->prefix = 'JTable';
                $table->common->config = 'array()';
                $content_type->table = json_encode($table);

                $content_type->rules = '';

                $field_mappings = new \stdClass();
                $field_mappings->common = new \stdClass();
                $field_mappings->common->core_content_item_id = 'id';
                $field_mappings->common->core_title = 'title';
                $field_mappings->common->core_state = 'published';
                $field_mappings->common->core_alias = 'alias';
                $field_mappings->common->core_created_time = 'created_time';
                $field_mappings->common->core_modified_time = 'modified_time';
                $field_mappings->common->core_body = 'description';
                $field_mappings->common->core_hits = 'null';
                $field_mappings->common->core_publish_up = 'null';
                $field_mappings->common->core_publish_down = 'null';
                $field_mappings->common->core_access = 'access';
                $field_mappings->common->core_params = 'params';
                $field_mappings->common->core_featured = 'null';
                $field_mappings->common->core_metadata = 'null';
                $field_mappings->common->core_language = 'language';
                $field_mappings->common->core_images = 'null';
                $field_mappings->common->core_urls = 'null';
                $field_mappings->common->core_version = 'null';
                $field_mappings->common->core_ordering = 'null';
                $field_mappings->common->core_metakey = 'metakey';
                $field_mappings->common->core_metadesc = 'metadesc';
                $field_mappings->common->core_catid = 'null';
                $field_mappings->common->core_xreference = 'null';
                $field_mappings->common->asset_id = 'asset_id';
                $field_mappings->special = new \stdClass();
                $field_mappings->special->path = 'path';
                $field_mappings->special->code = 'code';
                $field_mappings->special->note = 'note';
                $field_mappings->special->portfolio_id = 'portfolio_id';
                $field_mappings->special->account_type_id = 'account_type_id';
                $field_mappings->special->currency_id = 'currency_id';
                $content_type->field_mappings = json_encode($field_mappings);

                $content_type->router = '';
                $content_type->content_history_options = '';

                $db = Factory::getDbo();
                $query = $db->getQuery(true);
                $query->insert('#__content_types');
                foreach ($content_type as $label => $value) {
                        $query->set($query->qn($label) . '=' . $query->q($value));
                }
                $db->setQuery($query);
                $db->execute();
        }

        public static function showWatermark() {
                $extension = new \Joomla\CMS\Table\Extension(Factory::getDbo());
                $extension->load(array('element' => 'pkg_phmoney'));
                $manifest = new \Joomla\Registry\Registry($extension->manifest_cache);
                $name = Text::_($manifest->get('name'));
                return strpos($name, 'FREE');
        }

}
