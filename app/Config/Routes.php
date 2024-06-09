<?php

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Config\Services;

/**
 * @var RouteCollection $routes
 */

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
  require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
// $routes->setDefaultNamespace('App\Controllers');
// $routes->setDefaultController('AuthController');
// $routes->setDefaultMethod('index');
// $routes->setTranslateURIDashes(false);
// $routes->set404Override(function () {
//   $data['title'] = 'Not Found';
//   return view('auth/404', $data);
// });
// $routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Default route
$routes->get('/', 'AuthController::login');
$routes->post('/auth', 'AuthController::auth');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/dashboard', 'HomeController::index');

// Error handling routes
// $routes->get('/404', 'AuthController::notfound');
// $routes->get('/403', 'AuthController::blocked');

// Redirect invalid controller paths to 404
$invalidControllers = [
  'home',
  'homecontroller',
  'rolecontroller',
  'menucontroller',
  'submenucontroller',
  'menuaccesscontroller',
  'usercontroller',
  'profilecontroller',
  'transactionincontroller',
  'transactionoutcontroller',
  'cashflowincontroller',
  'cashflowoutcontroller',
  'reportcontroller',
  'authcontroller',
  'pdfcontroller'
];

// foreach ($invalidControllers as $controller) {
//   $routes->addRedirect("/{$controller}", '/404');
//   $routes->addRedirect("/{$controller}/(:any)", '/404');
// }

// Roles
$routes->group('roles', function ($routes) {
  $routes->get('/', 'RoleController::index');
  $routes->get('create', 'RoleController::create');
  $routes->post('save', 'RoleController::save');
  $routes->get('detail/(:num)', 'RoleController::detail/$1');
  $routes->get('edit/(:num)', 'RoleController::edit/$1');
  $routes->put('update/(:num)', 'RoleController::update/$1');
  $routes->delete('delete/(:num)', 'RoleController::delete/$1');
});

// Tools > Menu
$routes->group('tools/menu', function ($routes) {
  $routes->get('/', 'MenuController::index');
  $routes->get('create', 'MenuController::create');
  $routes->post('save', 'MenuController::save');
  $routes->get('edit/(:num)', 'MenuController::edit/$1');
  $routes->put('update/(:num)', 'MenuController::update/$1');
  $routes->delete('delete/(:num)', 'MenuController::delete/$1');
});

// Tools > Submenu
$routes->group('tools/submenu', function ($routes) {
  $routes->get('/', 'SubmenuController::index');
  $routes->get('create', 'SubmenuController::create');
  $routes->post('save', 'SubmenuController::save');
  $routes->get('edit/(:num)', 'SubmenuController::edit/$1');
  $routes->put('update/(:num)', 'SubmenuController::update/$1');
  $routes->delete('delete/(:num)', 'SubmenuController::delete/$1');
});

// Tools > Access
$routes->group('tools/access', function ($routes) {
  $routes->get('/', 'MenuAccessController::index');
  $routes->get('edit/(:num)', 'MenuAccessController::edit/$1');
  $routes->put('update/(:num)', 'MenuAccessController::update/$1');
});

// Users
$routes->group('users', function ($routes) {
  $routes->get('/', 'UserController::index');
  $routes->get('create', 'UserController::create');
  $routes->post('save', 'UserController::save');
  $routes->get('detail/(:num)', 'UserController::detail/$1');
  $routes->get('edit/(:num)', 'UserController::edit/$1');
  $routes->put('update/(:num)', 'UserController::update/$1');
  $routes->delete('delete/(:num)', 'UserController::delete/$1');
  $routes->get('reset/(:num)', 'UserController::reset/$1');
  $routes->patch('changepass/(:num)', 'UserController::changepass/$1');
});

// User's Profile
$routes->group('profile', function ($routes) {
  $routes->get('(:num)', 'ProfileController::show/$1');
  $routes->get('edit/(:num)', 'ProfileController::edit/$1');
  $routes->put('update/(:num)', 'ProfileController::update/$1');
  $routes->get('changepass/(:num)', 'ProfileController::editpass/$1');
  $routes->patch('changepass/(:num)', 'ProfileController::changepass/$1');
});

// Transactions > Transaksi Masuk
$routes->group('transactions/transaksi-masuk', function ($routes) {
  $routes->get('/', 'TransactionInController::index');
  $routes->get('create', 'TransactionInController::create');
  $routes->post('save', 'TransactionInController::save');
  $routes->get('edit/(:num)', 'TransactionInController::edit/$1');
  $routes->put('update/(:num)', 'TransactionInController::update/$1');
  $routes->get('detail/(:num)', 'TransactionInController::detail/$1');
  $routes->delete('delete/(:num)', 'TransactionInController::delete/$1');
});

// Transactions > Transaksi Pengambilan
$routes->group('transactions/transaksi-pengambilan', function ($routes) {
  $routes->get('/', 'TransactionOutController::index');
  $routes->get('create', 'TransactionOutController::create');
  $routes->post('save', 'TransactionOutController::save');
  $routes->delete('delete/(:num)', 'TransactionOutController::delete/$1');
});

// PDF generation routes for invoices
$routes->group('transactions', function ($routes) {
  $routes->get('invoice/generate-pdf/(:num)', 'PDFController::invoicePDF/$1');
});

// Cash flow > Pemasukan
$routes->group('cash-flow/pemasukan', function ($routes) {
  $routes->get('/', 'CashflowInController::index');
});

// Cash flow > Pengeluaran
$routes->group('cash-flow/pengeluaran', function ($routes) {
  $routes->get('/', 'CashflowOutController::index');
  $routes->get('create', 'CashflowOutController::create');
  $routes->post('save', 'CashflowOutController::save');
  $routes->get('edit/(:num)', 'CashflowOutController::edit/$1');
  $routes->put('update/(:num)', 'CashflowOutController::update/$1');
  $routes->delete('delete/(:num)', 'CashflowOutController::delete/$1');
});

// Reports
$routes->group('reports', function ($routes) {
  $routes->get('transaksi-masuk', 'ReportController::indexTransaksiMasuk');
  $routes->get('transaksi-masuk/generate-pdf/(:segment)/(:segment)', 'PDFController::transaksiMasukPDF/$1/$2');
  $routes->get('transaksi-pengambilan', 'ReportController::indexTransaksiPengambilan');
  $routes->get('transaksi-pengambilan/generate-pdf/(:segment)/(:segment)', 'PDFController::transaksiPengambilanPDF/$1/$2');
  $routes->get('pemasukan', 'ReportController::indexPemasukan');
  $routes->get('pemasukan/generate-pdf/(:segment)/(:segment)', 'PDFController::pemasukanPDF/$1/$2');
  $routes->get('pengeluaran', 'ReportController::indexPengeluaran');
  $routes->get('pengeluaran/generate-pdf/(:segment)/(:segment)', 'PDFController::pengeluaranPDF/$1/$2');
});

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
  require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
