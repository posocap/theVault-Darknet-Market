<?php

Route::middleware(['guest'])->group(function () {

    Route::get('signin','Auth\LoginController@showSignIn')->name('signin');
    Route::post('signin','Auth\LoginController@postSignIn')->name('signin.post')->middleware('throttle:10,1');

    Route::get('signup/{refid?}','Auth\RegisterController@showSignUp')->name('signup');

    Route::post('signup','Auth\RegisterController@signUpPost')->name('signup.post')->middleware('throttle:10,1');

    Route::get('mnemonic/show','Auth\RegisterController@showMnemonic')->name('mnemonic');

    Route::get('/forgotpassword', 'Auth\ForgotPasswordController@showForget');
    Route::get('/forgotpassowrd/mnemonic', 'Auth\ForgotPasswordController@showMnemonic');
    Route::get('/forgotpassword/pgp', 'Auth\ForgotPasswordController@showPGP');

    Route::post('/forgotpassword/mnemonic', 'Auth\ForgotPasswordController@resetMnemonic')->middleware('throttle:10,1');
    Route::post('/forgotpassword/pgp', 'Auth\ForgotPasswordController@sendVerify')->middleware('throttle:10,1');

    Route::get('/forgotpassword/pgp/verify', 'Auth\ForgotPasswordController@showVerify')->name('pgprecover');
    Route::post('/forgotpassword/pgp/verify', 'Auth\ForgotPasswordController@resetPgp')->name('resetpgp')->middleware('throttle:10,1');

});

Route::get('verify', 'Auth\LoginController@showVerify') -> name('verify');
Route::post('verify', 'Auth\LoginController@postVerify') -> name('verify.post')->middleware('throttle:10,1');


Route::post('signout','Auth\LoginController@postSignOut')->name('signout.post');




