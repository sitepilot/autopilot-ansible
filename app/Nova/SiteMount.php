<?php

namespace App\Nova;

use Illuminate\Http\Request;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;

class SiteMount extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\SiteMount::class;

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
    public static $title = 'id';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('System User', 'sysuser', Sysuser::class)
                ->searchable()
                ->hideWhenUpdating()
                ->withoutTrashed()
                ->rules(\App\SiteMount::getValidationRules('sysuser_id')),
            BelongsTo::make('Site', 'site', Site::class)
                ->searchable()
                ->hideWhenUpdating()
                ->withoutTrashed()
                ->rules(\App\SiteMount::getValidationRules('site_id')),

            \App\SiteMount::getNovaStatusField($this),

            HasMany::make('Tasks', 'tasks', Task::class)
        ];
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
                ->setName('Provision Mount')
                ->setResourceName('mount')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected site mount(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.')
        ];
    }
}
