<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('register', static function () {
	return redirect()->to('/login');
});

$routes->post('register', static function () {
	return redirect()->to('/login');
});

service('auth')->routes($routes);

$routes->group('', ['filter' => 'session'], static function ($routes): void {
	$routes->get('dashboard', 'DashboardController::index');

	$routes->group('admin', ['filter' => 'group:admin,superadmin'], static function ($routes): void {
		$routes->get('employees', 'Admin\EmployeeController::index');
		$routes->post('employees/sync', 'Admin\EmployeeController::sync');

		$routes->group('signatories', static function ($routes) {
			$routes->get('/', 'Admin\SignatoriesController::index');
			$routes->get('create', 'Admin\SignatoriesController::create');
			$routes->post('store', 'Admin\SignatoriesController::store');
			$routes->get('(:num)/edit', 'Admin\SignatoriesController::edit/$1');
			$routes->post('(:num)/update', 'Admin\SignatoriesController::update/$1');
			$routes->post('(:num)/destroy', 'Admin\SignatoriesController::destroy/$1');
		});
		$routes->get('reports', 'Admin\PlaceholderController::index/reports');

		$routes->group('users', static function ($routes) {
			$routes->get('/', 'Admin\UserController::index');
			$routes->get('create/(:num)', 'Admin\UserController::create/$1');
			$routes->post('store', 'Admin\UserController::store');
			$routes->get('credential', 'Admin\UserController::showCredential');
			$routes->get('(:num)/edit', 'Admin\UserController::edit/$1');
			$routes->post('(:num)/update', 'Admin\UserController::update/$1');
			$routes->post('(:num)/reset-password', 'Admin\UserController::resetPassword/$1');
			$routes->post('(:num)/toggle-active', 'Admin\UserController::toggleActive/$1');
			$routes->post('(:num)/destroy', 'Admin\UserController::destroy/$1');
		});

		$routes->group('tariffs', static function ($routes) {
			$routes->get('/', 'Admin\TariffController::index');
			$routes->get('create', 'Admin\TariffController::create');
			$routes->post('store', 'Admin\TariffController::store');
			$routes->get('(:num)/edit', 'Admin\TariffController::edit/$1');
			$routes->post('(:num)/update', 'Admin\TariffController::update/$1');
			$routes->post('(:num)/destroy', 'Admin\TariffController::destroy/$1');
		});
	});

	// Pengajuan Perdin — accessible by Kepegawaian, Keuangan, Dosen
	$routes->group('travel', ['filter' => 'group:admin,superadmin,lecturer'], static function ($routes): void {
		$routes->get('', 'TravelRequestController::index');
		$routes->get('create', 'TravelRequestController::create');
		$routes->post('store', 'TravelRequestController::store');
		$routes->get('employees', 'TravelRequestController::getEmployees');
		$routes->post('check-tariff', 'TravelRequestController::checkTariff');
		$routes->get('(:num)', 'TravelRequestController::show/$1');
		$routes->get('(:num)/edit', 'TravelRequestController::edit/$1');
		$routes->post('(:num)/update', 'TravelRequestController::update/$1');
		$routes->post('(:num)/destroy', 'TravelRequestController::destroy/$1');
		$routes->post('(:num)/submit', 'TravelRequestController::submit/$1');
		$routes->post('(:num)/cancel', 'TravelRequestController::cancel/$1');
		$routes->get('(:num)/lampiran', 'TravelRequestController::downloadLampiran/$1');
		$routes->get('(:num)/sppd', 'TravelRequestController::downloadSppd/$1');
	});

	$routes->group('verification', ['filter' => 'group:verificator'], static function ($routes): void {
		$routes->get('/', 'VerificationController::index');
		$routes->get('(:num)/show', 'VerificationController::show/$1');
		$routes->post('(:num)/approve', 'VerificationController::approve/$1');
		$routes->post('(:num)/reject', 'VerificationController::reject/$1');
	});
});
