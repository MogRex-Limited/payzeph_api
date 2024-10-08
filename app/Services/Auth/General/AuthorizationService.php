<?php

namespace App\Services\Auth\General;

use App\Exceptions\General\InvalidRequestException;
use App\Models\ApiCredential;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthorizationService
{
    public static function hasPermissionTo(array $permissions, User $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user->hasAnyPermission($permissions)) {
            abort(403);
        }
    }

    public static function hasRole(array $roles, User $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user->hasRole($roles)) {
            abort(403);
        }
    }


    public static function checkForRoles(array $roles, User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user->hasRole($roles);
    }

    public static function syncSudoRoles()
    {
        if (!empty($sudo = sudo())) {
            $role = Role::firstOrCreate(["name" => "Sudo"]);
            $permissions = Permission::where("guard_name", "web")->pluck("name")->toArray();
            $role->syncPermissions($permissions);
            $sudo->syncRoles([$role]);
        }
    }

    public static function getRoleByName($name)
    {
        $role = Role::firstOrCreate(["name" => $name]);
        return $role;
    }

    public static function verifyApiKey($request)
    {
        $headers = $request->header();
        if (empty($headers["api-key"] ?? null)) {
            throw new InvalidRequestException("No API key provided");
        }

        $api_key = $headers["api-key"][0];

        $env  = env("APP_ENV");
        if ($env == "local" || ($env == "staging")) {
            $key = env("TEST_API_KEY");
        } else {
            $key = env("LIVE_API_KEY");
        }

        if ($key != $api_key) {
            throw new InvalidRequestException("Invalid API Key.");
        }
    }

    public static function verifyExternalApiKey($request)
    {
        $headers = $request->header();

        if (empty($headers["authorization"][0] ?? null)) {
            throw new InvalidRequestException("Add Authorization to Headers");
        }

        if (!empty($authorization = $headers["authorization"][0] ?? null)) {
            $api_key = explode(" ", $authorization)[1] ?? null;
        }

        if (empty($api_key)) {
            throw new InvalidRequestException("No API Key Provided");
        }

        $api_setting = ApiCredential::where("private_key", $api_key)->first();

        if (empty($api_setting)) {
            throw new InvalidRequestException("Invalid API Key");
        }

        $user = $api_setting->user;
        $token = SanctumService::createAccountToken($user);

        if (!empty($token)) {
            $authorization = "Bearer $token";
            $request->headers->set('Authorization', $authorization);
        }
    }
}
