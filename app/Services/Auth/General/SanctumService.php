<?php

namespace App\Services\Auth\General;

use Illuminate\Database\Eloquent\Model;

class SanctumService
{
    const SESSION_KEY = "sanctum_auth_token";
    const WEB_TOKEN_NAME = "sanctum_web_auth_token";
    const API_TOKEN_NAME = "sanctum_api_auth_token";
    const WEB_META_NAME = "sanctum_auth_token";

    public static function createAccountToken(Model $model, $session = self::SESSION_KEY)
    {
        $model->tokens()->where('name', $session)->delete();
        $token = $model->createToken($session)->plainTextToken;
        return $token ?? null;
    }

    public static function findAccountToken($model)
    {
        $check_token = $model?->tokens()->where('name', self::SESSION_KEY)
            ->latest()->first();

        if (optional($check_token)->last_used_at !== null) {
            $token = $model?->createToken(self::SESSION_KEY)->plainTextToken;
        } else if (optional($check_token)->plainTextToken == null) {
            $token = $model?->createToken(self::SESSION_KEY)->plainTextToken;
        } else {
            $token =  $check_token->plainTextToken;
        }
        return $token;
    }

    public static function getRequestToken($request)
    {
        $token = $request->bearerToken();
        return $token;
    }

    public static function flushInvalidTokens($model, $request)
    {
        $currentToken = $request->model()->currentAccessToken();
        $model?->tokens()->where('id', '!=', $currentToken->id)->delete();
    }
}
