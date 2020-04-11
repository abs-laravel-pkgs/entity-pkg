<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EntityU2qw extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('entity_types', function (Blueprint $table) {
			$table->unsignedInteger('created_by_id')->nullable()->after('name');
			$table->unsignedInteger('updated_by_id')->nullable()->after('created_by_id');
			$table->unsignedInteger('deleted_by_id')->nullable()->after('updated_by_id');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('entity_types', function (Blueprint $table) {
			$table->dropForeign('entity_types_company_id_foreign');
			$table->dropForeign('entity_types_created_by_id_foreign');
			$table->dropForeign('entity_types_updated_by_id_foreign');
			$table->dropForeign('entity_types_deleted_by_id_foreign');

			$table->dropColumn('company_id');
			$table->dropColumn('created_by_id');
			$table->dropColumn('updated_by_id');
			$table->dropColumn('deleted_by_id');
			$table->dropColumn('created_at');
			$table->dropColumn('updated_at');
			$table->dropColumn('softDeletes');
		});
	}
}
