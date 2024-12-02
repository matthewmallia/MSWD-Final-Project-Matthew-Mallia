<?php
require 'com/icemalta/kahuna/model/User.php';
require 'com/icemalta/kahuna/model/AccessToken.php';
require 'com/icemalta/kahuna/model/Product.php';
require 'com/icemalta/kahuna/model/Register.php';
require 'com/icemalta/kahuna/util/ApiUtil.php';

use com\icemalta\kahuna\model\{User, AccessToken, Product, Register};
use com\icemalta\kahuna\util\ApiUtil;

cors();

$endPoints = []; // This will be a list of the endpoints we support (URI + HTTP VERB)
$requestData = []; // Data we've received from the client when they make a request (ex with POST or PUT)
header("Content-Type: application/json; charset=UTF-8");

/* BASE URI */
$BASE_URI = '/kahuna/api/'; // Common part of all requests we receive

function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
{
    if (!is_null($data)) {
        $response['data'] = $data;
    }
    if (!is_null($error)) {
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    http_response_code($code);
}

function checkToken(array $requestData): bool 
{
    if (!isset($requestData['token']) || !isset($requestData['user'])) {
        return false;
    }
    $token = new AccessToken($requestData['user'], $requestData['token']);
    return AccessToken::verify($token);
}

// To allow cross origin requests 
function cors()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) { // localhost:8001
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}"); // localhost:8001
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // Cache for 1 day
    }

    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // Pre-flight request - browser is requesting additional permissions
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"); // Allow any headers to be sent
        }

        exit(0);
    }
}
/* This code retrieves the data which was sent along with a request, regardless of the 
the type of request (GET, POST, PUT, etc...) that was received */
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        $requestData = $_GET; 
        break;
    case 'POST':
        $requestData = $_POST;
        break;
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData); // load raw data
        ApiUtil::parse_raw_http_request($requestData); // Convert raw data into PHP format
        $requestData = is_array($requestData) ? $requestData : []; // If we receive nothing, set to an empty array
        break;
    case 'DELETE':
        break;
    default:
        sendResponse(null, 405, 'Method not allowed.');
}

/* 
    Extract EndPoint 
*/
$parsedURI = parse_url($_SERVER['REQUEST_URI']); // This give us the address we've received (1)
$path = explode('/', str_replace($BASE_URI, "", $parsedURI["path"])); // This gives us the endpoint (2)
$endPoint = $path[0];
$requestData['dataId'] = $path[1] ?? null; // This gives us the id (3), if any
if (empty($endPoint)) {
    // We haven't received an endpoint (client must have made a mistake)
    $endPoint = "/";
}

/* Extract the Headers */
if (isset($_SERVER["HTTP_X_API_USER"])) {
    $requestData["user"] = $_SERVER["HTTP_X_API_USER"];
}
if (isset($_SERVER['HTTP_X_API_KEY'])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}
if (isset($_SERVER['HTTP_X_ACCESS_LEVEL'])) {
    $requestData['accessLevel'] = $_SERVER['HTTP_X_ACCESS_LEVEL'];
}
/* EndPoint Handlers */

// This is just a simple test
$endPoints["/"] = function(string $requestMethod, array $requestData): void {
    sendResponse("Welcome to Kahuna API!");
};

// This is used when an invalid endpoint is specified
$endPoints["404"] = function (string $requestMethod, array $requestData): void {
    sendResponse(null, 404, "Endpoint " . $requestData["endPoint"] . " not found.");
};

// User Management Endpoints
$endPoints["user"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        // Register a new user account
        $email = $requestData['email'];
        $password = $requestData['password'];
        $firstName = $requestData['firstName'];
        $lastName = $requestData['lastName'];
        $accessLevel = $requestData['accessLevel'] ?? 'user'; // Default to 'user' if not specified
        $user = new User($email, $password, $firstName, $lastName, $accessLevel); // Creates new user with the required fields
        $user = User::save($user); // Save the user on tha DB
        sendResponse($user, 201); // CREATED
    } elseif ($requestMethod === 'PATCH') {
        sendResponse(null, 501, 'Updating a user has not been implemented yet.');
    } elseif ($requestMethod === 'DELETE') {
        sendResponse(null, 501, 'Deleting a user has not been implemented yet.');
    } elseif ($requestMethod === 'GET') {
       sendResponse(null, 501, 'Get a user has not been implemented yet.');
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

// Authentication Endpoints (Info provided according to $accessLevel, arranged a bit by ChatGpt)
$endPoints["login"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        // Get user credentials from requestData
        $email = $requestData['email'];
        $password = $requestData['password'];
        $firstName = ''; // Set as empty string
        $lastName = '';  // Set as empty string
        // Create a temporary user to authenticate with provided credentials
        $user = new User($email, $password, $firstName, $lastName, ''); // Temporary access level is not needed here
        $userAuthentication = User::authenticate($user);

        if ($userAuthentication) {
            // Access the authenticated user's existing access level
            $accessLevel = $requestData['accessLevel'] ?? $userAuthentication->getAccessLevel();

            // Generate an access token for this user's login
            $token = new AccessToken($userAuthentication->getId());
            $token = AccessToken::save($token);

            // Prepare the response based on accessLevel
            if ($accessLevel === 'admin') {
                sendResponse([
                    'user' => $userAuthentication->getId(),
                    'firstName' => $userAuthentication->getFirstName(),
                    'lastName' => $userAuthentication->getLastName(),
                    'email' => $userAuthentication->getEmail(),
                    'accessLevel' => $userAuthentication->getAccessLevel(),
                    'token' => $token->getToken()
                ]);
            } else {
                sendResponse([
                    'user' => $userAuthentication->getId(),
                    'firstName' => $userAuthentication->getFirstName(),
                    'lastName' => $userAuthentication->getLastName(),
                    'email' => $userAuthentication->getEmail(),
                    'token' => $token->getToken()
                ]);
            }
        } else {
            sendResponse(null, 401, 'Login failed.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endPoints["logout"] = function (string $requestMethod, array $requestData): void {
    
    if ($requestMethod === 'POST') {
        if (checkToken($requestData)) {
            $userId = $requestData['user'];
            $token = new AccessToken($userId);
            $token = AccessToken::delete($token);
            sendResponse('You have been logged out.');
        } else {
            sendResponse(null, 403, 'Missing, invalid or expired token.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endPoints["token"] = function (string $requestMethod, array $requestData): void {

    if ($requestMethod === 'GET') {
        sendResponse(['valid' => checkToken($requestData), 'token' => $requestData['token']]);
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

// Handling Product Endpoints
$endPoints["product"] = function (string $requestMethod, array $requestData): void {
    if (checkToken($requestData)) {
        // Check if the operator is an admin or a user
        $accessLevel = $requestData['accessLevel'] ?? 'user';
        // $userId = $requestData['userId']; // Assuming this comes from the token or session
        
        if ($requestMethod === 'GET') {
            // Retrieve product
            $id = $requestData['user'];
            if ($requestData['dataId']) {
                // Retrieve a specific to do is not supported yet
                sendResponse(null, 501, 'Getting a specific Product has not yet been implemented.');
            } else { 
                $product = new Product('', '', 0 , 0, 0);
                $products = Product::load($product);
                sendResponse($products);
            }
        } elseif ($requestMethod === 'POST') {
            // Add a product when ADMIN ONLY
            if ($accessLevel === 'admin') {
                $serialNo = $requestData['serialNo'];
                $productDesc = $requestData['productDesc'];
                $warranty = $requestData['warranty'];
                $product = new Product($serialNo, $productDesc, $warranty);
                $product = Product::save($product); // Saves product to DB
                sendResponse($product, 201);
            } else {
                sendResponse(null, 405, 'Unauthorised Access To Add New Products!');
            }
        } elseif ($requestMethod === 'PATCH') {
            // Modify product when ADMIN ONLY
            if ($accessLevel === 'admin') {
                $serialNo = $requestData['serialNo'];
                $productDesc = $requestData['productDesc'];
                $warranty = $requestData['warranty'];
                $id = $requestData['id'];
                $product = new Product($serialNo, $productDesc, $warranty, 0, $id);
                $product = Product::save($product);
                sendResponse($product);
            } else {
                sendResponse(null, 405, 'Unauthorised Access To Modify Products!');
            }
        } elseif ($requestMethod === 'DELETE') {
            // Delete Product when ADMIN ONLY
            if ($accessLevel === 'admin') {
            if (empty($requestData['dataId'])) {
                sendResponse(null, 400, 'Product ID is required.');
            } else {
                $id = $requestData['dataId'];
                $product = new Product(id: $id);
                if (Product::delete($product)) {
                    sendResponse('Product deleted successfully.');
                } else {
                    sendResponse(null, 404, 'Product not found.');
                }
            }} else {
                sendResponse(null, 405, 'Unauthorised Access To Modify Products!');
            }
        } 
    } else {
        sendResponse(null, 405, 'Please log in to continue.');
    }
};

// Handling Register Endpoints
$endPoints["register"] = function (string $requestMethod, array $requestData): void {
    if (checkToken($requestData)) {
        if ($requestMethod === 'GET') {
            // Retrieve registered product
            $userId = (int) $requestData['user']; // Cast userId to integer
            if ($requestData['dataId']) {
                // Retrieve a specific Registered product is not supported yet
                sendResponse(null, 501, 'Getting a specific registered product has not yet been implemented.');
            } else {
                $register = new Register($userId);
                $registers = Register::load($register);
                sendResponse($registers);
            }
        } elseif ($requestMethod === 'POST') {
            // Add a registered product
            $userId = (int) $requestData['user']; 
            $serialNo = $requestData['serialNo'];
            $register = new Register($userId, $serialNo);
            $register = Register::save($register);
            sendResponse($register, 201);
        } else {
            sendResponse(null, 405, 'Method not allowed.');
        }
    } else {
        // You need to be logged in to register stuff
        sendResponse(null, 403, 'Missing, invalid or expired token.');
    }
};


// When a request is received, figure out which endPoint (from the $endPoints array) should handle it
try {
    if (isset($endPoints[$endPoint])) {
        $endPoints[$endPoint]($requestMethod, $requestData);
    } else {
        $endPoints["404"]($requestMethod, array("endPoint" => $endPoint));
    }
} catch (Exception $e) {
    sendResponse(null, 500, $e->getMessage());
} catch (Error $e) {
    sendResponse(null, 500, $e->getMessage());
}

