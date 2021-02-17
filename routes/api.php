<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/**AUTH****/

Route::post('user-login', 'Auth\LoginUserController@login')->name('user-login');
Route::post('user-refresh-token', 'Auth\LoginUserController@refresh')->name('user-refresh');
Route::post('user-forget-password', 'Auth\ForgotPasswordController@userForgetPassword');
Route::post('user-reset-password', 'Auth\ResetPasswordController@userResetPassword');

Route::post('customer-login', 'Auth\LoginCustomerController@login')->name('customer-login');
Route::post('customer-forget-password', 'Auth\ForgotPasswordController@customerForgetPassword');
Route::post('customer-reset-password', 'Auth\ResetPasswordController@customerResetPassword');


Route::group(['middleware' => ['refresh_token','refresh_cookie', 'auth:user']], function()
{
    Route::post('user-me', 'Auth\LoginUserController@me');
    Route::post('user-logout', 'Auth\LoginUserController@logout');
});

Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:customer']], function()
{
    Route::post('customer-me', 'Auth\LoginCustomerController@me');
    Route::post('customer-logout', 'Auth\LoginCustomerController@logout');
});


Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user']], function()
{
    Route::get('user-profile/get', 'Auth\LoginUserController@getProfile');
    Route::post('user-profile/update/{id}', 'Auth\LoginUserController@updateProfile');
});

Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:customer']], function()
{
    Route::get('customer-profile/get', 'Auth\LoginCustomerController@getProfile');
    Route::post('customer-profile/update/{id}', 'Auth\LoginCustomerController@updateProfile');
});


/***Notification Backend****/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'notification', 'namespace'=>'Back\Notification'], function () {
    Route::get('get-notifications', 'NotificationController@getNotifications');
    Route::get('get-counter', 'NotificationController@getCounter');
    Route::get('get/{id}/{category?}', 'NotificationController@getNotification');
    Route::get('unread/get', 'NotificationController@getUnreadNotification');
    Route::put('set-notification-read/{id}/{category?}', 'NotificationController@readNotification');
    Route::put('set-favoris', 'NotificationController@setNotificationFavoris');
    Route::put('recover/inbox', 'NotificationController@recoverInInbox');
    Route::delete('destroy/{array}', 'NotificationController@destroy');
    Route::delete('destroy/definitive/{array}', 'NotificationController@destroyDefinitive');
});

/***Customer****/
// --- Customer manage ----
Route::group(['middleware' => ['refresh_token','refresh_cookie', 'auth:user'], 'prefix' => 'customer', 'namespace'=>'Back\Customer'], function () {
    Route::get('manage/get', 'CustomerController@getCustomers');
    Route::get('manage/get/{id}/{type?}', 'CustomerController@getCustomer');
    Route::get('manage/requirements', 'CustomerController@getRequirements');
    Route::post('manage/store', 'CustomerController@store');
    Route::post('manage/update/{id}', 'CustomerController@update');
    Route::delete('manage/destroy/{array}', 'CustomerController@destroy');
});

// --- Customer notes ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'customer', 'namespace'=>'Back\Customer'], function () {
    Route::get('note/get', 'CustomerNoteController@getNotes');
    Route::get('note/get/{id}/{type?}', 'CustomerNoteController@getNote');
    Route::post('note/store', 'CustomerNoteController@store');
    Route::put('note/update/{id}', 'CustomerNoteController@update');
    Route::delete('note/destroy/{array}', 'CustomerNoteController@destroy');
});


/***Event****/
// --- event manage ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'event', 'namespace'=>'Back\Event'], function () {
    Route::get('manage/get', 'EventController@getEvents');
    Route::get('manage/get/{id}/{type?}', 'EventController@getEvent');
    Route::get('manage/requirements', 'EventController@getRequirements');
    Route::post('manage/check-step/{step}/{id?}', 'EventController@checkStepper');
    Route::get('manage/set-statut-publication/{id}', 'EventController@setStatutPublication');
    Route::put('manage/set-action', 'EventController@setActionEvent');
    Route::post('manage/store', 'EventController@store');
    Route::post('manage/update/{id}', 'EventController@update');
    Route::delete('manage/destroy/{array}', 'EventController@destroy');
});

// --- Espace ----
//manage espace
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'space', 'namespace'=>'Back\Espace'], function () {
    Route::get('manage/get', 'EspaceController@getEspaces');
    Route::get('manage/get/{id}/{type?}', 'EspaceController@getEspace');
    Route::get('manage/requirements', 'EspaceController@getRequirements');
    Route::post('manage/store', 'EspaceController@store');
    Route::post('manage/update/{id}', 'EspaceController@update');
    Route::delete('manage/destroy/{array}', 'EspaceController@destroy');
});

//reservation espace
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'space', 'namespace'=>'Back\Espace'], function () {
    Route::get('event/get-space-events', 'EspaceEventController@getEspaceEvents');
    Route::get('event/get/{id}/{type?}', 'EspaceEventController@getReservation');
    Route::get('event/get-event-planning/{id}', 'EspaceEventController@getSpacePlanning');
    Route::put('event/set-statut/{id}', 'EspaceEventController@setStatutReservationSpace');
    Route::get('event/requirements', 'EspaceEventController@getRequirements');
    Route::post('event/store', 'EspaceEventController@store');
    Route::delete('event/destroy/{array}', 'EspaceEventController@destroy');
});

/***Repas***/
// --- Restaurant ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'repas', 'namespace'=>'Back\Repas'], function () {
    Route::get('restaurant/get', 'RestaurantController@getRestaurants');
    Route::get('restaurant/get/{id}', 'RestaurantController@getRestaurant');
    Route::post('restaurant/store', 'RestaurantController@store');
    Route::put('restaurant/update/{id}', 'RestaurantController@update');
    Route::delete('restaurant/destroy/{array}', 'RestaurantController@destroy');
});

// --- Menu ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'repas', 'namespace'=>'Back\Repas'], function () {
    Route::get('menu/get', 'MenuController@getMenus');
    Route::get('menu/get/{id}/{type?}', 'MenuController@getMenu');
    //Route::get('menu/requirements', 'MenuController@getRequirements');
    Route::post('menu/store', 'MenuController@store');
    Route::post('menu/update/{id}', 'MenuController@update');
    Route::delete('menu/destroy/{array}', 'MenuController@destroy');
});

// --- Commande ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'repas', 'namespace'=>'Back\Repas'], function () {
    Route::get('commande/get', 'CommandeController@getCommandes');
    Route::get('commande/get/{id}/{type?}', 'CommandeController@getCommande');
    Route::get('commande/requirements', 'CommandeController@getRequirements');
    Route::post('commande/store', 'CommandeController@store');
    Route::put('commande/update/{id}', 'CommandeController@update');
    Route::put('commande/set-statut/{id}', 'CommandeController@setStatutCommande');
    Route::delete('commande/destroy/{array}', 'CommandeController@destroy');
});

/***CONFIG****/
Route::group(['prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('general/get', 'GeneralController@get');
});
Route::group(['prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::post('general/update', 'GeneralController@update');
});
/***Role***/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('role/get', 'RoleController@getRoles');
    Route::get('role/get/{id}', 'RoleController@getRole');
    Route::get('role/permissions', 'RoleController@getPermissions');
    Route::post('role/store', 'RoleController@store');
    Route::put('role/update/{id}', 'RoleController@update');
    Route::delete('role/destroy/{array}', 'RoleController@destroy');
});

/***Locale***/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('locale/get', 'LocaleController@getLocales');
    Route::get('locale/get/{id}', 'LocaleController@getLocale');
    Route::post('locale/store', 'LocaleController@store');
    Route::put('locale/update/{id}', 'LocaleController@update');
    Route::delete('locale/destroy/{array}', 'LocaleController@destroy');
});

/***Civilité***/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('civilite/get', 'CiviliteController@getCivilites');
    Route::get('civilite/get/{id}', 'CiviliteController@getCivilite');
    Route::post('civilite/store', 'CiviliteController@store');
    Route::put('civilite/update/{id}', 'CiviliteController@update');
    Route::delete('civilite/destroy/{array}', 'CiviliteController@destroy');
});

/***Member status***/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('member-status/get', 'MemberStatutController@getMemberStatuts');
    Route::get('member-status/get/{id}', 'MemberStatutController@getMemberStatut');
    Route::post('member-status/store', 'MemberStatutController@store');
    Route::put('member-status/update/{id}', 'MemberStatutController@update');
    Route::delete('member-status/destroy/{array}', 'MemberStatutController@destroy');
});

/**USER**/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:user'], 'prefix' => 'config', 'namespace'=>'Back\Config'], function () {
    Route::get('user/get', 'UserController@getUsers');
    Route::get('user/get/{id}/{type?}', 'UserController@getUser');
    Route::get('user/requirements', 'UserController@getRequirements');
    Route::post('user/store', 'UserController@store');
    Route::put('user/update/{id}', 'UserController@update');
    Route::delete('user/destroy/{array}', 'UserController@destroy');
});



/****FRONT ROUTE****/
/***Notification Frontend****/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:customer'], 'prefix' => 'front/notification', 'namespace'=>'Front\Notification'], function () {
    Route::get('get-notifications', 'NotificationController@getNotifications');
    Route::get('get-counter', 'NotificationController@getCounter');
    Route::get('get/{id}/{category?}', 'NotificationController@getNotification');
    Route::get('unread/get', 'NotificationController@getUnreadNotification');
    Route::put('set-notification-read/{id}/{category?}', 'NotificationController@readNotification');
    Route::put('set-favoris', 'NotificationController@setNotificationFavoris');
    Route::put('recover/inbox', 'NotificationController@recoverInInbox');
    Route::delete('destroy/{array}', 'NotificationController@destroy');
    Route::delete('destroy/definitive/{array}', 'NotificationController@destroyDefinitive');
});

/***Event****/
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:customer'], 'prefix' => 'front/event', 'namespace'=>'Front\Event'], function () {
    Route::get('published', 'EventController@getPublished');
    Route::get('my-events', 'EventController@getMyEvents');
    Route::get('my-invitations', 'EventController@getMyInvitations');
    Route::get('get/{id}', 'EventController@getEvent');
    Route::put('set-action', 'EventController@setActionEvent');
});


/***Repas***/

// --- Commande ----
Route::group(['middleware' => ['refresh_token','refresh_cookie','auth:customer'], 'prefix' => 'front/repas', 'namespace'=>'Front\Repas'], function () {
    Route::get('restaurants/get', 'RestaurantController@getRestaurants');
    Route::get('menus/get', 'RestaurantController@getMenus');
    Route::get('commandes/get', 'CommandeController@getCommandes');
    Route::get('commande/get/{id}/{type?}', 'CommandeController@getCommande');
    Route::post('commande/store', 'CommandeController@store');
    Route::put('commande/update/{id}', 'CommandeController@update');
    Route::delete('commande/destroy/{array}', 'CommandeController@destroy');
});
