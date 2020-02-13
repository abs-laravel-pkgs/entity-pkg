<?php
Route::group(['namespace' => 'Abs\EntityPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'entity-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});