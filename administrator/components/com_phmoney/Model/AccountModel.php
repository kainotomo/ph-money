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

namespace Joomla\Component\Phmoney\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\String\StringHelper;
use Joomla\Component\Phmoney\Administrator\Helper\PhmoneyHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

require_once __DIR__ . '/../libraries/vendor/autoload.php';

use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

/**
 * Description of AccountModel
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class AccountModel extends AdminModel
{

        /**
         * The type alias for this content type. Used for content version history.
         *
         * @var      string
         */
        public $typeAlias = null;

        public function __construct($config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null, \Joomla\CMS\Form\FormFactoryInterface $formFactory = null)
        {
                $this->typeAlias = 'com_phmoney.account';
                parent::__construct($config, $factory, $formFactory);
        }

        /**
         * Method to get the row form.
         *
         * @param   array    $data      Data for the form.
         * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
         *
         * @return  \JForm|boolean  A JForm object on success, false on failure
         *
         */
        public function getForm($data = array(), $loadData = true)
        {
                $jinput = Factory::getApplication()->input;

                // Get the form.
                $form = $this->loadForm('com_phmoney.account', 'account', array('control' => 'jform', 'load_data' => $loadData));

                if (empty($form)) {
                        return false;
                }

                $jinput = Factory::getApplication()->input;

                /*
                 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
                 * The back end uses id so we use that the rest of the time and set it to 0 by default.
                 */
                $id = $jinput->get('a_id', $jinput->get('id', 0));

                // Determine correct permissions to check.
                if (!$this->getState('portfolio.id')) {
                        // New record. Can only create in selected categories.
                        $portfolio_id = Factory::getApplication()->getUserStateFromRequest('com_phmoney.accounts.filter.portfolio', 'filter_portfolio');
                        $form->setFieldAttribute('portfolio_id', 'default', $portfolio_id);
                }

                return $form;
        }

        /**
         * Method to get the data that should be injected in the form.
         *
         * @return  mixed  The data for the form.
         *
         */
        protected function loadFormData()
        {
                // Check the session for previously entered form data.
                $app = Factory::getApplication();
                $data = $app->getUserState('com_phmoney.edit.account.data', array());

                if (empty($data)) {
                        $data = $this->getItem();

                        // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Split Manager: Splits
                        if ($this->getState('account.id') == 0) {
                                $filters = (array) $app->getUserState('com_phmoney.accounts.filter', array('portfolio' => PhmoneyHelper::getDefaultPortfolio()));
                                $data->set(
                                        'state', $app->input->getInt(
                                                'state', ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                                        )
                                );
                                //$data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                                $data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : Factory::getConfig()->get('access'))));
                                $portfolio_id = (int) $filters['portfolio'];
                                $data->set('portfolio_id', $portfolio_id);
                                $db = $this->getDbo();
                                $query = $db->getQuery(true);
                                $query->select('currency_id')
                                        ->from('#__phmoney_portfolios')
                                        ->where('id = ' . $portfolio_id);
                                $db->setQuery($query);
                                $currency_id = $db->loadResult();
                                $data->set('currency_id', $currency_id);
                        }
                }

                // If there are params fieldsets in the form it will fail with a registry object
                if (isset($data->params) && $data->params instanceof Registry) {
                        $data->params = $data->params->toArray();
                }

                $this->preprocessData('com_phmoney.account', $data);

                return $data;
        }

        /**
         * Method to save the form data.
         *
         * @param   array  $data  The form data.
         *
         * @return  boolean  True on success, False on error.
         *
         */
        public function save($data)
        {

                $table = $this->getTable();
                $input = Factory::getApplication()->input;
                $pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
                $isNew = true;
                $context = $this->option . '.' . $this->name;

                if (!empty($data['tags']) && $data['tags'][0] != '') {
                        $table->newTags = $data['tags'];
                }

                if (isset($data['params']) && is_array($data['params'])) {
                        $params = new Registry($data['params']);

                        $data['params'] = (string) $params;
                }

                // Include the plugins for the save events.
                PluginHelper::importPlugin($this->events_map['save']);

                // Load the row if saving an existing category.
                if ($pk > 0) {
                        $table->load($pk);
                        $isNew = false;
                }

                // Set the new parent id if parent id not matched OR while New/Save as Copy .
                if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
                        $table->setLocation($data['parent_id'], 'last-child');
                }

                // Alter the title for save as copy
                if ($input->get('task') == 'save2copy') {
                        $origTable = clone $this->getTable();
                        $origTable->load($input->getInt('id'));

                        if ($data['title'] == $origTable->title) {
                                list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
                                $data['title'] = $title;
                                $data['alias'] = $alias;
                        } else {
                                if ($data['alias'] == $origTable->alias) {
                                        $data['alias'] = '';
                                }
                        }

                        $data['published'] = 0;
                }

                // Bind the data.
                if (!$table->bind($data)) {
                        $this->setError($table->getError());

                        return false;
                }

                // Bind the rules.
                if (isset($data['rules'])) {
                        $rules = new \JAccessRules($data['rules']);
                        $table->setRules($rules);
                }

                if (isset($data['moveFlag'])) {
                        if ($data['moveFlag'] == false) {
                                // Check the data.
                                if (!$table->check()) {
                                        $this->setError($table->getError());

                                        return false;
                                }
                        }
                        unset($data['moveFlag']);
                }

                // Trigger the before save event.
                $result = Factory::getApplication()->triggerEvent($this->event_before_save, array($context, &$table, $isNew, $data));

                if (in_array(false, $result, true)) {
                        $this->setError($table->getError());

                        return false;
                }

                // Store the data.
                if (!$table->store()) {
                        $this->setError($table->getError());

                        return false;
                }

                // Trigger the after save event.
                Factory::getApplication()->triggerEvent($this->event_after_save, array($context, &$table, $isNew, $data));

                // Rebuild the path for the category:
                if (!$table->rebuildPath($table->id)) {
                        $this->setError($table->getError());

                        return false;
                }

                // Rebuild the paths of the category's children:
                if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                        $this->setError($table->getError());

                        return false;
                }

                $this->setState($this->getName() . '.id', $table->id);

                // Clear the cache
                $this->cleanCache();

                return true;
        }

        public function validate($form, $data, $group = null)
        {

                $data = parent::validate($form, $data, $group);

                //validate checked_out
                if (empty($data['checked_out'])) {
                        $data['checked_out'] = null;
                }
                if (empty($data['checked_out_time'])) {
                        $data['checked_out_time'] = null;
                }

                //validate parent account type
                if ((int) $data['parent_id'] != 1) {
                        $db = $this->getDbo();
                        $query = $db->getQuery(true);

                        $query->select('act.value')
                                ->from('#__phmoney_account_types as act')
                                ->where('act.id = ' . (int) $data['account_type_id']);
                        $db->setQuery($query);
                        $account_type = $db->loadResult();

                        $query->clear();
                        $query->select('act.value')
                                ->from('#__phmoney_accounts as ac')
                                ->where('ac.id = ' . (int) $data['parent_id'])
                                ->join('LEFT', '#__phmoney_account_types AS act ON act.id = ac.account_type_id');
                        $db->setQuery($query);
                        $parent_account_type = $db->loadResult();

                        switch ($account_type) {
                                case 'asset':
                                        if ($parent_account_type != 'asset' && $parent_account_type != 'share') {
                                                $data = false;
                                        }
                                        break;
                                case 'share':
                                        if ($parent_account_type != 'asset' && $parent_account_type != 'share') {
                                                $data = false;
                                        }
                                        break;
                                case 'liability':
                                        if ($parent_account_type != 'liability') {
                                                $data = false;
                                        }
                                        break;
                                case 'income':
                                        if ($parent_account_type != 'income' && $parent_account_type != 'expense') {
                                                $data = false;
                                        }
                                        break;
                                case 'expense':
                                        if ($parent_account_type != 'income' && $parent_account_type != 'expense') {
                                                $data = false;
                                        }
                                        break;
                                case 'equity':
                                        if ($parent_account_type != 'equity') {
                                                $data = false;
                                        }
                                        break;
                                default:
                                        $data = false;
                                        break;
                        }
                        if ($data == false) {
                                $this->setError(Text::_('COM_PHMONEY_PARENT_ACCOUNT_TYPE'));
                        }
                }

                if ($data == false) {
                        return false;
                }

                /*                 * hacking tags problem
                 * 
                 */
                if (empty($data['params'])) {
                        $data['params'] = "{}";
                }
                $data['metakey'] = "{}";
                $data['metadata'] = "{}";
                $data['metadesc'] = "{}";
                $data['images'] = "{}";
                $data['urls'] = "{}";
                return $data;
        }

        public function getItem($pk = null)
        {

                $item = parent::getItem($pk);

                if ($item) {

                        if (!empty($item->id)) {

                                $item->tags = new TagsHelper;
                                $item->tags->getTagIds($item->id, 'com_phmoney.account');

                                // Convert the params field to an array.
                                $params = new Registry($item->params);
                                $item->params = $params->toArray();
                        }
                }

                return $item;
        }

        /**
         * Method to change the title & alias.
         *
         * @param   integer  $category_id  The id of the category.
         * @param   string   $alias        The alias.
         * @param   string   $title        The title.
         *
         * @return	array  Contains the modified title and alias.
         *
         */
        protected function generateNewTitle($category_id, $alias, $title)
        {
                // Alter the title & alias
                $table = $this->getTable();

                while ($table->load(array('alias' => $alias, 'parent_id' => $category_id))) {
                        $title = StringHelper::increment($title);
                        $alias = StringHelper::increment($alias, 'dash');
                }

                return array($title, $alias);
        }

        /**
         * Retrieve shares statistics from Yahoo
         * 
         * @param string $code The code from Yahoo
         * @return ApiClient The quote api client
         */
        public function download($code)
        {
                // Create a new client from the factory
                $client = ApiClientFactory::createApiClient();
                // Or use your own Guzzle client and pass it in
                $options = [/* ... */];
                $guzzleClient = new Client($options);
                $client = ApiClientFactory::createApiClient($guzzleClient);
                $quote = $client->getQuote($code);

                return $quote;
        }

        /**
         * Calculate Intrinsic value
         * @param array $data
         * @param ApiClient $quote the downloaded quote
         */
        public function calculateIntrinsicValue(&$data, $quote = null)
        {

                $table = $this->getTable();
                $table->calculateIntrinsicValue($data, $quote);
        }

        /**
         * Method to run batch operations.
         * 
         * @param array $commands The data of the batch form.
         * @param array $pks The accounts ids.
         * @param array $contexts Full name of account.
         * 
         * @retur boolean True if successful, false otherwise and internal error is set.
         */
        public function batch($commands, $pks, $contexts)
        {
                foreach ($pks as $pk) {
                        $item = $this->getItem($pk);
                        $itemTable = ArrayHelper::fromObject($item);
                        $data = array();
                        $moveFlag = false;

                        //copy
                        if ($commands['action_batch'] == 1) {
                                $data['id'] = 0;
                        } else {
                                //move
                                $data['id'] = $itemTable['id'];
                                if ($commands['parent_id'] == -1 && $commands['portfolio_id'] == -1) {
                                        $moveFlag = true;
                                }
                        }

                        //create data table to save account
                        $data['title'] = $itemTable['title'];
                        $data['account_type_id'] = $itemTable['account_type_id'];
                        $data['currency_id'] = $itemTable['currency_id'];
                        if ($commands['parent_id'] != -1) {
                                $data['parent_id'] = $commands['parent_id'];
                        } else {
                                $data['parent_id'] = $itemTable['parent_id'];
                        }
                        if ($commands['tags'] != null) {
                                $data['tags'] = $commands['tags'];
                        } else {
                                $data['tags'] = $itemTable['tagsHelper']['tags'];
                        }
                        $data['published'] = $itemTable['published'];
                        $data['code'] = $itemTable['code'];
                        $data['note'] = $itemTable['note'];
                        $data['alias'] = $itemTable['alias'];
                        if ($commands['portfolio_id'] != -1) {
                                $data['portfolio_id'] = $commands['portfolio_id'];
                        } else {
                                $data['portfolio_id'] = $itemTable['portfolio_id'];
                        }
                        $data['description'] = $itemTable['description'];
                        $data['path'] = $itemTable['path'];
                        $data['params']['address'] = $itemTable['params']['address'];
                        $data['params']['country'] = $itemTable['params']['country'];
                        $data['params']['isin'] = $itemTable['params']['isin'];
                        $data['metakey'] = $itemTable['metakey'];
                        $data['metadata'] = $itemTable['metadata'];
                        $data['metadesc'] = $itemTable['metadesc'];
                        $data['images'] = $itemTable['images'];
                        $data['urls'] = $itemTable['urls'];

                        $validAccType = $this->validateAccountType($data);

                        if (!$validAccType) {
                                return false;
                        }

                        if ($moveFlag == false) {
                                $data['moveFlag'] = false;
                        } else {
                                $data['moveFlag'] = true;
                        }

                        $result = $this->save($data);

                        if ($result == false) {
                                return false;
                        }
                }
                return true;
        }

        protected function validateAccountType($data)
        {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('at.value')
                        ->from('#__phmoney_accounts AS a')
                        ->join('LEFT', $db->quoteName('#__phmoney_account_types') . ' AS at ON at.id = a.account_type_id')
                        ->where('a.id  = ' . (int) $data['parent_id']);

                $db->setQuery($query);
                $parent_account_type = $db->loadResult();

                $query = $db->getQuery(true);

                $query->select('a.value')
                        ->from('#__phmoney_account_types AS a')
                        ->where('a.id  = ' . (int) $data['account_type_id']);

                $db->setQuery($query);
                $dataAccType = $db->loadResult();

                switch ($dataAccType) {
                        case 'asset':
                                if ($parent_account_type != 'asset' && $parent_account_type != 'share') {
                                        $data = false;
                                }
                                break;
                        case 'share':
                                if ($parent_account_type != 'asset' && $parent_account_type != 'share') {
                                        $data = false;
                                }
                                break;
                        case 'liability':
                                if ($parent_account_type != 'liability') {
                                        $data = false;
                                }
                                break;
                        case 'income':
                                if ($parent_account_type != 'income' && $parent_account_type != 'expense') {
                                        $data = false;
                                }
                                break;
                        case 'expense':
                                if ($parent_account_type != 'income' && $parent_account_type != 'expense') {
                                        $data = false;
                                }
                                break;
                        case 'equity':
                                if ($parent_account_type != 'equity') {
                                        $data = false;
                                }
                                break;
                        default:
                                $data = false;
                                break;
                }
                if ($data == false) {
                        $this->setError(Text::_('COM_PHMONEY_PARENT_ACCOUNT_TYPE'));
                        return false;
                }

                return true;
        }

}
