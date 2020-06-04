<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Nova\Actions\JobAction;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;

class Site extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Site::class;

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
        'id', 'name'
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->sysuser->name;
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
                ->rules(\App\Site::$validationRules['name']),

            BelongsTo::make('System User', 'sysuser', Sysuser::class)
                ->searchable()
                ->hideWhenUpdating()
                ->showCreateRelationButton()
                ->withoutTrashed()
                ->nullable()
                ->help('A new systemuser will be created when this field is left empty.'),

            Select::make('PHP Version')
                ->options([
                    74 => '7.4',
                    73 => '7.3'
                ])
                ->displayUsingLabels()
                ->rules(\App\Site::$validationRules['php_version']),

            Boolean::make('Certificate', 'certficate')
                ->exceptOnForms(),

            Text::make('Domain', function () {
                return "<a href=\"https://{$this->domain}\" target=\"_blank\" class=\"no-underline dim text-primary font-bold\">{$this->domain}</a>";
            })
                ->asHtml()
                ->exceptOnForms(),

            Text::make('Aliases', function () {
                if(count($this->domains) < 1) {
                    return '—';
                }

                $domains = '';
                $count = $more = 0;

                foreach ($this->domains as $domain) {
                    if ($count < 2) {
                        $domains .= (!empty($domains) ? ', ' : '') . "<a href=\"https://{$domain->name}\" target=\"_blank\" class=\"no-underline dim text-primary font-bold\">{$domain->name}</a>";
                    } else {
                        $more++;
                    }
                    $count++;
                }

                if ($more) {
                    $domains .= ", +$more";
                }

                return $domains;
            })
                ->asHtml()
                ->onlyOnIndex(),

            \App\Site::getNovaStatusField($this),

            HasMany::make('Domain Aliases', 'domains', Domain::class),
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
        return 30;
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
                ->setName('Provision Site')
                ->setResourceName('site')
                ->setFunctionName('provision')
                ->confirmButtonText('Provision')
                ->confirmText('Are you sure you want to provision the selected site(s)?')
                ->setSuccessMessage('Autopilot will provision your {{resourceName}} in a few seconds.'),
            (new JobAction)
                ->setName('Request Certificate')
                ->setResourceName('site')
                ->setFunctionName('certRequest')
                ->confirmButtonText('Request Certificate')
                ->confirmText('Are you sure you want to request certificates for the selected site(s)?')
                ->setSuccessMessage('Autopilot will request a certificate for your {{resourceName}} in a few seconds.')
        ];
    }
}
