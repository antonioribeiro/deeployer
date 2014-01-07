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
 
namespace PragmaRX\Deeployer\Support;

class Artisan extends Execute {

    public function migrate($extra = '')
    {
        return $this->command('migrate', $extra);
    }

    private function findSoftware()
    {
        $artisan = $this->workingPath.'/artisan';

        if ($this->files->exists($artisan))
        {
            return 'php '.$artisan;
        }
        
        throw new Exceptions\ArtisanNotFoundException('No Artisan found at '.$this->workingPath);
    }

}
