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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('commands', function () {
    return \App\Http\Resources\Command::collection(
        App\Command::latest()->get()
    );
});

Route::post('commands', function () {
    /** @var \App\Command $cmd */
    $cmd = App\Command::create([
        'command' => request('command'),
    ]);

    App\Jobs\RunCommand::dispatch($cmd);

    return \App\Http\Resources\Command::make($cmd);
});
