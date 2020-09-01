<?php

namespace App\Rules;

use App\Site;
use App\Sysuser;
use Illuminate\Contracts\Validation\Rule;

class SiteMountRule implements Rule
{
    private $validatonMessage = 'The selected :attribute is invalid.';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $site = Site::find($value);
        $sysuser = Sysuser::find(request()->post('sysuser'));

        if (!$site || !$sysuser) {
            $this->validatonMessage = 'The selected :attribute is invalid.';
        } elseif ($sysuser->server->id != $site->server->id) {
            $this->validatonMessage = 'The selected :attribute is not on the same server as the user.';
        } elseif ($sysuser->id == $site->sysuser->id) {
            $this->validatonMessage = 'The selected :attribute already belongs to the user.';
        } elseif ($sysuser->siteMounts()->where('site_id', $site->id)->count()) {
            $this->validatonMessage = 'The selected :attribute is already mounted to the user.';
        } else {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->validatonMessage;
    }
}
