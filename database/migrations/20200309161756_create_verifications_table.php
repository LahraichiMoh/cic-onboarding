<?php

use App\Support\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * PHPStorm helper
 * @property Builder schema
 * @property Blueprint table
 */
class CreateVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('tblverifications', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('phone')->unique();
            $table->string('phone_code')->nullable();
            $table->timestamp('phone_created_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->string('email')->unique();
            $table->string('email_code')->nullable();
            $table->timestamp('email_created_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->biginteger('lead_id')->unsigned();
            $table->biginteger('customer_id')->unsigned();
            
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('tblverifications');
    }
}
