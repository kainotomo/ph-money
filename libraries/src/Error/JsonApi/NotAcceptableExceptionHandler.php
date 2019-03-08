<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\JsonApi;

defined('JPATH_PLATFORM') or die;

use Exception;
use Joomla\CMS\Application\Exception\NotAcceptable;
use Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

/**
 * Handler for routing errors that should give a 406
 *
 * @since  4.0
 */
class NotAcceptableExceptionHandler implements ExceptionHandlerInterface
{
	/**
	 * If the exception handler is able to format a response for the provided exception,
	 * then the implementation should return true.
	 *
	 * @param   \Exception  $e  The exception to be handled
	 *
	 * @return bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function manages(Exception $e)
	{
		return $e instanceof NotAcceptable;
	}

	/**
	 * Handle the provided exception.
	 *
	 * @param   Exception  $e  The exception being handled
	 *
	 * @return  \Tobscure\JsonApi\Exception\Handler\ResponseBag
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function handle(Exception $e)
	{
		$status = 406;
		$error = ['title' => 'Not Acceptable'];

		$code = $e->getCode();

		if ($code)
		{
			$error['code'] = $code;
		}

		return new ResponseBag($status, [$error]);
	}
}