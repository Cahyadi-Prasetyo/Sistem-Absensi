<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TentangSistemController extends Controller
{
    public function index()
    {
        $technologies = [
            [
                'name' => 'Laravel 12',
                'description' => 'Backend framework untuk API dan business logic',
                'icon' => 'laravel',
                'color' => 'red',
            ],
            [
                'name' => 'Alpine.js',
                'description' => 'Frontend framework untuk interaktivitas',
                'icon' => 'alpine',
                'color' => 'teal',
            ],
            [
                'name' => 'Laravel Reverb',
                'description' => 'WebSocket server untuk real-time communication',
                'icon' => 'websocket',
                'color' => 'purple',
            ],
            [
                'name' => 'Redis',
                'description' => 'Cache, Queue, dan Pub/Sub message broker',
                'icon' => 'redis',
                'color' => 'red',
            ],
            [
                'name' => 'MySQL',
                'description' => 'Relational database untuk data persistence',
                'icon' => 'database',
                'color' => 'blue',
            ],
            [
                'name' => 'Docker',
                'description' => 'Container orchestration untuk distributed system',
                'icon' => 'docker',
                'color' => 'blue',
            ],
            [
                'name' => 'Nginx',
                'description' => 'Load balancer untuk distribusi traffic',
                'icon' => 'nginx',
                'color' => 'green',
            ],
            [
                'name' => 'Tailwind CSS',
                'description' => 'Utility-first CSS framework untuk styling',
                'icon' => 'tailwind',
                'color' => 'cyan',
            ],
        ];

        return view('tentang-sistem', compact('technologies'));
    }
}
