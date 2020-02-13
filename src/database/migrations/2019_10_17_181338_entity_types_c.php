<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EntityTypesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('entity_types', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('company_id')->nullable();
			$table->string('name', 191);

			$table->unique(["company_id", "name"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('entity_types');
	}
}
