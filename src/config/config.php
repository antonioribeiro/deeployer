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

    /**
     * Deeployer will try to find Envoy executable automatically, unless you need 
     * to use a different one.
     */

    'envoy_executable_path' => 'automatic',

    /**
     * Pass Through mode let you define your tasks this way:
     *
     *   @task('https://<provider>/<vendor>/<repository>:<branch>', ['on' => ['localhost']])
     *      ...
     *   @endtask
     * 
     */
    
    'envoy_pass_through' => true,

    /**
     * Using webhooks usually makes apache or nginx user to be the one
     * executing your scripts. If you need to deploy using a different
     * user, like root, just set it in this variable.
     *
     * This can also be tricky, because apache and nginx might not have
     * rights to impersonate (sudo) another user. So, it's possible you'll
     * have to add something like this in your /etc/sudoers file:
     *
     *    www-data ALL=(ALL) NOPASSWD: /usr/local/bin/envoy
     *   
     */

    'envoy_user' => '',

    /**
     * How much time should PHP wait for Envoy?
     */

    'envoy_timeout' => 3600,

    /**
     * 
     */

    'projects' => array(
                            /**
                             * This is an example for Deeployer using Envoy in normal mode 
                             */
                            
                            // array(
                            //         'git_repository' => 'https://bitbucket.org/antonioribeiro/conveniosaude/',

                            //         'git_remote' => 'origin',

                            //         'git_branch' => 'master',

                            //         'envoy_tasks' => array('deploy_conveniosaude'),

                            //         'envoy_user' => 'root',
                            // ),


                            /**
                             * This is an example for those who don't want to use Envoy
                             */
                            // array(
                            //         'ssh_connection' => 'staging',

                            //         'git_repository' => 'https://github.com/antonioribeiro/acr.com',

                            //         'git_remote' => 'origin',

                            //         'git_branch' => 'staging',

                            //         'git_force_pull' => false,

                            //         'remote_directory' => '/var/www/vhosts/antoniocarlosribeiro.com/acr.com/staging/',

                            //         'composer_update' => true,

                            //         'composer_optimize_autoload' => true,

                            //         'composer_extra_options' => '',

                            //         'composer_timeout' => 60 * 5, // 5 minutes

                            //         'artisan_migrate' => false,

                            //         'post_deploy_commands' => array(
                            //                                             'bash database.restore.sh'
                            //                                         ),
                            //     ),
                        ),
);
