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
            $posts = \App\Models\Post::factory(10)->make();
            $blog->posts()->saveMany($posts);

            foreach ($posts as $post) {
                $comments = \App\Models\Comment::factory(10)->make();
                $post->comments()->saveMany($comments);
            }
        });
    }
}
