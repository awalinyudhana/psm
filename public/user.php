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
    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE u.status = 1";

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


$app->get('/coverage', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed',
      'data' => array()
  );

  $coverage_id = $request->get('coverage_id');

  if(!$conn->connect_error) {
    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE u.status = 1 and u.coverage_id = $coverage_id";

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

$app->get('/pending', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed',
      'data' => array()
  );

  if(!$conn->connect_error) {
    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE u.status = 0";

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


$app->get('/rejected', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed',
      'data' => array()
  );

  if(!$conn->connect_error) {
    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE u.status = 2";

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

  $id = $request->get('users_id');

  if(!$conn->connect_error) {
    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE u.users_id = '$id'";

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
  $email = $request->get('email');

  if(!$conn->connect_error) {
    $query = "INSERT INTO users
(coverage_id, identity_number, name, address, email)
values ('$coverage_id', '$identity_number', '$name', '$address', '$email')";


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

$app->post('/update', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $users_id = $request->get('users_id');
  $coverage_id = $request->get('coverage_id');
  $name = $request->get('name');
  $address = $request->get('address');

  if(!$conn->connect_error) {
    $query = "UPDATE users SET coverage_id = $coverage_id, name = '$name', address = '$address'
WHERE users_id = $users_id";

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

$app->get('/check', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $email = trim($request->get('email'));

  if(!$conn->connect_error) {

    $query = "SELECT u.*, c.name as coverage_name FROM users u JOIN coverage c on c.coverage_id = u.coverage_id
WHERE u.email = '$email'";

    $result = $conn->query($query) or die(mysqli_error($conn));

    if($result->num_rows > 0)
    {
      $data['status'] = 'success';
      $row = $result->fetch_assoc();
      $data['data'] = $row;

    }else{
        $data = ['status' => 'error', 'message' => 'user not found'];
    }
  }

  $dataJson = json_encode($data);
  return new Response($dataJson, 200, array(
      'Content-Type' => 'application/json'
  ));
});

$app->get('/approve', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $id = $request->get('users_id');
  $date_approved = date('Y-m-d h:i:s');

  if(!$conn->connect_error) {
    $query = "UPDATE users SET status = '1', date_approved = '$date_approved' WHERE users_id = '$id'";

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

$app->get('/disable', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $id = $request->get('users_id');

  if(!$conn->connect_error) {
    $query = "UPDATE users SET status = '0' WHERE users_id = '$id'";

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

$app->get('/alert', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $id = $request->get('users_id');

  if(!$conn->connect_error) {
    $query = "UPDATE users SET alert_status = 1 WHERE users_id = '$id'";

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

$app->get('/reject', function(Request $request) use($app)
{
  $conn = konekDb();
  $data = array(
      'status'  => 'error - db failed'
  );

  $id = $request->get('users_id');
  $note = $request->get('note');

  if(!$conn->connect_error) {
    $query = "UPDATE users SET status = '2', note = '$note' WHERE users_id = '$id'";

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