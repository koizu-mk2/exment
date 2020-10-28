<?php

namespace Exceedone\Exment;

use Storage;
use Encore\Admin\Admin;
use Encore\Admin\Middleware as AdminMiddleware;
use Encore\Admin\AdminServiceProvider as ServiceProvider;
use Exceedone\Exment\Providers as ExmentProviders;
use Exceedone\Exment\Model\Plugin;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Services\Plugin\PluginPublicBase;
use Exceedone\Exment\Services\Plugin\PluginApiBase;
use Exceedone\Exment\Enums\Driver;
use Exceedone\Exment\Enums\ApiScope;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\PluginType;
use Exceedone\Exment\Validator\ExmentCustomValidator;
use Exceedone\Exment\Middleware\Initialize;
use Exceedone\Exment\Database as ExmentDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Connection;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Webpatser\Uuid\Uuid;

class ExmentServiceProvider extends ServiceProvider
{
    
    /**
     * Application Policy Map
     *
     * @var array
     */
    protected $policies = [
        'Exceedone\Exment\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * ServiceProviders
     *
     * @var array
     */
    protected $serviceProviders = [
        ExmentProviders\RouteServiceProvider::class,
        ExmentProviders\Route2factorServiceProvider::class,
        ExmentProviders\RouteOAuthServiceProvider::class,
        ExmentProviders\PasswordResetServiceProvider::class,
        ExmentProviders\PluginServiceProvider::class,
    ];

    /**
     * @var array commands
     */
    protected $commands = [
        'Exceedone\Exment\Console\InstallCommand',
        'Exceedone\Exment\Console\UpdateCommand',
        'Exceedone\Exment\Console\PublishCommand',
        'Exceedone\Exment\Console\ScheduleCommand',
        'Exceedone\Exment\Console\NotifyScheduleCommand',
        'Exceedone\Exment\Console\BatchCommand',
        'Exceedone\Exment\Console\BackupCommand',
        'Exceedone\Exment\Console\RestoreCommand',
        'Exceedone\Exment\Console\ClientListCommand',
        'Exceedone\Exment\Console\BulkInsertCommand',
        'Exceedone\Exment\Console\PatchDataCommand',
        'Exceedone\Exment\Console\InitTestCommand',
        'Exceedone\Exment\Console\CheckLangCommand',
        'Exceedone\Exment\Console\NotifyTestCommand',
        'Exceedone\Exment\Console\RefreshDataCommand',
        'Exceedone\Exment\Console\RefreshTableDataCommand',
        'Exceedone\Exment\Console\ImportCommand',
        'Exceedone\Exment\Console\ExportCommand',
        'Exceedone\Exment\Console\ExportChunkCommand',
        'Exceedone\Exment\Console\ResetPasswordCommand',
    ];

    
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Exceedone\Exment\Middleware\TrustProxies::class,
        \Exceedone\Exment\Middleware\ExmentDebug::class,
    ];


    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin.auth'       => \Exceedone\Exment\Middleware\Authenticate::class,
        'admin.auth-2factor'       => \Exceedone\Exment\Middleware\Authenticate2factor::class,
        'admin.password-limit'       => \Exceedone\Exment\Middleware\AuthenticatePasswordLimit::class,
        'admin.bootstrap2'  => \Exceedone\Exment\Middleware\Bootstrap::class,
        'admin.initialize'  => \Exceedone\Exment\Middleware\Initialize::class,
        'admin.login'  => \Exceedone\Exment\Middleware\Login::class,
        'admin.morph'  => \Exceedone\Exment\Middleware\Morph::class,
        'adminapi.auth'       => \Exceedone\Exment\Middleware\AuthenticateApi::class,
        'admin.browser'  => \Exceedone\Exment\Middleware\Browser::class,
        'admin.web-ipfilter'  => \Exceedone\Exment\Middleware\WebIPFilter::class,
        'admin.api-ipfilter'  => \Exceedone\Exment\Middleware\ApiIPFilter::class,
        'admin.log'        => \Exceedone\Exment\Middleware\LogOperation::class,

        'admin.pjax'       => AdminMiddleware\Pjax::class,
        'admin.permission' => AdminMiddleware\Permission::class,
        'admin.bootstrap'  => AdminMiddleware\Bootstrap::class,
        'admin.session'    => AdminMiddleware\Session::class,
        
        'pluginapi.auth'       => \Exceedone\Exment\Middleware\AuthenticatePluginApi::class,

        'scope' => \Exceedone\Exment\Middleware\CheckForAnyScope::class,

        'laravel-page-speed.space' => \Exceedone\Exment\Middleware\CollapseWhitespace::class,
        'laravel-page-speed.jscomments' => \Exceedone\Exment\Middleware\InlineJsRemoveComments::class,
        'laravel-page-speed.comments' => \RenatoMarinho\LaravelPageSpeed\Middleware\RemoveComments::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        // Exment web page default
        'admin' => [
            'admin.browser',
            'admin.web-ipfilter',
            'admin.initialize',
            'admin.auth',
            'admin.auth-2factor',
            'admin.password-limit',
            'admin.morph',
            'admin.bootstrap2',
            'laravel-page-speed.space',
            'laravel-page-speed.jscomments',
            'laravel-page-speed.comments',
            'admin.pjax',
            'admin.log',
            'admin.bootstrap',
            'admin.permission',
            'admin.session',
        ],
        // Exment not login web page. (Ex. login, forget password)
        'admin_anonymous' => [
            'admin.browser',
            'admin.web-ipfilter',
            'admin.initialize',
            'admin.login',
            'admin.morph',
            'admin.bootstrap2',
            'admin.pjax',
            'admin.log',
            'admin.bootstrap',
            'admin.permission',
            'admin.session',
        ],
        // Exment not login web page, and simple config, (Almost use image)
        'admin_anonymous_simple' => [
            'admin.web-ipfilter',
            'admin.initialize',
        ],
        // Exment install page
        'admin_install' => [
            'admin.browser',
            'admin.web-ipfilter',
            'admin.initialize',
            'admin.session',
        ],
        // Exment plugin's css and js.
        'admin_plugin_public' => [
            'admin.auth',
            'admin.auth-2factor',
            'admin.bootstrap2',
        ],
        // Exment Web page. custom verify
        'adminweb' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Exceedone\Exment\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        // Exment API page
        'adminapi' => [
            'admin.api-ipfilter',
            'adminapi.auth',
            'admin.morph',
            'admin.log',
            // 'throttle:60,1',
            'bindings',
        ],
        // Exment Plugin API
        'pluginapi' => [
            'pluginapi.auth',
        ],
        // Exment API not login page. (Ex. get token)
        'adminapi_anonymous' => [
            'admin.api-ipfilter',
            // 'throttle:60,1',
            'admin.morph',
            'admin.log',
            'bindings',
        ],
        // Extends web middleware If call exment's parts by user, please Add
        'exment_web' => [
            'admin.initialize',
            'admin.morph',
        ],
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->bootApp();
        $this->bootSetting();
        $this->bootDatabase();
        $this->bootSchedule();

        $this->publish();
        $this->load();

        $this->registerPolicies();

        $this->bootPassport();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        require_once(__DIR__.'/Services/Helpers.php');

        $this->mergeConfigFrom(
            __DIR__.'/../config/exment.php',
            'exment'
        );
        
        // register global middleware.
        $kernel = $this->app->make(Kernel::class);
        foreach ($this->middleware as $middleware) {
            $kernel->pushMiddleware($middleware);
        }

        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        ////// register middleware group.
        foreach ($this->getMiddlewareGroups() as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }

        // register database
        $this->app->resolving('db', function ($db, $app) {
            $db->extend('mariadb', function ($config, $name) use ($app) {
                return (new ExmentDatabase\Connectors\MariaDBConnectionFactory($app))->make($config, $name);
            });
        });

        // bind plugin for page
        $this->app->bind(PluginPublicBase::class, function ($app) {
            return Plugin::getPluginPageModel();
        });
        $this->app->bind(PluginApiBase::class, function ($app) {
            return Plugin::getPluginPageModel();
        });
        $this->app->bind(CustomTable::class, function ($app) {
            return CustomTable::findByEndpoint();
        });
        
        Passport::ignoreMigrations();
    }

    protected function publish()
    {
        $this->publishes([__DIR__.'/../config' => config_path()]);
        $this->publishes([__DIR__.'/../public' => public_path('')], 'public');
        $this->publishes([__DIR__.'/../resources/views/vendor' => resource_path('views/vendor')], 'views_vendor');
        $this->publishes([base_path('vendor/exceedone/laravel-admin/resources/assets') => public_path('vendor/laravel-admin')], 'laravel-admin-assets-exment');
        $this->publishes([base_path('vendor/exceedone/laravel-admin/resources/lang') => resource_path('lang')], 'laravel-admin-lang-exment');
        $this->publishes([__DIR__.'/../resources/lang_vendor' => resource_path('lang')], 'lang_vendor');
    }

    protected function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'exment');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'exment');

        // load plugins
        if (!canConnection() || !hasTable(SystemTableName::PLUGIN)) {
            return;
        }

        $pluginPages = Plugin::getByPluginTypes(PluginType::PLUGIN_TYPE_PLUGIN_USE_VIEW(), true);
        foreach ($pluginPages as $pluginPage) {
            if (!is_null($items = $pluginPage->_getLoadView())) {
                $this->loadViewsFrom($items[0], $items[1]);
            }
        }
    }

    protected function bootApp()
    {
        foreach ($this->serviceProviders as $serviceProvider) {
            $this->app->register($serviceProvider);
        }
        
        $this->commands($this->commands);

        if (!$this->app->runningInConsole()) {
            $this->commands(\Laravel\Passport\Console\KeysCommand::class);
        }

        if (config('admin.https') || config('admin.secure')) {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
        if (boolval(config('admin.use_app_url', false))) {
            \URL::forceRootUrl(config('app.url'));
        }
    }

    protected function bootSchedule()
    {
        // set hourly event
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('exment:schedule')->hourly();
                
            // set cron event
            try {
                if (hasTable(SystemTableName::PLUGIN)) {
                    $plugins = Plugin::getCronBatches();
                    foreach ($plugins as $plugin) {
                        $cronSchedule = $this->app->make(Schedule::class);
                        $cronSchedule->command("exment:batch {$plugin->id}")->cron(array_get($plugin, 'options.batch_cron'));
                    }
                }
            } catch (\Exception $ex) {
            }
        });
    }

    protected function bootPassport()
    {
        // adding rule for laravel-passport
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = Uuid::generate()->string;
        });
        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });
        Passport::tokensCan(ApiScope::transArray('api.scopes'));
    }


    protected function bootSetting()
    {
        Initialize::requireBootstrap();

        // Extend --------------------------------------------------
        Auth::provider('exment-auth', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new Providers\LoginUserProvider($app['hash'], \Exceedone\Exment\Model\LoginUser::class);
        });
        
        \Validator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
            return new ExmentCustomValidator($translator, $data, $rules, $messages, $customAttributes);
        });

        foreach (['exment', 'backup', 'plugin', 'template'] as $driverKey) {
            Storage::extend("exment-driver-$driverKey", function ($app, $config) use ($driverKey) {
                return Driver::getExmentDriver($app, $config, $driverKey);
            });
        }

        Initialize::initializeConfig(false);
        
        if (method_exists("\Encore\Admin\Admin", "registered")) {
            Admin::registered(function () {
                Initialize::registeredLaravelAdmin();
            });
        } else {
            Admin::booting(function () {
                Initialize::registeredLaravelAdmin();
            });
        }
    }
    
    /**
     * Boot database for extend
     *
     * @return void
     */
    protected function bootDatabase()
    {
        Connection::resolverFor('mysql', function (...$parameters) {
            return new ExmentDatabase\MySqlConnection(...$parameters);
        });
        Connection::resolverFor('mariadb', function (...$parameters) {
            return new ExmentDatabase\MariaDBConnection(...$parameters);
        });
        Connection::resolverFor('sqlsrv', function (...$parameters) {
            return new ExmentDatabase\SqlServerConnection(...$parameters);
        });
        Connection::resolverFor('pgsql', function (...$parameters) {
            return new ExmentDatabase\PostgresConnection(...$parameters);
        });
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
    
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return $this->policies;
    }

    /**
     * Get Middleware Groups
     * *Merge adminapioauth, adminwebapi
     *
     * @return void
     */
    public function getMiddlewareGroups()
    {
        ////// register middleware group.
        $middlewareGroups = $this->middlewareGroups;
        // append oauth login
        $middleware = $middlewareGroups['admin'];
        foreach ($middleware as &$m) {
            if ($m == 'admin.web-ipfilter') {
                $m = 'admin.api-ipfilter';
            }
        }
        $middlewareGroups['adminapioauth'] = $middleware;

        // append adminwebapi
        $middleware = $middlewareGroups['adminapi'];
        foreach ($middleware as &$m) {
            if ($m == 'admin.api-ipfilter') {
                $m = 'admin.web-ipfilter';
            }
        }
        $middlewareGroups['adminwebapi'] = $middleware;

        return $middlewareGroups;
    }
}
