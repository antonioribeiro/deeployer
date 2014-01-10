# Deeployer v0.8.0

[![Latest Stable Version](https://poser.pugx.org/pragmarx/deeployer/v/stable.png)](https://packagist.org/packages/pragmarx/deeployer)

#### Automatically deploy Laravel applications every time it's pushed to the remote repository

Deployment via git webhooks is a common functionallity of most PaaS these days. This package is intended to be used for those that are hosting their websites in VPS, dedicated server or any other hosting provider that doesn't support web hooks.

Since this package uses Laravel remote (SSH-2) functionality to remote or locally deploy applications, your deployment app can be in one server and deploy applications to others, as many as you need.

### Usage

Define an url for your deployer to be used, in Github you can find this at Settings > Service Hooks > WebHook URLs > URL, example:

```
http://deployer.yoursite.io/deploy
```

Create a route in your application for the deployer url:

```
Route::post('deploy', function() 
{
    return Deeployer::run();
});
```

Edit the file `app/config/packages/pragmarx/deeployer/config.php` and create your projects. In my opinion, is better to not use the `master` branch while automatically deploying apps:

```
'projects' => array(
                        array(
                                'ssh_connection' => 'yoursite-staging',

                                'git_repository' => 'https://github.com/yourname/yoursite.io',

                                'git_remote' => 'origin',

                                'git_branch' => 'staging',

                                'remote_directory' => '/var/www/vhosts/yoursite.io/staging/',

                                'composer_update' => true,

                                'composer_optimize_autoload' => true,

                                'composer_extra_options' => '',

                                'artisan_migrate' => false,

                                'post_deploy_commands' => array(
                                                                    'zsh send-deployment-emails.sh',
                                                                ),
                            ),
                    ),
```

Create the remote connection by editing `app/config/remote.php`:

```
	'connections' => array(

		'yoursite-staging' => array(
			'host'     => 'yoursite.com:22', <-- you can set a different SSH port if you need 
			'username' => 'root',            <-- the user you use to deploy applications on your server
			'password' => 'Bassw0rt',
			'key'      => '',                <-- key files are safer than passwords
			'root'     => '/var/www',        <-- you can ignore this, deployment path will be changed by Deeployer
		),

	),
```

Go to your server and `tail` the log file:

```
php artisan tail
```

Add that url to Github and push something to your branch to automatically deploy your application:

```
git pull origin master:testing
git pull origin master:staging
git pull origin master:production
```

### Installation

#### Requirements

- Laravel 4.1+
- Composer >= 2014-01-07 - This is a PSR-4 package
- SSH-2 Server

#### Installing

`composer require pragmarx/deeployer dev-master`

Once this operation completes, add the service provider to your app/config/app.php:

```
'PragmaRX\Deeployer\Vendor\Laravel\ServiceProvider',
```

Publish the configuration file:

```
artisan config:publish pragmarx/deeployer
```

### TODO

- Bitbucket is not done yet.
- Create a deployment artisan command, to manually deploy something troubled.
- Tests, tests, tests.

### Author

Antonio Carlos Ribeiro - <acr@antoniocarlosribeiro.com> - <http://twitter.com/iantonioribeiro>

### License

Deeployer is licensed under the MIT License - see the `LICENSE` file for details

### Contributing

Pull requests and issues are more than welcome.