<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Blog::factory(10)->create()->each(function ($blog) {
            $blog->posts()->saveMany(\App\Models\Post::factory(10)->make())->each(function ($post) {
                $post->comments()->saveMany(\App\Models\Comment::factory(10)->make())->each(function ($comment) {
                });
            });
        });
    }
}
