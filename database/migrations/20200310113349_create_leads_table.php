<?php

use App\Support\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * PHPStorm helper
 * @property Builder schema
 * @property Blueprint table
 */
class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('address')->nullable();

            $table->integer('city_id')->nullable();
            $table->enum('company_status', ['company', 'liberal', 'selfEmployed']);

            $table->integer('sub_branch_id')->nullable();
            $table->boolean('terms_of_use')->default(0);

            $table->integer('abonnement_id')->nullable();

            $table->string('ice')->nullable();
            $table->string('ice_file_path')->nullable();

            $table->biginteger('verification_id')->nullable();

            $table->integer('customer_id')->nullable();

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
        $this->schema->drop('leads');
    }
}
