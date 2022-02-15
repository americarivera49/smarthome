<?php
require_once 'conexion.php';
require_once 'jwt.php';

/********BLOQUE DE ACCESO DE SEGURIDAD */
$headers = apache_request_headers();
$tmp = $headers["Authorization"];
$jwt = str_replace("Bearer ", "", $tmp);
if(JWT::verify($jwt, Config::SECRET) != 0){
    header("http/1.1 401 unauthorized");
    exit;
}

$user = JWT::get_data($jwt, Config::SECRET)["user"];
/*** BLOQUE WEB SERVICE REST */
$metodo = $_SERVER["REQUEST_METHOD"];
switch($metodo){
    case 'GET':
        $c = conexion();
        if(isset($_GET['id'])){
            $s = $c->prepare("SELECT * FROM registro WHERE id = :id");
            $s->bindValue(":id", $_GET['id']);
        }else{
            $s = $c->prepare("SELECT * FROM registro");
        }
        $s->execute();
        $s->setFetchMode(PDO::FETCH_ASSOC);
        $r = $s->fetchAll();
        header("http/1.1 200 ok");
        echo json_encode($r);
        break;
    case 'POST':
        if(isset($_POST['sensor']) && isset($_POST['valor'])){
            $c = conexion();
            $s = $c->prepare("INSERT INTO registro (user, sensor, valor, fecha) VALUES (:u, :s, :v, NOW())");
            $s->bindValue(":u", $user);
            $s->bindValue(":s", $_POST['sensor']);
            $s->bindValue(":v", $_POST['valor']);
            $s->execute();
            if($s->rowCount() > 0){
                header("http/1.1 201 created");
                echo json_encode(["add" => "y", "id" => $c->lastInsertId()]);
            }else{
                header("http/1.1 400 bad request");
                echo json_encode(["add" => "n"]);
            }
        }else{
            header("http/1.1 400 bad request");
            echo "Faltan datos";
        }
        break;
    case 'PUT':
        if (isset($_GET['id']) && isset($_GET['user']) && $_GET['sensor'] && isset($_GET['valor']) && isset($_GET['fecha'])){
            $c = conexion();
            $s = $c->prepare("UPDATE registro SET user = :u, sensor = :s, valor = :v, fecha = :fecha WHERE id = :id");
            $s-> bindValue (":u", $GET['user']);
            $s-> bindValue (":s", $GET['sensor']);
            $s-> bindValue (":v", $GET['valor']);
            $s-> bindValue (":f", $GET['fecha']);
            $s-> bindValue (":id", $GET['id']);
            $s->execute();
            if($s->rowCount() > 0){
                header("http/1.1 200 Ok");
                echo json_encode(array("updated" => "y"));
            }else{
                header("http/1.1 400 Bad request");
                echo json_encode(array("updated" => "n"));
            }
        }else {
            header("http/1.1 400 Bad request");
            echo "Faltan datos";
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])){
            $c = conexion();  
            $s = $c->prepare("DELETE FROM registro WHERE id = :id");
            $s->bindValue("id", $_GET['id']);
            $s->execute();
            if($s->rowCount() > 0){
                header("http/1.1 200 Ok");
                echo json_encode(array("delete" => "y"));
            }else{
                header("http/1.1 400 bad request");
                echo json_encode(array("delete" => "n"));
            }
        }else{
            header("http/1.1 400 bad request");
            echo "Faltan datos";
        }
        break;
    default:

}