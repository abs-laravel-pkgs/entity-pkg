<?php

Route::group(['namespace' => 'Abs\EntityPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'entity-pkg'], function () {
	//FAQs
	Route::get('/entities/get-list', 'EntityController@getEntityList')->name('getEntityList');
	Route::get('/entity/get-form-data', 'EntityController@getEntityFormData')->name('getEntityFormData');
	Route::post('/entity/save', 'EntityController@saveEntity')->name('saveEntity');
	Route::get('/entity/delete', 'EntityController@deleteEntity')->name('deleteEntity');
});

Route::group(['namespace' => 'Abs\EntityPkg', 'middleware' => ['web'], 'prefix' => 'entity-pkg'], function () {
	//FAQs
	Route::get('/entities/get', 'EntityController@getEntities')->name('getEntities');
});
