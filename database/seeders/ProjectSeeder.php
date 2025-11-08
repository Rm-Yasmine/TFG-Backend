<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $users = User::all();

        Project::factory(5)->create()->each(function ($project) use ($users) {
            $project->members()->attach(
                $users->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
