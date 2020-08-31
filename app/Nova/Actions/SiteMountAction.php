<?php

namespace App\Nova\Actions;

use App\Site;
use App\Nova\Actions\Action;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;

class SiteMountAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Mount To User';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Go';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            if ($fields->action == 'mount') {
                $model->mountToSysuser($fields->user);
                return Action::message("Autopilot will start mounting your site to '{$fields->user}' in a few seconds.");
            } elseif ($fields->action == 'unmount') {
                $model->unmountFromSysuser($fields->user);
                return Action::message("Autopilot will start unmounting your site from '{$fields->user}' in a few seconds.");
            }
        }

        return Action::danger("Unknown action.");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $mountUsers = ['sitepilot' => 'sitepilot (Administrator)'];

        if (isset(request()->resourceId)) {
            if ($site = Site::find(request()->resourceId)->first()) {
                foreach ($site->server->sysusers as $sysuser) {
                    if ($sysuser->id != $site->sysuser->id) $mountUsers[$sysuser->name] = $sysuser->name . ' (' . $sysuser->full_name . ')';
                }
            }
        }

        return [
            Select::make('Action')->options([
                'mount' => 'Mount',
                'unmount' => 'Unmount'
            ])->displayUsingLabels()
                ->rules(['required']),
            Select::make('User')->options(
                $mountUsers
            )->displayUsingLabels()
                ->rules(['required']),
        ];
    }
}
