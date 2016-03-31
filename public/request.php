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

$app->post('/request', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $users_id = $request->get('users_id');

    if(!$conn->connect_error) {
        $query = "INSERT INTO request
(users_id)
values ($users_id)";

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



$app->post('/pick', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $request_id = $request->get('request_id');
    $agent_id = $request->get('agent_id');
    $date_pick = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {

        if (!$conn->connect_error) {

            $query_update = "UPDATE request r SET r.status = 1, r.agent_id = $agent_id, r.date_pick = '$date_pick'
 WHERE r.request_id = $request_id";

            $result = $conn->query($query_update) or die(mysqli_error($conn));
            //$result = $conn->query($query);

            if ($result) {
                $data['status'] = 'ok';
            }

        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->get('/get', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );
    $agent_id = $request->get('agent_id');

    if(!$conn->connect_error) {
        $query = "SELECT r.*,  u.*m u.name as users_name, c.name as coverage_name FROM request r
JOIN users u ON u.users_id = r.users_id
JOIN coverage c on c.coverage_id = u.coverage_id
 WHERE r.status = 0 AND c.coverage = $agent_id";

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

$app->run();