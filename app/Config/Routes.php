<?php use CodeIgniter\Router\RouteCollection;

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

 // TEST
$routes->get("/", "HomeController::Index");
 // TEST
$routes->get("/test", "SignupApiController::TestPreflight");

// 仮登録取得
$routes->post("/api/signup/load.preflight", "SignupApiController::LoadPreflight");
// 仮登録作成
$routes->post("/api/signup/create.preflight", "SignupApiController::CreatePreflight");
// 仮登録認証
$routes->post("/api/signup/auth.preflight", "SignupApiController::AuthPreflight");

// 利用者作成
$routes->post("/api/signup/create.user", "SignupApiController::CreateUser");
// 利用者認証
$routes->post("/api/signin/auth.user", "SigninApiController::AuthUser");