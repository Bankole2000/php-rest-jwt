<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// database connection will be here
// files needed to connect to database
include_once 'api/config/Database.php';
include_once 'api/objects/User.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate user object
$user = new User($db);
 
// check email existence here
// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// set product property values
$user->email = $data->email;
$email_exists = $user->emailExists();
 
// files for jwt will be here
// generate json web token
include_once 'api/config/core.php';
include_once 'vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once 'vendor/firebase/php-jwt/src/ExpiredException.php';
include_once 'vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once 'vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
 
// generate jwt will be here
// check if email exists and if password is correct
if($email_exists && password_verify($data->password, $user->password)){
 
  $token = array(
     "iss" => $iss,
     "aud" => $aud,
     "iat" => $iat,
     "nbf" => $nbf,
     "data" => array(
         "id" => $user->id,
         "firstname" => $user->firstname,
         "lastname" => $user->lastname,
         "email" => $user->email
     )
  );

  // set response code
  http_response_code(200);

  // generate jwt
  $jwt = JWT::encode($token, $key);
  echo json_encode(
          array(
              "message" => "Successful login.",
              "jwt" => $jwt
          )
      );

}

// login failed will be here
// login failed
else{
 
  // set response code
  http_response_code(401);

  // tell the user login failed
  echo json_encode(array("message" => "Login failed."));
}
?>