<?php

/**
 * Part of the Deeployer package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Deeployer
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Deeployer;

use PragmaRX\Deeployer\Support\Config;

use PragmaRX\Deeployer\Deployers\Github;
use PragmaRX\Deeployer\Deployers\Bitbucket;

use Illuminate\Http\Request;

use Log;

class Deeployer
{
	private $config;
 
	private $request;

	private $github;

	private $bitbucket;

	private $payload;

	/**
	 * Initialize Deeployer object
	 * 
	 * @param Locale $locale
	 */
	public function __construct(
									Config $config,	
									Request $request,
									Github $github,
									Bitbucket $bitbucket
								)
	{
		$this->config = $config;

		$this->request = $request;

		$this->github = $github;

		$this->bitbucket = $bitbucket;

		$this->payload = $this->decodePayload($this->request->get('payload'));
	}

	public function run()
	{
		$service = $this->getServiceName();

		$service->deploy($this->payload);
	}

	protected function decodePayload($payload)
	{
		return json_decode( $payload );
	}

	public function getServiceName()
	{
		if (strpos($this->payload->repository->url, 'github') > 0)
		{
			return $this->github;
		}
		else
		if (strpos($this->payload->repository->url, 'bitbucket') > 0)
		{
			return $this->bitbucket;
		}

		return false;
	}
}
