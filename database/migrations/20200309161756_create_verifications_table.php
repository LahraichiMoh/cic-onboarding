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

            $table->string('phone')->nullable();
            $table->string('phone_code')->nullable();
            $table->timestamp('phone_created_at')->nullable();
            $table->timestamp('phone_code_generated_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->string('email')->nullable();
            $table->string('email_code')->nullable();
            $table->timestamp('email_created_at')->nullable();
            $table->timestamp('email_code_generated_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->biginteger('lead_id')->nullable();
            $table->biginteger('customer_id')->nullable();
            
            $table->timestamps();
            $table->engine = 'InnoDB';

            $table->unique('phone');
            $table->unique('email');
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
