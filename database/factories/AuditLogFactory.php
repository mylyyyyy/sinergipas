<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_id' => null,
            'activity' => fake()->randomElement(['download_document', 'verify_document', 'update_settings']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'details' => fake()->sentence(),
            'is_system' => false,
        ];
    }
}
