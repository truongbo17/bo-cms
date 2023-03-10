<?php

/*
|--------------------------------------------------------------------------
| Backpack\BackupManager Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Backpack\BackupManager package.
|
*/

Route::group([
    'namespace'  => 'Bo\BackupManager\App\Http\Controllers',
    'prefix'     => config('bo.base.route_prefix', 'admin'),
    'middleware' => ['web', config('bo.base.middleware_key', 'admin')],
], function () {
    Route::get('backup', 'BackupController@index')->name('backup.index');
    Route::put('backup/create', 'BackupController@create')->name('backup.store');
    Route::get('backup/download/', 'BackupController@download')->name('backup.download');
    Route::delete('backup/delete/', 'BackupController@delete')->name('backup.destroy');
});
