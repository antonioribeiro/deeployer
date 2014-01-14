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

return array(

    'create_deeployer_alias' => true,

    'deeployer_alias' => 'Deeployer',

    'projects' => array(
                            array(
                                    'ssh_connection' => 'staging',

                                    'git_repository' => 'https://github.com/antonioribeiro/acr.com',

                                    'git_remote' => 'origin',

                                    'git_branch' => 'staging',

                                    'git_force_pull' => false,

                                    'remote_directory' => '/var/www/vhosts/antoniocarlosribeiro.com/acr.com/staging/',

                                    'composer_update' => true,

                                    'composer_optimize_autoload' => true,

                                    'composer_extra_options' => '',

                                    'composer_timeout' => 60 * 5, // 5 minutes

                                    'artisan_migrate' => false,

                                    'post_deploy_commands' => array(
                                                                        'bash database.restore.sh'
                                                                    ),
                                ),
                        ),
);
