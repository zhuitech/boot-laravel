<?php

use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = config('boot-laravel.setting.table_name');

        Schema::create($table, function(Blueprint $table) {
            $table->increments('id');
            $table->string('key', 100)->comment('键')->index();
            $table->text('value')->comment('值');
            $table->timestamps();
	        $table->comment = '系统配置表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = config('boot-laravel.setting.table_name');

        Schema::drop($table);
    }
}
