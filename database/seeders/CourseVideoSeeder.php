<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('videos')->insert([
            'class_id' => 1,
            'path' => 'courses/1/1/1/class/37_seg_video.mp4',
            'filename' => '37_seg_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 2,
            'path' => 'courses/1/1/2/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 3,
            'path' => 'courses/1/1/3/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 4,
            'path' => 'courses/1/10/4/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 5,
            'path' => 'courses/1/9/5/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 6,
            'path' => 'courses/1/9/6/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 7,
            'path' => 'courses/1/8/7/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 8,
            'path' => 'courses/1/8/8/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 9,
            'path' => 'courses/1/8/9/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 10,
            'path' => 'courses/1/7/10/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 11,
            'path' => 'courses/1/7/11/class/2_min_video.mp4',
            'filename' => '37_seg_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 12,
            'path' => 'courses/1/6/12/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
        DB::table('videos')->insert([
            'class_id' => 13,
            'path' => 'courses/1/6/13/class/2_min_video.mp4',
            'filename' => '2_min_video.mp4',
            'videoable_type' => 'test',
            'videoable_id' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'saved_time' => '0',
        ]);
    }
}
