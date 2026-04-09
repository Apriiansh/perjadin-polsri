<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('register', static function () {
	return redirect()->to('/login');
});

$routes->get('register', static function () {
	return redirect()->to('/login');
});

$routes->post('register', static function () {
	return redirect()->to('/login');
});

// Override Shield's Login Controller with Hybrid Login Controller
$routes->get('login', 'Auth\LoginController::loginView');
$routes->post('login', 'Auth\LoginController::loginAction');

// SSO Routes
$routes->get('sso/to-polsripay', 'Auth\SsoController::toPolsripay');
$routes->get('sso/consume', 'Auth\SsoController::consume');

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
		$routes->group('reports', static function ($routes) {
			$routes->get('/', 'Admin\ReportController::index');
			$routes->get('download/(:num)', 'Admin\ReportController::downloadSpjBundle/$1');
		});

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

		$routes->group('students', static function ($routes) {
			$routes->get('/', 'Admin\StudentController::index');
			$routes->get('(:num)', 'Admin\StudentController::show/$1');
			$routes->get('(:num)/edit', 'Admin\StudentController::edit/$1');
			$routes->post('(:num)/update', 'Admin\StudentController::update/$1');
			$routes->post('(:num)/reset-password', 'Admin\StudentController::resetPassword/$1');
			$routes->post('(:num)/destroy', 'Admin\StudentController::destroy/$1');
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
	// Pengajuan Perdin — accessible by Kepegawaian, Keuangan, Dosen, Mahasiswa
	$routes->group('travel', ['filter' => 'group:admin,superadmin,lecturer,verificator,student'], static function ($routes): void {
		$routes->get('', 'TravelRequestController::index');
		$routes->get('active', 'TravelRequestController::active');
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
		$routes->post('(:num)/complete', 'TravelRequestController::complete/$1');
		$routes->get('download/lampiran/(:num)', 'TravelRequestController::downloadLampiran/$1');
		$routes->get('download/file/(:num)', 'TravelRequestController::downloadFile/$1');
		$routes->get('download/spd/(:num)', 'TravelRequestController::downloadSpd/$1');
		$routes->get('(:num)/statement', 'TravelRequestController::downloadStatement/$1');
		$routes->get('(:num)/control-list', 'TravelRequestController::downloadControlList/$1');
		$routes->get('(:num)/nominative-list', 'TravelRequestController::downloadNominativeList/$1');
		$routes->get('(:num)/bundle-excel', 'TravelRequestController::downloadBundleExcel/$1');
		$routes->get('(:num)/bundle-spj', 'Admin\ReportController::downloadSpjBundle/$1');

		// Data Enrichment (Phase 8)
		$routes->get('(:num)/enrichment', 'CompletenessController::enrichment/$1');
		$routes->post('(:num)/enrichment', 'CompletenessController::storeEnrichment/$1');

		// Document Submission & Review (Phase 8b & 9)
		$routes->group('completeness', static function ($routes) {
			$routes->post('(:num)/upload', 'ReviewController::upload/$1');
			$routes->get('(:num)/download', 'ReviewController::download/$1');
			$routes->post('(:num)/verify', 'ReviewController::verify/$1');
			$routes->post('(:num)/verify-all', 'ReviewController::verifyAll/$1'); // Phase 20
			$routes->post('(:num)/reject-all', 'ReviewController::rejectAll/$1'); // Phase 21
			$routes->post('member/(:num)/verify', 'ReviewController::verifyMember/$1'); // Phase 31
			$routes->post('member/(:num)/reject', 'ReviewController::rejectMember/$1'); // Phase 31
		});

		// Student Travel Routes (Phase 24)
		$routes->group('student', static function ($routes) {
			$routes->get('', 'StudentTravelController::index');
			$routes->get('create', 'StudentTravelController::create');
			$routes->post('store', 'StudentTravelController::store');
			$routes->get('credential', 'StudentTravelController::credential');
			$routes->get('(:num)', 'StudentTravelController::show/$1');
			$routes->get('(:num)/download', 'StudentTravelController::downloadReport/$1');

			// Support Documentation (Perjadin Mahasiswa)
			$routes->get('(:num)/enrichment', 'StudentCompletenessController::enrichment/$1');
			$routes->post('(:num)/enrichment', 'StudentCompletenessController::storeEnrichment/$1');

			$routes->get('(:num)/documentation', 'StudentReviewController::documentation/$1');
			$routes->post('(:num)/documentation', 'StudentReviewController::submitDocumentation/$1');
			$routes->get('(:num)/verification', 'StudentReviewController::verification/$1');
			$routes->post('(:num)/verify/(:segment)', 'StudentReviewController::verifyAll/$1');

			$routes->post('(:num)/destroy', 'StudentTravelController::destroy/$1'); 
			$routes->post('(:num)/cancel', 'StudentTravelController::cancel/$1');

			$routes->delete('file/(:num)', 'StudentReviewController::deleteFile/$1');
			$routes->get('file/(:num)', 'StudentReviewController::viewFile/$1');
			$routes->get('download/(:num)', 'StudentReviewController::downloadFile/$1');
		});
	});

	$routes->group('verification', ['filter' => 'group:verificator'], static function ($routes): void {
		$routes->get('/', 'VerificationController::index');
		$routes->get('(:num)/show', 'VerificationController::show/$1');
		$routes->post('(:num)/approve', 'VerificationController::approve/$1');
		$routes->post('(:num)/reject', 'VerificationController::reject/$1');
	});

	// Documentation & Verification (Phase 12 & 13)
	$routes->group('documentation', function ($routes) {
		$routes->get('(:num)', 'ReviewController::documentation/$1');
		$routes->post('(:num)', 'ReviewController::submitDocumentation/$1');
		$routes->delete('file/(:num)', 'ReviewController::deleteFile/$1');
		$routes->get('file/(:num)', 'ReviewController::viewFile/$1'); // Phase 15
		$routes->get('download/(:num)', 'ReviewController::downloadFile/$1'); // Phase 16
		$routes->get('(:num)/verification', 'ReviewController::verification/$1', ['filter' => 'group:superadmin,verificator']);
	});

	// Blanko Kosong (Phase 23)
	$routes->group('blanko-kosong', ['filter' => 'group:lecturer,admin,superadmin,verificator'], static function ($routes) {
		$routes->get('/', 'BlankoController::index');
		$routes->get('download', 'BlankoController::download');
	});
});
