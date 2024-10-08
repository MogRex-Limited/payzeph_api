<?php

namespace App\Services\Auth\User;

use App\Models\User;
use App\Services\User\UserService;
use App\Services\Auth\User\UserVerifyService;
use App\Services\Notification\AppMailerService;

class UserRegistrationService
{

    public $user_service;
    public $user_verify_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->user_verify_service = new UserVerifyService;
    }

    public function create(array $data): User
    {
        $user = $this->user_service->create($data);
        $this->user_verify_service->sendPin($user);
        return $user;
    }

    public  function postRegisterActions(User $user)
    {
        // $this->sendWelcomeMessage($user);
    }

    private function sendWelcomeMessage(User $user)
    {
        AppMailerService::send([
            "data" => [
                'user' => $user,
            ],
            "to" => $user->email,
            "template" => "emails.user.welcome",
            "subject" => "Welcome to Mentra",
        ]);
    }
}
