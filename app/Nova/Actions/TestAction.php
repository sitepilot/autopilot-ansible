<?php

namespace App\Nova\Actions;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;

class TestAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Test';

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
            $model->test();
        }

        if (count($models) > 1) {
            $label = Str::plural($this->label);
            return Action::message("Autopilot will start testing your {$label} in a few seconds.");
        } else {
            return Action::message("Autopilot will start testing your {$this->label} in a few seconds.");
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
