<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;

class Domain extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Domain::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Autopilot';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->site->name;
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
                ->rules(\App\Domain::$validationRules['name']),

            BelongsTo::make('Site')
                ->searchable()
                ->withoutTrashed(),

            \App\Domain::getNovaStatusField($this),

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
        return 50;
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
                ->exceptOnIndex()
                ->showOnTableRow()
                ->setName('Provision Domain')
                ->setResourceName('domain')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected domain(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.')
                ->canRunWhenNotBusy($this),
        ];
    }
}
