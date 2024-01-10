<?php 
use CodeIgniter\Router\RouteCollection;

/**
 * ========== Remix用メソッド規約 ============
 * GETメソッド  => 取得
 * POSTメソッド => 取得 or 新規登録
 * PUTメソッド  => 更新登録
 * DELETEメソッド => 削除
 * ==========================================
 */

/**
 * @var RouteCollection $routes
 */

// 仮登録取得
$routes->post("/api/signup/load.preflight", "SignupApiController::LoadPreflight");
// 仮登録作成
$routes->post("/api/signup/create.preflight", "SignupApiController::CreatePreflight");

$routes->get("/api/signup/test.preflight", "SignupApiController::TestPreflight");