<?php

namespace App\Nova;

use App\Nova\Actions\GenerateKeypairAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;

class Sysuser extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Sysuser::class;

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
        'id', 'name', 'full_name', 'email'
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->server->name;
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
                ->hideWhenUpdating()
                ->rules(\App\Sysuser::$validationRules['name']),

            Text::make('Full Name', 'full_name')
                ->sortable()
                ->rules(\App\Sysuser::$validationRules['full_name']),

            Text::make('Email', 'email')
                ->sortable()
                ->rules(\App\Sysuser::$validationRules['email']),

            Boolean::make('Isolated')
                ->rules(\App\Sysuser::$validationRules['isolated']),

            BelongsTo::make('Server')
                ->searchable()
                ->withoutTrashed()
                ->nullable()
                ->hideWhenUpdating()
                ->help('A random server will be assigned when this field is left empty.'),

            Textarea::make('Password')
                ->rows(1)
                ->sortable()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Sysuser::$validationRules['password']),

            Textarea::make('MySQL Password')
                ->rows(1)
                ->sortable()
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules(\App\Sysuser::$validationRules['mysql_password']),

            Textarea::make('Public Key', 'public_key')
                ->exceptOnForms()
                ->hideFromIndex(),

            \App\Sysuser::getNovaStatusField($this),

            HasMany::make('Sites'),
            HasMany::make('Databases'),
            HasMany::make('Keys', 'keys', SysuserKey::class),
            HasMany::make('Tasks', 'tasks', Task::class)
        ];
    }

    /**
     * Returns the menu position.
     *
     * @return int
     */
    public static function menuPosition()
    {
        return 20;
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
                ->setName('Test User')
                ->setResourceName('user')
                ->setFunctionName('test')
                ->confirmButtonText('Run Tests')
                ->confirmText('Are you sure you want to test the selected user(s)?')
                ->setSuccessMessage('Autopilot will test your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Provision User')
                ->setResourceName('user')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected user(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.'),
            (new GenerateKeypairAction)
        ];
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'System Users';
    }
}
