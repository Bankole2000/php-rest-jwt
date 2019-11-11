<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// files for decoding jwt will be here
// required to encode json web token
include_once 'config/core.php';
include_once '../vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once '../vendor/firebase/php-jwt/src/ExpiredException.php';
include_once '../vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
 
// database connection will be here
// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate user object
$user = new User($db);
 
// retrieve given jwt here
// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// get jwt
$jwt=isset($data->jwt) ? $data->jwt : "";
 
// decode jwt here
// if jwt is not empty
if($jwt){
 
  // if decode succeed, show user details
  try {

      // decode jwt
      $decoded = JWT::decode($jwt, $key, array('HS256'));

      // set user property values here
      // set user property values
$user->firstname = $data->firstname;
$user->lastname = $data->lastname;
$user->email = $data->email;
$user->password = $data->password;
$user->id = $decoded->data->id;
 
// update user will be here
// update the user record
if($user->update()){
  // regenerate jwt will be here
  // we need to re-generate jwt because user details might be different
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
$jwt = JWT::encode($token, $key);

// set response code
http_response_code(200);

// response in json format
echo json_encode(
       array(
           "message" => "User was updated.",
           "jwt" => $jwt
       )
   );
}

// message if unable to update user
else{
  // set response code
  http_response_code(401);

  // show error message
  echo json_encode(array("message" => "Unable to update user."));
}
  }

  // catch failed decoding will be here
  // if decode fails, it means jwt is invalid
catch (Exception $e){
 
  // set response code
  http_response_code(401);

  // show error message
  echo json_encode(array(
      "message" => "Access denied.",
      "error" => $e->getMessage()
  ));
}
}

// error message if jwt is empty will be here
// show error message if jwt is empty
else{
 
  // set response code
  http_response_code(401);

  // tell the user access denied
  echo json_encode(array("message" => "Access denied."));
}
?>