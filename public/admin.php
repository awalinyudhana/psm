<?php


date_default_timezone_set("Asia/Bangkok");

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

require_once '../vendor/autoload.php';
require_once 'global.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->post('/login', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $username = trim($request->get('username'));
    $password = $request->get('password');

    if(!$conn->connect_error) {

        $query = "SELECT * FROM admin a WHERE a.username = '$username'";

        $result = $conn->query($query) or die(mysqli_error($conn));

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();

            if(password_verify($password, $row['password'])){
                $data = ['status' => 'ok'];
            }else{
                $data = ['status' => 'error', 'message' => 'please check your password'];
            }
        }else{
            $data = ['status' => 'error', 'message' => 'username not found'];
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->post('/insert', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $username = $request->get('username');
    $name = $request->get('name');
    $password = password_hash($request->get('password'),PASSWORD_DEFAULT);

    if(!$conn->connect_error) {
        $query = "INSERT INTO admin
(username, name, password)
values ('$username', '$name', '$password')";

        $result = $conn->query($query) or die(mysqli_error($conn));
        //$result = $conn->query($query);

        if($result)
        {
            $data['status'] = 'ok';
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->run();