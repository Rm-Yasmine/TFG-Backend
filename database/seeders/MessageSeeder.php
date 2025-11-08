<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $users = User::all();
        $projects = Project::all();

        foreach ($projects as $project) {
            Message::factory(4)->create([
                'project_id' => $project->id,
                'sender_id' => $users->random()->id,
                'receiver_id' => $users->random()->id,
            ]);
        }
    }
}
