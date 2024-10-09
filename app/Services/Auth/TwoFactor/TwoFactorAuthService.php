<?php

namespace App\Services\Auth\TwoFactor;

use App\Exceptions\Auth\AuthException;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthService
{
    protected $google_2fa;
    protected $model;

    public function __construct($model = null)
    {
        $this->google_2fa = new Google2FA;
        $this->model = $model ?? auth()->user();
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function generateSecretKey()
    {
        DB::beginTransaction();
        try {
            $secret = $this->google_2fa->generateSecretKey();
            $this->model->update([
                "google2fa_secret" => $secret
            ]);

            $qr_code_url = $this->google_2fa->getQRCodeUrl(
                config('app.name'),
                $this->model->email,
                $secret
            );

            $qr_code = base64_encode(QrCode::format('png')->size(200)->generate($qr_code_url));

            DB::commit();
            return [
                'secret' => $secret,
                'qr_code_url' => $qr_code_url,
                'qr_code' => 'data:image/png;base64,' . $qr_code,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function enable2FA(array $data)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($data, [
                'otp' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            if (empty($this->model->google2fa_secret)) {
                throw new AuthException("Generate your keys to proceed.");
            }

            $valid = $this->google_2fa
                ->verifyKey($this->model->google2fa_secret, $data["otp"]);

            if (!$valid) {
                throw new AuthException("Invalid OTP");
            }

            $this->model->update([
                "two_factor_enabled" => true
            ]);

            DB::commit();
            return $this->model->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function disable2FA()
    {
        $this->model->update([
            "google2fa_secret" => null,
            "two_factor_enabled" => false,
        ]);

        return $this->model->refresh();
    }
}
