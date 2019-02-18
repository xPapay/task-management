<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'DashboardController@index')->middleware('auth');

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/tasks', 'TasksController@index');
Route::get('/tasks/supervising', 'SupervisedTasksController@index')->middleware('auth');
Route::get('/tasks/{task}/assignees', 'AssigneesController@index')->middleware('auth');
Route::get('/tasks/{task}/comments', 'CommentsController@index')->middleware('auth');
Route::post('/tasks/{task}/comments', 'CommentsController@store')->name('comment.add')->middleware('auth');
Route::get('/tasks/{task}', 'TasksController@show')->middleware('auth');
Route::patch('/tasks/{task}', 'TasksController@update')->middleware('auth');
Route::delete('/tasks/{task}', 'TasksController@destroy')->middleware('auth');
Route::post('/tasks', 'TasksController@store')->middleware('auth');

Route::post('/finished-tasks/{task}', 'FinishedTasksController@store')->middleware('auth');
Route::delete('/finished-tasks/{task}', 'FinishedTasksController@destroy')->middleware('auth');

Route::get('/profile/create', 'ProfilesController@create');// TODO: admin middleware
Route::post('/profile/create', 'ProfilesController@store')->name('profile.create');
Route::post('/profile/update-password', 'ProfilesController@updatePassword')->middleware('auth')->name('profile.updatePassword');
Route::get('/profile', 'ProfilesController@show')->name('profile')->middleware('auth');
Route::post('/profile', 'ProfilesController@updateAvatar')->middleware('auth');
Route::patch('/profile', 'ProfilesController@update')->middleware('auth');

Route::get('/users', 'UsersController@index')->middleware('auth');

Route::get('/notifications', 'NotificationsController@index')->name('notifications')->middleware('auth');
Route::patch('/notifications/read', 'NotificationsController@readAll')->middleware('auth');
Route::patch('/notifications/{notification}', 'NotificationsController@update')->middleware('auth');

Route::get('/attachments/{attachment}', 'AttachmentsController@show');
