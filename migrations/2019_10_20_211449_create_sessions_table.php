<?php

use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 100)->unique();
            $table->unsignedBigInteger('user_id')->comment('用户ID')->nullable();
            $table->string('ip_address', 45)->comment('IP地址')->nullable();
            $table->text('user_agent')->comment('浏览器')->nullable();
            $table->text('payload')->comment('数据');
            $table->integer('last_activity')->comment('最后一次活动');
	        $table->comment = '会话表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
