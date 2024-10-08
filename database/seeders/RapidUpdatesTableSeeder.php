<?php

namespace Database\Seeders;

use App\Constants\Account\User\UserConstants;
use App\Constants\Availability\AvailabilityConstants;
use App\Constants\General\StatusConstants;
use App\Models\AvailabilityHours;
use App\Models\Therapist;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RapidUpdatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $therapists = Therapist::whereHas("user", function ($user) {
            $user->where("role", UserConstants::THERAPIST);
        })->get();

        foreach ($therapists as $key => $therapist) {
            foreach (AvailabilityConstants::DEFAULT["data"] as $week_day) {
                $week_day['therapist_id'] = $therapist->user_id;
                AvailabilityHours::updateOrCreate(
                    [
                        "day" => $week_day["day"],
                        'therapist_id' => $therapist->user_id
                    ],
                    $week_day
                );
            }
        }
    }
}
