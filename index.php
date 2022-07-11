<?php
include 'config.php';
include 'headers.php';
require "verif_auth.php";


if ($_SERVER['REQUEST_METHOD'] == 'GET') :
    if( isset($_GET['id_posts'])) :
        $sql = sprintf("SELECT * FROM posts LEFT JOIN users ON posts.id_user = users.id_users WHERE id_question = %d",
            $_GET['id_posts']
        );
        $response['response'] = 'One presponse to post whit id '.$_GET['id_posts'];
    else :
        $sql = "SELECT * FROM posts LEFT JOIN users ON posts.id_user = users.id_users WHERE id_question IS NULL ORDER BY date DESC";
        $response['response'] = 'All posts';
    endif;

    $result = $connect->query($sql);
    echo $connect->error;

    $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
    $response['nb_hits'] = $result->num_rows;
endif; //end GET

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') :
    if( isset($_GET['id_posts']) ) :
    $sql = sprintf("DELETE FROM posts WHERE id_posts=%d",
        $_GET['id_posts']
    );
    $connect->query($sql);
    echo $connect->error;
    $response['response'] = "Suppression post id {$_GET['id_posts']}";
    else :
        $response['response'] = "Il manque l'id";
        $response['code'] = 500;
    endif;
    //exit;
endif;

if ($_SERVER['REQUEST_METHOD'] == 'POST') :
    //extraction de l'objet json du paquet HTTP
    $json = file_get_contents('php://input');
    //décodage du format json, ça génère un object PHP
    $objectPOST = json_decode($json);
    $sql = sprintf("INSERT INTO posts SET titre='%s', contenu='%s', date='%s'",
        strip_tags(addslashes($objectPOST->nom)), //lire une propriété d'un objet PHP
        strip_tags(addslashes($objectPOST->prenom)),
        date('Y-m-d')
    );
    /*
    $sql = sprintf("INSERT INTO personnes SET nom='%s', prenom='%s'",
        $_POST['nom'], 
        $_POST['prenom']
    );
    */
    $connect->query($sql);
    echo $connect->error;
    $response['response'] = "Ajout un post avec id " . $connect->insert_id;
    $response['new_id'] = $connect->insert_id;
    //exit;
endif; //END POST

if ($_SERVER['REQUEST_METHOD'] == 'PUT') :
    //extraction de l'objet json du paquet HTTP
    $json = file_get_contents('php://input');
    //décodage du format json, ça génère un object PHP
    //$objectPOST = json_decode($json);
    $arrayPOST = json_decode($json, true);

    if( isset($arrayPOST['titre']) AND isset($arrayPOST['contenu'])) :
        $sql = sprintf("UPDATE posts SET titre='%s', contenu='%s' WHERE id_posts= %d",
            strip_tags(addslashes($arrayPOST['titre'])), //lire une propriété d'un objet PHP
            strip_tags(addslashes($arrayPOST['contenu'])),
            $_GET['id_posts']
        );
        $connect->query($sql);
        echo $connect->error;
        $response['response'] = "Edit un post avec id " . $_GET['id_posts'];
        $response['new_data'] = $arrayPOST;
    else :
        $response['response'] = "Il manque des données";
        $response['code'] = 500;
    endif;
endif; //END PUT

$response['code'] = ( isset($response['code']) ) ? $response['code'] : 200;

$response['time'] = date('Y-m-d,H:i:s');

echo json_encode($response);