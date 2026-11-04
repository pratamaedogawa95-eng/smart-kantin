<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Admin;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Customer::index');

// ==========================================
// RUTE KHUSUS CUSTOMER
// ==========================================
$routes->get('customer', 'Customer::index');
$routes->post('customer/checkout', 'Customer::checkout');
$routes->get('customer/sukses/(:num)', 'Customer::sukses/$1');

// Fetch API status real-time di HP customer
$routes->get('customer/status-json/(:num)', 'Customer::getOrderStatusJson/$1');

// Endpoint konfirmasi tombol "Sudah Diterima"
$routes->post('customer/terimaMakanan/(:num)', 'Customer::terimaMakanan/$1');

$routes->get('customer/cari-civitas', 'Customer::cariCivitas');
$routes->post('customer/simpan-identitas', 'Customer::simpanIdentitas');
$routes->get('customer/logout-identitas', 'Customer::logoutIdentitas');

$routes->get('customer/qris/(:num)', 'Customer::qris/$1');
$routes->post('customer/proses-bayar-qris/(:num)', 'Customer::prosesBayarQris/$1');
$routes->get('customer/menu-json', 'Customer::getMenuJson');

// ==========================================
// RUTE MODUL TENANT / DAPUR / DASHBOARD
// ==========================================

// Autentikasi Tenant
$routes->get('tenant/login', 'Tenant\Auth::login');
$routes->post('tenant/proses-login', 'Tenant\Auth::prosesLogin');
$routes->get('tenant/logout', 'Tenant\Auth::logout');

// Dashboard & Monitor Dapur
$routes->get('tenant', 'Tenant\Dashboard::index');
$routes->get('tenant/dashboard', 'Tenant\Dashboard::index');
$routes->get('tenant/get-orders-json', 'Tenant\Dashboard::getOrdersJson');

// Kelola Menu (Tambah, Edit, Update, Hapus, Toggle Status)
$routes->post('tenant/storeMenu', 'Tenant\Dashboard::storeMenu');
$routes->get('tenant/menu/edit/(:num)', 'Tenant\Dashboard::editMenu/$1');
$routes->post('tenant/menu/update/(:num)', 'Tenant\Dashboard::updateMenu/$1');

// Rute Toggle Status (Didaftarkan keduanya agar aman dari perbedaan penamaan link view)
$routes->get('tenant/toggleStatus/(:num)', 'Tenant\Dashboard::toggleStatus/$1');
$routes->get('tenant/toggleStatusMenu/(:num)', 'Tenant\Dashboard::toggleStatus/$1');

$routes->get('tenant/deleteMenu/(:num)', 'Tenant\Dashboard::deleteMenu/$1');
$routes->post('tenant/updateStatus', 'Tenant\Dashboard::updateStatus');


// ==========================================
// RUTE MODUL SUPER ADMIN
// ==========================================
$routes->get('admin', [Admin::class, 'index']);
$routes->post('admin/simpanTenant', [Admin::class, 'simpanTenant']);
$routes->post('admin/simpanMeja', [Admin::class, 'simpanMeja']);
$routes->get('admin/hapusMeja/(:num)', [Admin::class, 'hapusMeja/$1']);

// Fetch API statistik omzet & laporan tenant real-time
$routes->get('admin/stats-json', [Admin::class, 'getAdminStatsJson']);