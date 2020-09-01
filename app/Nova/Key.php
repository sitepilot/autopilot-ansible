<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;

class Key extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Key::class;

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

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
        'id', 'name', 'key'
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return (isset($this->sysuser->name) ? $this->sysuser->name :  $this->server->name);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $fields = [
            //
        ];

        return array_merge($fields, [
            BelongsTo::make('Server')->hideWhenUpdating(),

            Text::make('Name', 'name')
                ->sortable()
                ->rules(\App\Key::$validationRules['name']),

            Textarea::make('Key', 'key')
                ->hideWhenUpdating()
                ->rules(\App\Key::$validationRules['key']),

            \App\Key::getNovaStatusField($this),

            HasMany::make('Tasks', 'tasks', Task::class)
        ]);
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
                ->showOnTableRow()
                ->setName('Provision Key')
                ->setResourceName('key')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected key(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.')
        ];
    }
}
