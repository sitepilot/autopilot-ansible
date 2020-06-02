<?php

namespace App;

class AnsibleInventory
{
    /**
     * Store a inventory file for a given server.
     *
     * @param  Server  $server
     * @return string
     */
    public static function storeFor(Server $server)
    {
        return tap(storage_path('app/inventories/' . md5("autopilot-inventory-" . $server->id)), function ($path) use ($server) {
            static::ensureDirectoryExists();
            static::ensureFileExists($path, view('ansible.inventory', [
                'server' => $server
            ])->render(), 0600);
        });
    }

    /**
     * Ensure the inventories directory exists.
     *
     * @return void
     */
    protected static function ensureDirectoryExists()
    {
        if (!is_dir(storage_path('app/inventories'))) {
            mkdir(storage_path('app/inventories'), 0755, true);
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
