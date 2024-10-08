<?php

namespace App\Services\Auth\Admin;

use App\Models\Admin;
use App\Services\Admin\AdminService;
use App\Services\Auth\Admin\AdminVerifyService;
use App\Services\Notification\AppMailerService;

class AdminRegistrationService
{

    public $admin_service;
    public $admin_verify_service;

    public function __construct()
    {
        $this->admin_service = new AdminService;
        $this->admin_verify_service = new AdminVerifyService;
    }

    public function create(array $data): Admin
    {
        $admin = $this->admin_service->create($data);
        // $this->admin_verify_service->sendPin($admin);
        return $admin;
    }

    public  function postRegisterActions(Admin $admin)
    {
        // $this->sendWelcomeMessage($admin);
    }

    private function sendWelcomeMessage(Admin $admin)
    {
        AppMailerService::send([
            "data" => [
                'admin' => $admin,
            ],
            "to" => $admin->email,
            "template" => "emails.admin.welcome",
            "subject" => "Welcome to Mentra",
        ]);
    }
}
