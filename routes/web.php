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

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/','Bi\HomeController@index');
Route::any('/stat/requestCount','Bi\StatController@requestCount');

/*Route::get('/phpinfo',function(){
    phpinfo();
});*/

Route::get('/test', function(){

    //$a = \App\Logics\StatLogic::setTripFrequencyDistribution(111, 'all:', 0);
    $b = \App\Logics\StatLogic::getTripFrequencyDistribution('all:', 0);

    var_dump( $b);exit;

    echo 666;
    var_dump(\App\Logics\StatLogic::getDailyActiveData(\App\Objects\DeviceObject::CACHE_CHANNEL_PRE, 1));
    var_dump(877);
});

Route::get('/home', 'HomeController@index')->name('home');
