<?php

/*
|--------------------------------------------------------------------------
| Bo\LogManager Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Bo\LogManager package.
|
*/

Route::group([
    'namespace'  => 'Bo\LogManager\App\Http\Controllers',
    'middleware' => ['web', config('bo.base.middleware_key', 'admin')],
    'prefix'     => config('bo.base.route_prefix', 'admin'),
], function () {
    Route::get('log', 'LogController@index')->name('log.index');
    Route::get('log/preview/{file_name}', 'LogController@preview')->name('log.show');
    Route::get('log/download/{file_name}', 'LogController@download')->name('log.download');
    Route::delete('log/delete/{file_name}', 'LogController@delete')->name('log.destroy');
});
