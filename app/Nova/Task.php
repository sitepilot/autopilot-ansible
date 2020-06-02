<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;

class Task extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Task::class;

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
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Server'),

            Text::make('Name'),

            Text::make('User'),

            Text::make('Status', function () {
                return '<span>' . ucfirst($this->status) . '</span>';
            })
                ->asHtml(),

            Text::make('Exit Code', function () {
                return '<span style="' . ($this->exit_code > 0 ? 'color: red;' : '') . '">' . $this->exit_code . '</span>';
            })
                ->asHtml(),

            Text::make('Playbook')
                ->onlyOnDetail(),

            Textarea::make('Output'),

            Code::make('Variables', 'vars')->json(),

            DateTime::make('Start', 'created_at')
                ->exceptOnForms(),
            
            DateTime::make('End', 'updated_at')
                ->exceptOnForms(),
        ];
    }
    
    /**
     * Returns the menu position.
     *
     * @return int
     */
    public static function menuPosition()
    {
        return 90;
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
        return [];
    }
}
