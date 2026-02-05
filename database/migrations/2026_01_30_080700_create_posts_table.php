<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->longText('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('slug');
            $table->index('published_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};