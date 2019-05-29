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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Description of PortfoliosModel
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class PortfoliosModel extends ListModel {

        protected function getListQuery() {

                // Create a new query object.
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $user = Factory::getUser();

                // Select the required fields from the table.
                $query->select(
                        $this->getState(
                                'list.select', 'a.id, a.title, a.user_id, a.currency_id, ' .
                                'a.published,  a.user_default'
                        )
                );
                $query->from('#__phmoney_portfolios AS a');

                // Join over the currencies.
                $query->select(
                                'c.name as currency_name'
                        )
                        ->join('LEFT', '#__phmoney_currencys AS c ON c.id = a.currency_id');

                // Filter by user
                $query->where('a.user_id  = ' . (int) $user->id);

                // Filter by published state
                $published = (string) $this->getState('filter.published');

                if (is_numeric($published)) {
                        $query->where('a.published = ' . (int) $published);
                } elseif ($published === '') {
                        $query->where('(a.published = 0 OR a.published = 1)');
                }

                // Filter by currency
                $currency = (string) $this->getState('filter.currency');
                if (is_numeric($currency)) {
                        $query->where('a.currency_id = ' . (int) $currency);
                }

                // Filter by a single or group of tags.
		$hasTag = false;
		$tagId  = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$hasTag = true;

			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId);
		}
		elseif (is_array($tagId))
		{
			$tagId = ArrayHelper::toInteger($tagId);
			$tagId = implode(',', $tagId);
			if (!empty($tagId))
			{
				$hasTag = true;

				$query->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tagId . ')');
			}
		}

		if ($hasTag)
		{
			$query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
				. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_phmoney.portfolio')
			);
		}

                // Filter by search in title.
                $search = $this->getState('filter.search');

                if (!empty($search)) {
                        if (stripos($search, 'id:') === 0) {
                                $query->where('a.id = ' . (int) substr($search, 3));
                        } else {
                                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                                $query->where('(a.title LIKE ' . $search . ' OR a.description LIKE ' . $search . ')');
                        }
                }

                // Add the list ordering clause.
                $orderCol = $this->state->get('list.ordering', 'a.id');
                $orderDirn = $this->state->get('list.direction', 'DESC');

                $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

                return $query;
        }

}
