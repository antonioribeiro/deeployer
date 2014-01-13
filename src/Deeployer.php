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
use Illuminate\Log\Writer;

class Deeployer
{
    private $config;

    private $log;
 
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
                                    Writer $log,
                                    Request $request,
                                    Github $github,
                                    Bitbucket $bitbucket
                                )
    {
        $this->config = $config;

        $this->log = $log;

        $this->request = $request;

        $this->github = $github;

        $this->bitbucket = $bitbucket;
    }

    public function run()
    {
        $this->originalPayload = $this->request->get('payload');

        $this->payload = $this->decodePayload($this->originalPayload);

        if ( ! $this->request->has('payload'))
        {
            $this->message('no payload was sent in the POST.');
        }
        else
        if ( ! isset($this->payload))
        {
            $this->message('payload received was empty.');
        }
        else
        if ($service = $this->getService())
        {
            $service->deploy($this->payload);
        }
        else
        {
            $this->message('service not found.');
            $this->message('payload received: '.$this->originalPayload);
        }
    }

    protected function decodePayload($payload)
    {
        return json_decode( $payload );
    }

    public function getService()
    {
        if ($this->github->payloadIsFromGithub($this->payload))
        {
            return $this->github;
        }

        if ($this->bitbucket->payloadIsFromBitbucket($this->payload))
        {
            return $this->bitbucket;
        }

        return false;
    }

    private function message($message)
    {
        $this->log->info(sprintf('Deeployer: %s', $message));
    }    
}
