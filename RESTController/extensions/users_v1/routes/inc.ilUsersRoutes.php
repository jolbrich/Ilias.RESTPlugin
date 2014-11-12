<?php


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// users
$app->get('/v1/users', 'authenticateILIASAdminRole', function () use ($app) {
    try {

        $limit = 10;
        $offset = 0;

        $result = array();
        $usr_model = new ilUsersModel();

        $fields = array('login','email');
        $request = $app->request();
        $reqFields = $request->params('fields');
        if (isset($reqFields)){
            $fields = explode(",",$reqFields);
        }
        if ($request->params('limit')){
            $limit = $request->params('limit');
        }
        if ($request->params('offset')){
            $offset = $request->params('offset');
        }
        $result['_metadata']['limit'] = $limit;
        $result['_metadata']['offset'] = $offset;
        $all_users = $usr_model->getAllUsers($fields);
        $totalCount = count($all_users);
        $result['_metadata']['totalCount'] = $totalCount;
        // TODO: Sanity check on $offset parameter

        for ($i = $offset; $i<min($totalCount, $offset+$limit); $i++) {
            $current_user = array('user'=>$all_users[$i]);
            $result['users'][] = $current_user;
        }

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

$app->get('/v1/users/:user_id', 'authenticateTokenOnly', function ($user_id) use ($app) {
    try {
        $env = $app->environment();
        $id = $user_id;
        if ($user_id == "mine") {
            $id = ilRestLib::loginToUserId($env['user']);
        }
        $result = array();
        // $result['usr_id'] = $user_id;
        $usr_model = new ilUsersModel();
        $usr_basic_info =  $usr_model->getBasicUserData($id);
        $result['user'] = $usr_basic_info;

        // if (($mediaType == 'application/json'))
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// bulk import via XML
// consumes the schema that is produced by Administration -> Users -> Export
$app->post('/v1/users', 'authenticateILIASAdminRole', function() use ($app) {
  $request = $app->request();
  $importData = $request->getBody();

  $model = new ilUsersModel();
  $import_result = $model->bulkImport($importData);

  echo json_encode($import_result);
});



/*
$app->post('/v1/users', 'authenticate', function () use ($app) { // create
    try { // root only

        $request = $app->request();
        $user = $request->params('username');
        $pass = $request->params('password');

        $user_data['login'] = $user;
        $user_data['passwd'] = $pass;

        $result = array();
        $usr_model = new ilUsersModel();
        $user_id = $usr_model->addUser($user_data);

        $status = true;

        if ($status == true) {
            $result['status'] = "User ".$user_id." created.";
        }else {
            $result['status'] = "User could not be created!";
        }

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});
*/

$app->put('/v1/users/:user_id', 'authenticate', function ($user_id) use ($app){ // update
    try {

        $usr_model = new ilUsersModel();
        $a_Requests = $app->request->put();

        foreach ($a_Requests as $key => $value) {
            $usr_model->updateUser($user_id, $key, $value);
        }

        $result = array();
        $result['status'] = 'success';
        $usr_basic_info =  $usr_model->getBasicUserData($user_id);
        $result['user'] = $usr_basic_info;
        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

$app->delete('/v1/users/:user_id', 'authenticate', function ($user_id) use ($app) {
    try {
        $result = array();
        $usr_model = new ilUsersModel();
        $status = $usr_model->deleteUser($user_id);

        if ($status == true) {
            $result['status'] = "User ".$user_id." deleted.";
        }else {
            $result['status'] = "User ".$user_id." not deleted!";
        }

        $app->response()->header('Content-Type', 'application/json');
        echo json_encode($result);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});
?>
