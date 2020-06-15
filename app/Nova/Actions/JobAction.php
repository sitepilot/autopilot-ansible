<?php

namespace App\Nova\Actions;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;

class JobAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Provision';

    /**
     * The name of the resource.
     *
     * @var string
     */
    private $resourceName = 'resource';

    /**
     * The function to run on the model.
     *
     * @var string
     */
    private $functionName = 'provision';

    /**
     * The success message that will be shown after execution.
     *
     * @var string
     */
    private $successMessage = 'Autopilot will start provisioning your {resourceName} in a few seconds.';

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
            $function = $this->functionName;
            $model->$function();
        }

        if (count($models) > 1) {
            $resourceName = Str::plural($this->resourceName);
        } else {
            $resourceName = $this->resourceName;
        }
        
        return Action::message(str_replace('{{resourceName}}', $resourceName, $this->successMessage));
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
     * Set the name of the resource.
     *
     * @var string
     */
    public function setResourceName($name)
    {
        $this->resourceName = $name;

        return $this;
    }

    /**
     * Set the name of the function to run on the model.
     *
     * @var string
     */
    public function setFunctionName($name)
    {
        $this->functionName = $name;

        return $this;
    }

    /**
     * Set the success mesage for the action.
     *
     * @var string
     */
    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;

        return $this;
    }
}
