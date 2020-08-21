<?php

namespace App;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class SecureShellKey
{
    /**
     * Create a new SSH key for a new server.
     *
     * @return object
     */
    public static function forServer()
    {
        return app()->environment('local') || app()->environment('testing')
            ? static::forTesting()
            : static::make();
    }

    /**
     * Create a new SSH key for a new system user.
     *
     * @return object
     */
    public static function forSysuser(Sysuser $sysuser)
    {
        return app()->environment('local') || app()->environment('testing')
            ? static::forTesting()
            : static::make("{$sysuser->name}@{$sysuser->server->name}");
    }

    /**
     * Create a new SSH key for testing.
     *
     * @return object
     */
    protected static function forTesting()
    {
        return (object) [
            'publicKey' => trim(file_get_contents(base_path('docker/test/ssh/test_key.pub'))),
            'privateKey' => trim(file_get_contents(base_path('docker/test/ssh/test_key'))),
        ];
    }

    /**
     * Create a new SSH key.
     *
     * @return object
     */
    public static function make($email = null)
    {
        if (!$email) {
            $email = "worker@" . config('autopilot.root_domain');
        }

        $name = Str::random(20);

        (new Process(
            ["ssh-keygen", "-C", "\"$email\"", "-f", $name, "-t", "rsa", "-m", "PEM", "-b", "4096"],
            storage_path('app')
        ))->mustRun();

        [$publicKey, $privateKey] = [
            trim(file_get_contents(storage_path('app/' . $name . '.pub'))),
            trim(file_get_contents(storage_path('app/' . $name))),
        ];

        @unlink(storage_path('app/' . $name . '.pub'));
        @unlink(storage_path('app/' . $name));

        return (object) compact('publicKey', 'privateKey');
    }

    /**
     * Store a secure shell key for the given user.
     *
     * @param  Server  $server
     * @return string
     */
    public static function storeFor(Server $server)
    {
        return tap(storage_path('app/keys/' . md5("autopilot-key-" . $server->id)), function ($path) use ($server) {
            static::ensureKeyDirectoryExists();
            static::ensureFileExists($path, trim($server->private_key), 0600);
        });
    }

    /**
     * Ensure the SSH key directory exists.
     *
     * @return void
     */
    protected static function ensureKeyDirectoryExists()
    {
        if (!is_dir(storage_path('app/keys'))) {
            mkdir(storage_path('app/keys'), 0755, true);
        }
    }

    /**
     * Ensure the given file exists.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  string  $chmod
     * @return string
     */
    protected static function ensureFileExists($path, $contents, $chmod)
    {
        file_put_contents($path, $contents);

        chmod($path, $chmod);
    }
}
