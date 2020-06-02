<?php

namespace App\Nova\Actions;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;

class StopAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Stop';

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
            $model->stop();
        }

        if (count($models) > 1) {
            $label = Str::plural($this->label);
            return Action::message("Autopilot will stop your {$label} in a few seconds.");
        } else {
            return Action::message("Autopilot will stop your {$this->label} in a few seconds.");
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Set the displayble name of the resource.
     *
     * @var string
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
