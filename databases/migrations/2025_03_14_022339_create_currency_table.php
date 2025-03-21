<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('name')->comment('币种名称（支持多语言）');
            $table->string('symbol')->comment('币种代码');
            $table->string('icon')->nullable()->comment('币种图标');
            $table->string('sort')->default('0')->comment('排序');
            $table->integer('decimals')->default(2)->comment('精度');
            $table->timestamps();
            $table->unique('symbol', 'symbol_unique');
            $table->comment('交易对表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency');
    }
};
