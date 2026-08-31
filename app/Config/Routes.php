<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('health/live', 'Health::live');
$routes->get('health/ready', 'Health::ready');
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->post('register', 'Auth::register');
$routes->post('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('groups', 'Groups::index');
$routes->get('groups/all', 'Groups::all');
$routes->get('groups/new', 'Groups::createForm');
$routes->post('groups', 'Groups::create');
$routes->get('groups/(:num)', 'Groups::show/$1');
$routes->post('groups/(:num)/members', 'Groups::addMember/$1');

$routes->get('bracket', 'Bracket::index');
$routes->post('bracket/picks', 'Bracket::save');
$routes->get('games', 'Games::index');
$routes->get('profile', 'Profile::index');
