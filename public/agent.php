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

$app->get('/get', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    if(!$conn->connect_error) {
        $query = "SELECT a.* , c.name as coverage_name FROM agent a JOIN coverage c on c.coverage_id = a.coverage_id";

        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $data['status'] = 'success';
            while($row = $result->fetch_assoc()) {
                $data['data'][] = $row;
            }
        } else {
            $data['status'] = 'error - not found';
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->get('/detail', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    $id = $request->get('agent_id');

    if(!$conn->connect_error) {
        $query = "SELECT a.* , c.name as coverage_name FROM agent a JOIN coverage c on c.coverage_id = a.coverage_id
 WHERE a.agent_id = $id";

        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $data['status'] = 'success';
            $row = $result->fetch_assoc();
            $data['data'] = $row;
        } else {
            $data['status'] = 'error - not found';
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

    $coverage_id = $request->get('coverage_id');
    $identity_number = $request->get('identity_number');
    $name = $request->get('name');
    $address = $request->get('address');
    $city = $request->get('city');
    $zip_code = $request->get('zip_code');
    $password = password_hash($request->get('password'),PASSWORD_DEFAULT);

    if(!$conn->connect_error) {
        $query = "INSERT INTO agent
(coverage_id, identity_number, name, address, city, zip_code, password)
values ($coverage_id, $identity_number, '$name', '$address', '$city', '$zip_code', '$password')";

        $result = $conn->query($query) or die(mysqli_error($conn));

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


$app->post('/update', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $agent_id = $request->get('agent_id');
    $coverage_id = $request->get('coverage_id');
    $identity_number = $request->get('identity_number');
    $name = $request->get('name');
    $address = $request->get('address');
    $city = $request->get('city');
    $zip_code = $request->get('zip_code');
//  $password = password_hash($request->get('password'),PASSWORD_DEFAULT);

    if(!$conn->connect_error) {
        $query = "UPDATE agent SET coverage_id = $coverage_id, identity_number = $identity_number name = '$name', address = '$address',
city = '$city', zip_code = '$zip_code' WHERE agent_id = '$agent_id'";


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

$app->post('/login', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $identity_number = trim($request->get('identity_number'));
    $password = $request->get('password');

    if(!$conn->connect_error) {

        $query = "SELECT a.* , c.name as coverage_name FROM agent a JOIN coverage c on c.coverage_id = a.coverage_id
 WHERE a.identity_number = '$identity_number'";

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

$app->post('/updatePassword', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $agent_id = $request->get('agent_id');
    $password = $request->get('password');
    $new_password = password_hash($request->get('new_password'), PASSWORD_DEFAULT);

    if(!$conn->connect_error) {

        $query = "SELECT * FROM agent a WHERE a.agent_id = $agent_id";

        $result = $conn->query($query) or die(mysqli_error($conn));

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();

            if(password_verify($password, $row['password'])){

                $query_update = "UPDATE agent SET password = '$new_password' WHERE agent_id = $agent_id";

                $result = $conn->query($query_update) or die(mysqli_error($conn));
                //$result = $conn->query($query);

                if($result)
                {
                    $data['status'] = 'ok';
                }

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

$app->run();