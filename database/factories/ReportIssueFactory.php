<?php

namespace Database\Factories;

use App\Models\ReportIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportIssue>
 */
class ReportIssueFactory extends Factory
{
    protected $model = ReportIssue::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => 'open',
            'admin_note' => null,
        ];
    }
}
