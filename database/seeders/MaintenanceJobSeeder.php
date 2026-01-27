<?php

namespace Database\Seeders;

use App\Models\MaintenanceJob;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceJobSeeder extends Seeder
{
    public function run(): void
    {
        // Get users with 'staff_maintenance' role (operators)
        $operators = User::role('staff_maintenance')->pluck('id')->toArray();
        $admin = User::role('admin')->first();
        
        if (empty($operators)) {
            // Create a default operator if none exists
            $operator = User::create([
                'name' => 'Default Operator',
                'email' => 'operator@warehouse.com',
                'password' => bcrypt('password123'),
            ]);
            $operator->assignRole('staff_maintenance');
            $operators = [$operator->id];
        }

        $machines = Machine::all();

        $jobs = [
            [
                'title' => 'Monthly Preventive Maintenance - Forklift FLT-001',
                'description' => 'Regular monthly maintenance including oil change, filter replacement, and safety inspection',
                'machine_code' => 'FLT-001',
                'type' => 'preventive',
                'priority' => 'medium',
                'status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(2),
                'estimated_duration' => 120,
            ],
            [
                'title' => 'Urgent Repair - Hydraulic Leak on Forklift FLT-003',
                'description' => 'Hydraulic fluid leak detected in lifting mechanism. Immediate repair required.',
                'machine_code' => 'FLT-003',
                'type' => 'breakdown',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'scheduled_date' => Carbon::now(),
                'started_at' => Carbon::now()->subHours(2),
                'estimated_duration' => 180,
            ],
            [
                'title' => 'Belt Replacement - Conveyor CNV-001',
                'description' => 'Scheduled belt replacement for main conveyor line. Belt showing signs of wear.',
                'machine_code' => 'CNV-001',
                'type' => 'corrective',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(5),
                'estimated_duration' => 240,
            ],
            [
                'title' => 'Quarterly HVAC Maintenance',
                'description' => 'Quarterly maintenance for central AC unit including filter replacement and coil cleaning',
                'machine_code' => 'HVC-001',
                'type' => 'preventive',
                'priority' => 'low',
                'status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(10),
                'estimated_duration' => 90,
            ],
            [
                'title' => 'Battery Check - Electric Pallet Jack',
                'description' => 'Battery performance check and water level inspection',
                'machine_code' => 'PLJ-001',
                'type' => 'preventive',
                'priority' => 'low',
                'status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(3),
                'started_at' => Carbon::now()->subDays(3)->setHour(9),
                'completed_at' => Carbon::now()->subDays(3)->setHour(10),
                'estimated_duration' => 60,
                'actual_duration' => 55,
            ],
            [
                'title' => 'Safety Inspection - Scissor Lift',
                'description' => 'Annual safety inspection and certification',
                'machine_code' => 'SCL-001',
                'type' => 'preventive',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(7),
                'estimated_duration' => 90,
            ],
            [
                'title' => 'Refrigeration System Check - Cold Storage',
                'description' => 'Temperature calibration and refrigerant level check',
                'machine_code' => 'HVC-002',
                'type' => 'preventive',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(1),
                'estimated_duration' => 120,
            ],
            [
                'title' => 'Fire Alarm System Test',
                'description' => 'Monthly fire alarm system test and battery check',
                'machine_code' => 'FIR-001',
                'type' => 'preventive',
                'priority' => 'medium',
                'status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(5),
                'started_at' => Carbon::now()->subDays(5)->setHour(14),
                'completed_at' => Carbon::now()->subDays(5)->setHour(15),
                'estimated_duration' => 45,
                'actual_duration' => 50,
            ],
        ];

        foreach ($jobs as $jobData) {
            $machine = Machine::where('code', $jobData['machine_code'])->first();
            
            if ($machine) {
                $job = MaintenanceJob::create([
                    'title' => $jobData['title'],
                    'description' => $jobData['description'],
                    'machine_id' => $machine->id,
                    'assigned_to' => $operators[array_rand($operators)],
                    'created_by' => $admin->id ?? 1,
                    'type' => $jobData['type'],
                    'priority' => $jobData['priority'],
                    'status' => $jobData['status'],
                    'scheduled_date' => $jobData['scheduled_date'],
                    'started_at' => $jobData['started_at'] ?? null,
                    'completed_at' => $jobData['completed_at'] ?? null,
                    'estimated_duration' => $jobData['estimated_duration'],
                    'actual_duration' => $jobData['actual_duration'] ?? null,
                ]);
            }
        }
    }
}