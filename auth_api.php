<?php
include 'config.php';
include 'headers.php';
session_start();

$response['script'] = 'auth_api.php';


if ( isset($_GET['delog']) ) :
    unset($_SESSION['user']);
    unset($_SESSION['token']);
    unset($_SESSION['expiration']);
    $response['response'] = "déconnection";
    $response['time'] = date('Y-m-d,H:i:s');
    $response['code'] = 200;
    echo json_encode($response);
    exit;
endif;

//connexion
$json = file_get_contents('php://input');
$arrayPOST = json_decode($json, true);

if ( !isset($arrayPOST['login']) OR !isset($arrayPOST['password'])) :
    $response['message'] = "Il manque login et/ou password";
    $response['code'] = 500;   
else:
    $sql = sprintf("SELECT * FROM users WHERE login = '%s' AND password = '%s'",
        $arrayPOST['login'],
        $arrayPOST['password']    
    );
    $result = $connect->query($sql);
    echo $connect->error;
    $nb_rows =  $result->num_rows;
    if($nb_rows == 0) :
        $response['message'] = 'error de log/pass';
        $response['code'] = 403;
    else :
        $row = $result->fetch_all(MYSQLI_ASSOC);
        $row = $row[0];
        //print_r($row);
        $_SESSION['user'] = $row['id_users'];
        $_SESSION['token'] = md5($row['login'].time());
        $_SESSION['expiration'] = time() + 1 * 600;
        $response['response'] = "OK connecté";
        $response['token'] = $_SESSION['token'];

        //$response['session'] = $_SESSION['user'];
        //exit;

    endif;
endif;

$response['code'] = ( isset($response['code']) ) ? $response['code'] : 200;

echo json_encode($response);
exit;