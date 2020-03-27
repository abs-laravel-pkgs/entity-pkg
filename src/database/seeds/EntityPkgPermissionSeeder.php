<?php
namespace Abs\EntityPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class EntityPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//FAQ
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'entities',
				'display_name' => 'Entities',
			],
			[
				'display_order' => 1,
				'parent' => 'entities',
				'name' => 'add-entity',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'entities',
				'name' => 'edit-entity',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'entities',
				'name' => 'delete-entity',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}