<?php

namespace App\Nova\Actions;

use App\Nova\Resource;
use Laravel\Nova\Actions\Action as NovaAction;

class Action extends NovaAction
{
    /**
     * Set the name of the action.
     *
     * @var string
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Action is only allowed to run when resource status is set to "ready".
     *
     * @param Resource $resource
     * @return $this
     */
    public function canRunWhenReady(Resource $resource)
    {
        $this->canSee(function ($request) use ($resource) {
            return $resource->resource->exists && $resource->isReady()
                || optional($request->findModelQuery()->first())->isReady()
                || request()->isMethod('post') && request('action');
        });

        $this->canRun(fn ($request, $model) => $model->isReady());

        return $this;
    }

    /**
     * Action is only allowed to run when resource is not busy.
     *
     * @param Resource $resource
     * @return $this
     */
    public function canRunWhenNotBusy(Resource $resource)
    {
        $this->canSee(function ($request) use ($resource) {
            return $resource->resource->exists && $resource->isBusy() === false
                || optional($request->findModelQuery()->first())->isBusy() === false
                || request()->isMethod('post') && request('action');
        });

        $this->canRun(fn ($request, $model) => $model->isBusy() === false);

        return $this;
    }
}
