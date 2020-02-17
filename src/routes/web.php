<?php

Route::group(['namespace' => 'Abs\EntityPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'entity-pkg'], function () {
	//Entity Type
	Route::get('/entity-type/get-list', 'EntityTypeController@getEntityTypeList')->name('getEntityTypeList');
	Route::get('/entity-type/get-form-data', 'EntityTypeController@getEntityTypeFormData')->name('getEntityTypeFormData');
	Route::post('/entity-type/save', 'EntityTypeController@saveEntityType')->name('saveEntityType');
	Route::get('/entity-type/delete', 'EntityTypeController@deleteEntityType')->name('deleteEntityType');

	//Entity
	Route::get('/entities/get-list', 'EntityController@getEntityList')->name('getEntityList');
	Route::get('/entity/get-form-data', 'EntityController@getEntityFormData')->name('getEntityFormData');
	Route::get('/entity/get-entity-type-data/', 'EntityController@getEntityTypeData')->name('getEntityTypeData');
	Route::post('/entity/save', 'EntityController@saveEntity')->name('saveEntity');
	Route::get('/entity/delete', 'EntityController@deleteEntity')->name('deleteEntity');

});

Route::group(['namespace' => 'Abs\EntityPkg', 'middleware' => ['web'], 'prefix' => 'entity-pkg'], function () {
	//FAQs
	Route::get('/entities/get', 'EntityController@getEntities')->name('getEntities');
});
