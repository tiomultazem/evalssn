<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->match(['get', 'post'], 'runquery', 'Home::runquery');

$routes->get('settings', 'SettingsController::index');
$routes->match(['get', 'post'], 'settings/load-databases', 'SettingsController::loadDatabases');
$routes->match(['get', 'post'], 'settings/test-connection', 'SettingsController::testConnection');
$routes->match(['get', 'post'], 'settings/save', 'SettingsController::save');
$routes->post('settings/upload-query', 'SettingsController::uploadQuery');
$routes->post('activate-license', 'Home::activateLicense');

$routes->set404Override('\App\Controllers\ErrorController::notFound');
