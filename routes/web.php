<?php

use Illuminate\Support\Facades\Route;
use Studio\Totem\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\TasksController;
use Studio\Totem\Http\Controllers\ExportTasksController;
use Studio\Totem\Http\Controllers\ImportTasksController;
use Studio\Totem\Http\Controllers\ActiveTasksController;
use Studio\Totem\Http\Controllers\ExecuteTasksController;

Route::get('/artisan-migrate', function() {
    Artisan::call('migrate');
    return "migration done";
});


Route::redirect('/', 'admin/home');

Auth::routes(['register' => false]);

// Change Password Routes...
Route::get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
Route::patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');

Route::group(['middleware' => ['auth'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('permissions', 'Admin\PermissionsController');
    Route::delete('permissions_mass_destroy', 'Admin\PermissionsController@massDestroy')->name('permissions.mass_destroy');
    Route::resource('roles', 'Admin\RolesController');
    Route::delete('roles_mass_destroy', 'Admin\RolesController@massDestroy')->name('roles.mass_destroy');
    Route::resource('users', 'Admin\UsersController');
    Route::delete('users_mass_destroy', 'Admin\UsersController@massDestroy')->name('users.mass_destroy');

    Route::group(['prefix' => 'tasks'], function () {
        Route::get('/', [TasksController::class, 'index'])->name('tasks.all');
    
        Route::get('create', [TasksController::class, 'create'])->name('task.create');
        Route::post('create', [TasksController::class, 'store']);
    
        Route::get('export', [ExportTasksController::class, 'index'])->name('tasks.export');
        Route::post('import', [ImportTasksController::class, 'index'])->name('tasks.import');
    
        Route::get('{task}', [TasksController::class, 'view'])->name('task.view');
    
        Route::get('{task}/edit', [TasksController::class, 'edit'])->name('task.edit');
        Route::post('{task}/edit', [TasksController::class, 'update']);
    
        Route::delete('{task}', [TasksController::class, 'destroy'])->name('task.delete');
    
        Route::post('status', [ActiveTasksController::class, 'store'])->name('task.activate');
        Route::delete('status/{id}', [ActiveTasksController::class, 'destroy'])->name('task.deactivate');
    
        Route::get('{task}/execute', [ExecuteTasksController::class, 'index'])->name('task.execute');
    });
});
