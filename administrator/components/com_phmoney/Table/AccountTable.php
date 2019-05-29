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
use Joomla\CMS\Table\Nested;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Description of AccountTable
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class AccountTable extends Nested {

    /**
     * Object constructor to set table and key fields.  In most cases this will
     * be overridden by child classes to explicitly set the table and key fields
     * for a particular database table.
     *
     * @param   string               $table       Name of the table to model.
     * @param   mixed                $key         Name of the primary key field in the table or array of field names that compose the primary key.
     * @param   \JDatabaseDriver     $db          JDatabaseDriver object.
     * @param   DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     */
    public function __construct(\JDatabaseDriver $db) {
        $this->typeAlias = 'com_phmoney.account';

        parent::__construct('#__phmoney_accounts', 'id', $db);
    }

    public function check() {
        // Generate a valid alias
        $this->generateAlias();

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
                ->from('#__phmoney_accounts AS a')
                ->where('a.parent_id  = ' . (int) $this->parent_id)
                ->where('a.portfolio_id  = ' . (int) $this->portfolio_id)
                ->where('a.alias  = ' . $db->q($this->alias));

        $db->setQuery($query);
        $result = $db->loadAssocList();

        //check if there is an entry with same alias/title
        while ($result != null) {
            $this->alias .= "-copy";
            $this->title .= " - Copy";

            $query = $db->getQuery(true);
            
            $query->select('*')
                    ->from('#__phmoney_accounts AS a')
                    ->where('a.parent_id  = ' . (int) $this->parent_id)
                    ->where('a.portfolio_id  = ' . (int) $this->portfolio_id)
                    ->where('a.alias  = ' . $db->q($this->alias));

            $db->setQuery($query);
            $result = $db->loadAssocList();
        }

        return parent::check();
    }

    /**
     * Generate a valid alias from title / date.
     * Remains public to be able to check for duplicated alias before saving
     *
     * @return  string
     */
    public function generateAlias() {
        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    /**
     * Calculate Intrinsic value
     * @param array $data
     * @param ApiClient $quote the downloaded quote
     */
    public function calculateIntrinsicValue(&$data = null, $quote = null) {

        if (is_null($data)) {
            $data = array();
            $data['params'] = array();
        }

        if (!is_null($quote)) {
            if (!is_numeric($data['params']['investment_horizon'])) {
                $data['params']['investment_horizon'] = 5;
            }
            if (!is_numeric($data['params']['margin'])) {
                $data['params']['margin'] = 20;
            }
            $data['params']['eps_trailing'] = round($quote->getEpsTrailingTwelveMonths(), 2);
            $data['params']['eps_forward'] = round($quote->getEpsForward(), 2);
            $data['params']['pe_trailing'] = round($quote->getTrailingPE(), 2);
            $data['params']['pe_forward'] = round($quote->getForwardPE(), 2);
            $data['params']['dividend_trailing_rate'] = round($quote->getTrailingAnnualDividendRate(), 2);
            $data['params']['dividend_trailing'] = round($quote->getTrailingAnnualDividendYield() * 100, 2);
            $data['params']['price'] = round($quote->getRegularMarketPrice(), 2);
        }

        //check share statistics
        $valid_statistics = is_numeric($data['params']['investment_horizon']) && is_numeric($data['params']['margin']) && is_numeric($data['params']['eps_trailing']) && is_numeric($data['params']['eps_forward']) && is_numeric($data['params']['pe_forward']) && is_numeric($data['params']['dividend_trailing']) && is_numeric($data['params']['price']);
        if (!$valid_statistics) {
            $this->setError(Text::_('COM_PHMONEY_NON_NUMERIC_VALUES'));
            return false;
        }

        $eps_growth_rate = ($data['params']['eps_forward'] - $data['params']['eps_trailing']) / $data['params']['eps_trailing'];
        $sumForecastedEPS = 0;
        $forecastedEPS = array();
        $forecastedEPS[1] = $data['params']['eps_trailing'] * (1 + $eps_growth_rate);
        $sumForecastedEPS += $forecastedEPS[1];
        for ($i = 2; $i <= $data['params']['investment_horizon']; $i++) {
            $forecastedEPS[$i] = $forecastedEPS[$i - 1] + ($forecastedEPS[$i - 1] * $eps_growth_rate);
            $sumForecastedEPS += $forecastedEPS[$i];
        }
        $estimatedEPS = $data['params']['eps_trailing'] * (pow(1 + $eps_growth_rate, $data['params']['investment_horizon']));
        $forecastPrice = $estimatedEPS * $data['params']['pe_forward'];
        $estimatedDividends = $sumForecastedEPS * $data['params']['dividend_trailing'] / 100;
        $totalEstimatedValue = $estimatedDividends + $forecastPrice;
        $data['params']['intrinsic_value'] = round(
                $totalEstimatedValue / pow(1 + $eps_growth_rate, $data['params']['investment_horizon'])
                , 2);
        if (is_nan($data['params']['intrinsic_value'])) {
            unset($data['params']['intrinsic_value']);
            return;
        }
        $data['params']['estimated_signal_value'] = round(
                $data['params']['intrinsic_value'] - ($data['params']['intrinsic_value'] * $data['params']['margin'] / 100)
                , 2);
        if (is_nan($data['params']['estimated_signal_value'])) {
            unset($data['params']['estimated_signal_value']);
            return;
        }
        $data['params']['estimated_margin_of_safety'] = round(
                ($data['params']['intrinsic_value'] - $data['params']['price']) / $data['params']['intrinsic_value'] * 100
                , 2);
        if (is_nan($data['params']['estimated_margin_of_safety'])) {
            unset($data['params']['estimated_margin_of_safety']);
            return;
        }
        if ($data['params']['price'] < $data['params']['estimated_signal_value']) {
            $data['params']['signal'] = Text::_('COM_PHMONEY_BUY');
        } else {
            $data['params']['signal'] = Text::_('COM_PHMONEY_SELL');
        }
    }

}
