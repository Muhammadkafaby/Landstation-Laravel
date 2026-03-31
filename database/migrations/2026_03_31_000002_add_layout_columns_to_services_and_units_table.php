<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('layout_mode')->nullable()->after('billing_type');
            $table->unsignedInteger('layout_canvas_width')->nullable()->after('layout_mode');
            $table->unsignedInteger('layout_canvas_height')->nullable()->after('layout_canvas_width');
            $table->string('layout_background_image_path')->nullable()->after('layout_canvas_height');
            $table->json('layout_meta_json')->nullable()->after('layout_background_image_path');
        });

        Schema::table('service_units', function (Blueprint $table) {
            $table->integer('layout_x')->nullable()->after('capacity');
            $table->integer('layout_y')->nullable()->after('layout_x');
            $table->unsignedInteger('layout_w')->nullable()->after('layout_y');
            $table->unsignedInteger('layout_h')->nullable()->after('layout_w');
            $table->integer('layout_rotation')->nullable()->after('layout_h');
            $table->integer('layout_z_index')->nullable()->after('layout_rotation');
            $table->json('layout_meta_json')->nullable()->after('layout_z_index');
        });
    }

    public function down(): void
    {
        Schema::table('service_units', function (Blueprint $table) {
            $table->dropColumn([
                'layout_x',
                'layout_y',
                'layout_w',
                'layout_h',
                'layout_rotation',
                'layout_z_index',
                'layout_meta_json',
            ]);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'layout_mode',
                'layout_canvas_width',
                'layout_canvas_height',
                'layout_background_image_path',
                'layout_meta_json',
            ]);
        });
    }
};
