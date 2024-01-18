<?php namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * UtilHelper
 * @todo ユーティリティヘルパー
 */
class UtilHelper
{
  /**
   * Connect CMSB
   */
  public static function ConnectCMSB() {
    $libraryPath = 'cmsb/lib/viewer_functions.php';
    $dirsToCheck = ['','../','../../','../../../','../../../../']; // add if needed: 'C:/Apache24/htdocs/fut-event-site-ci4/'
    foreach ($dirsToCheck as $dir) { if (@include_once("$dir$libraryPath")) { break; }}
    if (!function_exists('getRecords')) { die("Couldn't load viewer library, check filepath in sourcecode."); }
  }
  
  /**
   * Encrypt
   * @param String $plain_text
   * @param String $key
   * @param String $salt
   * @return String any
   * @todo 暗号化処理
   */
  public static function Encrypt($plain_text, $key, $salt)
  {
    $pbkdf2 = self::Pbkdf2("sha1", $key, $salt, 1000, 48, true);
    $key = substr($pbkdf2, 0, 32); //Keylength: 32
    $iv = substr($pbkdf2, 32, 16); // IV-length: 16
    return base64_encode(@openssl_encrypt($plain_text, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv));
  }
  
  /**
   * Decrypt
   * @param String $encrypted_text
   * @param String $key
   * @param String $salt
   * @return String any
   * @todo 複合化処理
   */
  public static function Decrypt($encrypted_text, $key, $salt)
  {
    $pbkdf2 = self::Pbkdf2("sha1", $key, $salt, 1000, 48, true);
    $key = substr($pbkdf2, 0, 32); //Keylength: 32
    $iv = substr($pbkdf2, 32, 16); // IV-length: 16
    return @openssl_decrypt(base64_decode($encrypted_text), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
  }
    
  /**
   * Pbkdf2
   * @param String $algorithm
   * @param String $key
   * @param String $salt
   * @param Int $count
   * @param Int $key_length
   * @param Bool $raw_output
   * @return String any
   * @todo PBKDF2鍵導出処理
   */
  private static function Pbkdf2($algorithm, $key, $salt, $count, $key_length, $raw_output = false)
  {
    $algorithm = strtolower($algorithm);
    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);
    
    $output = "";
    for($i = 1; $i <= $block_count; $i++) 
    {
      // $i encoded as 4 bytes, big endian.
      $last = $salt . pack("N", $i);
      // first iteration
      $last = $xorsum = hash_hmac($algorithm, $last, $key, true);
      // perform the other $count - 1 iterations
      for($j = 1; $j < $count; $j++) 
      {
        $xorsum ^= ($last = hash_hmac($algorithm, $last, $key, true));
      }
      $output .= $xorsum;
    }
    return substr($output, 0, $key_length);
  }
	
	/**
	 * GetRandomNumber
	 * @param Int $length
	 * @todo ランダム文字列取得
	 */
	public static function GetRandomNumber(Int $length = 32)
	{
		return substr(str_shuffle("1234567890"), 0, $length);
	}
  
	/**
	 * GetRandomString
	 * @param Int $length
	 * @todo ランダム文字列取得
	 */
	public static function GetRandomString(Int $length = 32)
	{
		return substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	}
	
	/**
	 * GetToken
	 * @todo トークン生成
	 */
	public static function GenerateToken(int $length)
	{
		return self::GetRandomString($length);
	}
	
	/**
	 * GetSecretKey
	 * @param Int $length
	 * @todo Salt取得
	 */
	public static function GetSecretKey(int $length = 16)
	{
		return base64_encode(openssl_random_pseudo_bytes($length));
	}
	
	/**
	 * GetDateJP
	 * @param String $date
	 * @param Bool $is_week
	 * @return String any
	 * @todo 日本式日付取得
	 */
	public static function GetDateJP($date, $is_week = false)
	{
	}
	
	/**
	 * 指定したキーの値を取得する。2次元配列のみ対応
	 * @param target_data 値を取り出したい多次元配列
	 * @param column_key  値を返したいカラム
	 * @param index_key   返す配列のインデックスとして使うカラム
	 * return array       入力配列の単一のカラムを表す値の配列を返し
	 */
	public static function ArrayColumn($target_data, $column_key, $index_key = null) 
	{
		if (is_array($target_data) === FALSE || count($target_data) === 0) return FALSE;
		
		$result = array();
		foreach ($target_data as $array) 
		{
			if (array_key_exists($column_key, $array) === FALSE) continue;
			if (is_null($index_key) === FALSE && array_key_exists($index_key, $array) === TRUE) 
			{
				$result[$array[$index_key]] = $array[$column_key];
				continue;
			}
			$result[] = $array[$column_key];
		}
		
		if (count($result) === 0) return FALSE;
		return $result;
	}
  
  /**
   * GroupBy
   * @param String $key
   * @param Array $array
   * @return Array any
   */
  public static function GroupBy($key, array $array): array
  {
    $result = [];
    foreach ($array as $row) 
    {
      if (array_key_exists($key, $row)) 
      {
        $result[$row[$key]][] = $row;
      } 
      else 
      {
        $result[""][] = $row;
      }
    }
    return $result;
  }
  
  /**
   * Br2Nl
   * @param String $string
   * @return String any
   */
  public static function Br2Nl(string $string): string
  {
    $s = str_replace("<br>", "\n", $string);
    return preg_replace('/&lt;br[[:space:]]*\ ?[[:space:]]*=""&gt;/i', "", $s);
  }
  
  /**
   * 参照元が許可ドメインと一致するか判別する
   *
   * @param {string} $url_host: 指定するURLと一致するか確認する
   * @return boolean
   */
  public static function IsAllowedDomain(string $url_host = "")
  {
    return isset($_SERVER["HTTP_REFERER"]) && parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST) === $url_host;
  }
  
  /**
   * GetEncodedSignature
   * @param Array $payload
   * @return String any
   */
  public static function GetEncodedSignature(array $payload): string
  {
    /*
    // Payload生成
    $payload = [];
    
    // Payload生成
    $payload = [
      "data" => [
        "ticket"  => (object)["token" => $ticket_token]
      ],
      "iat" => time(),
      "exp" => time() + ($lifespan) // 寿命を与える
    ];
    */
    
    // 署名生成
    return JWT::encode($payload, getenv("jwt.secret.key"), getenv("jwt.signing.algorithm"));
  }
  
  /**
   * GetDecodedSignature
   * @param String $qrcode
   * @return Array any
   */
  public static function GetDecodedSignature(string $signature): array
  {
    $decoded = JWT::decode($signature, new Key(getenv("jwt.secret.key"), getenv("jwt.signing.algorithm")));
    return (array)$decoded;
  }
}