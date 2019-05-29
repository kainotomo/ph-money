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

namespace Joomla\Component\Phmoney\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

/**
 * Description of Router
 *
 * @author KAINOTOMO PH LTD <info@kainotomo.com>
 */
class Router extends RouterView {

        protected $noIDs = false;

        /**
         * The category factory
         *
         * @var CategoryFactoryInterface
         *
         * @since  4.0.0
         */
        private $categoryFactory;

        /**
         * The db
         *
         * @var DatabaseInterface
         *
         * @since  4.0.0
         */
        private $db;

        /**
         * Phmoney Component router constructor
         *
         * @param   SiteApplication           $app              The application object
         * @param   AbstractMenu              $menu             The menu object to work with
         * @param   CategoryFactoryInterface  $categoryFactory  The category object
         * @param   DatabaseInterface         $db               The database object
         */
        public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db) {

                /*

                  $this->categoryFactory = $categoryFactory;
                  $this->db = $db;
                  $params = ComponentHelper::getParams('com_phmoney');
                  $this->noIDs = (bool) $params->get('sef_ids');

                  $portfolios = new RouterViewConfiguration('portfolios');
                  $portfolios->setKey('portfolio_id');
                  $this->registerView($portfolios);

                  $accounts = new RouterViewConfiguration('accounts');
                  $accounts->setKey('account_id')->setParent($portfolios, 'portfolio_id');
                  $this->registerView($accounts);

                  $category = new RouterViewConfiguration('category');
                  $category->setKey('id')->setParent($categories, 'catid')->setNestable()->addLayout('blog');
                  $this->registerView($category);
                  $article = new RouterViewConfiguration('article');
                  $article->setKey('id')->setParent($category, 'catid');
                  $this->registerView($article);
                  $this->registerView(new RouterViewConfiguration('archive'));
                  $this->registerView(new RouterViewConfiguration('featured'));
                  $form = new RouterViewConfiguration('form');
                  $form->setKey('a_id');
                  $this->registerView($form);
                 * 
                 */

                parent::__construct($app, $menu);

                $this->attachRule(new MenuRules($this));
                //$this->attachRule(new StandardRules($this));
                $this->attachRule(new NomenuRules($this));
        }

}
