<?php

namespace App\Nova;

use Exception;
use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Textarea;;

class Server extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Server::class;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Autopilot';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'provider', 'region', 'provider_server_id'
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return ($this->provider ? $this->provider : '') .
            ($this->region ? ', ' . $this->region : '') .
            ($this->size ? ', ' . $this->size : '');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules(\App\Server::$validationRules['name']),

            Select::make('Provider', 'provider')
                ->options([
                    'custom' => 'Custom',
                    'upcloud' => 'UpCloud'
                ])
                ->hideWhenUpdating()
                ->rules(\App\Server::$validationRules['provider']),

            Select::make('Region', 'region')
                ->options(function () {
                    $options = [];

                    try {
                        $upcloud = new \App\Server([
                            'provider' => 'upcloud'
                        ]);

                        foreach ($upcloud->withProvider()->regions() as $region) {
                            $options[$region] = ["label" => $region, "group" => "UpCloud"];
                        }
                    } catch (Exception $e) {
                        //
                    }

                    return $options;
                })
                ->hideWhenUpdating()
                ->rules(\App\Server::$validationRules['region']),

            Select::make('Size', 'size')
                ->options(function () {
                    $options = [];

                    try {
                        $upcloud = new \App\Server([
                            'provider' => 'upcloud'
                        ]);

                        foreach ($upcloud->withProvider()->sizes() as $size) {
                            $options[$size] = ["label" => $size, "group" => "UpCloud"];
                        }
                    } catch (Exception $e) {
                        //
                    }

                    return $options;
                })
                ->hideWhenUpdating()
                ->rules(\App\Server::$validationRules['size']),

            Text::make('Provider ID', 'provider_server_id')
                ->hideFromIndex()
                ->hideWhenCreating(),

            Select::make('Type')
                ->options([
                    'shared' => 'Shared',
                    'dedicated' => 'Dedicated',
                    'unmanaged' => 'Unmanaged'
                ])
                ->displayUsingLabels()
                ->rules(\App\Server::$validationRules['type']),

            Text::make('User')
                ->hideFromIndex()
                ->rules(\App\Server::$validationRules['user']),

            Number::make('Port')
                ->hideFromIndex()
                ->rules(\App\Server::$validationRules['port']),

            Text::make('Address')
                ->rules(\App\Server::$validationRules['address']),

            Text::make('IPv6 Address')
                ->hideFromIndex()
                ->rules(\App\Server::$validationRules['ipv6_address']),

            Text::make('Private Address')
                ->hideFromIndex()
                ->rules(\App\Server::$validationRules['private_address']),

            Textarea::make('Admin Password')
                ->rows(1)
                ->sortable()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['admin_password']),

            Textarea::make('MySQL Password')
                ->rows(1)
                ->sortable()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->readonly(function ($request) {
                    return $request->isUpdateOrUpdateAttachedRequest();
                })
                ->rules(\App\Server::$validationRules['mysql_password']),

            Textarea::make('Public Key', 'public_key')
                ->exceptOnForms()
                ->hideFromIndex(),

            \App\Server::getNovaStatusField($this),

            new Panel('General Configuration', $this->generalConfigurationFields()),

            new Panel('PHP Configuration', $this->phpConfigurationFields()),

            new Panel('SMTP Relay Configuration', $this->smtpRelayConfigurationFields()),

            HasMany::make('Users', 'sysusers', Sysuser::class),
            HasMany::make('Keys', 'keys', Key::class),
            HasMany::make('Tasks', 'tasks', Task::class),
        ];
    }

    /**
     * Get the general configuration fields for the resource.
     *
     * @return array
     */
    protected function generalConfigurationFields()
    {
        return [
            Select::make('Timezone', 'timezone')
                ->options(\App\Server::getTimezones())
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['timezone']),

            Text::make('Administrator Email', 'admin_email')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['admin_email']),

            Text::make('Health Email', 'health_email')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['health_email']),
        ];
    }

    /**
     * Get the php configuration fields for the resource.
     *
     * @return array
     */
    protected function phpConfigurationFields()
    {
        return [
            Number::make('Max Post Size', 'php_post_max_size')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['php_post_max_size']),

            Number::make('Max Upload Filesize', 'php_upload_max_filesize')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['php_upload_max_filesize']),

            Number::make('Memory Limit', 'php_memory_limit')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['php_memory_limit'])
        ];
    }

    /**
     * Get the smtp relay configuration fields for the resource.
     *
     * @return array
     */
    protected function smtpRelayConfigurationFields()
    {
        return [
            Text::make('Relay Host', 'smtp_relay_host')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['smtp_relay_host']),

            Text::make('Relay Domain', 'smtp_relay_domain')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['smtp_relay_domain']),

            Text::make('Relay User', 'smtp_relay_user')
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['smtp_relay_user']),

            Text::make('Relay Password', 'smtp_relay_password')
                ->onlyOnForms()
                ->hideWhenCreating()
                ->rules(\App\Server::$validationRules['smtp_relay_password'])
        ];
    }

    /**
     * Returns the menu position.
     *
     * @return int
     */
    public static function menuPosition()
    {
        return 10;
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new JobAction)
                ->setName('Test Server')
                ->setResourceName('server')
                ->setFunctionName('test')
                ->confirmButtonText('Run Tests')
                ->confirmText('Are you sure you want to test the selected server(s)?')
                ->setSuccessMessage('Autopilot will test your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Stop Server')
                ->setResourceName('server')
                ->setFunctionName('stop')
                ->confirmButtonText('Stop')
                ->confirmText('Are you sure you want to stop the selected server(s)?')
                ->setSuccessMessage('Autopilot will stop your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Start Server')
                ->setResourceName('server')
                ->setFunctionName('start')
                ->confirmButtonText('Start')
                ->confirmText('Are you sure you want to start the selected server(s)?')
                ->setSuccessMessage('Autopilot will start your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Provision Server')
                ->setResourceName('server')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected server(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Renew Certificates')
                ->setResourceName('server')
                ->setFunctionName('certRenew')
                ->confirmButtonText('Renew Certificates')
                ->confirmText('Are you sure you want to renew certificates on the selected server(s)?')
                ->setSuccessMessage('Autopilot will renew certificates on your {{resourceName}} in a few seconds.'),
        ];
    }
}
