<?PHP
    $loader = new Phalcon\Loader();
    $loader->registerDirs(array(
        __DIR__.'/models/'
    ))->register();
    $di = new Phalcon\DI\FactoryDefault();
    $di->set('db',function(){
        return new Phalcon\Db\Adapter\Pdo\Mysql(array(
            'host'=>'localhost',
            'username'=>'api',
            'password'=>'sLzarCgKYJUBxjegjadZ76rD8eHqEMAY',
            'dbname'=>'robot'
        ));
    });
    $app = new Phalcon\Mvc\Micro($di);
    $app->get('/api/test',function(){
        echo json_encode(array('name'=>'hello'));
    });
    $app->get('/api/robots',function() use ($app){
        $phql = 'SELECT id,name FROM robots order by name';
        $robots = $app->modelsManager->executeQuery($phql);
        $data = array();
        foreach($robots as $robot) {
            $data[] = array(
                'id'=>$robot->id,
                'name'=>$robot->name
            );
        }
        echo json_encode($data);
    });
    $app->get('/api/robots/search/{name}',function($name) use($app){
        $phql = 'select * from robots where name like :name: order by name';
        $robots = $app->modelsManager->executeQuery($phql,array(
            'name'=>'%'.$name.'%'
        ));
        $data = array();
        foreach($robots as $robot){
            $data[] = array(
                'id'=>$robot->id ,
                'name'=>$robot->name
            );
        }
        echo json_encode($data);
    });
    $app->get('/api/robots/{id:[0-9]+}',function($id) use ($app){
        $phql = "select * from robots where id = :id:";
        $robot = $app->modelsManager->executeQuery($phql,array(
            'id'=>$id
        ))->getFirst();
        $response = new Phalcon\Http\Response();
        if ($robot == false) {
            $response->setJsonContent(array('status'=>'NOT-FOUND'));
        } else {
            $response->setJsonContent(array(
                'status'=>'FOUND' ,
                'data'=>array(
                'id'=>$robot->id,
                'name'=>$robot->name
                )
            ));
        }
        return $response;
    });
    $app->post('/api/robots',function() use ($app) {
        $robot = $app->request->getJsonRawBody();
        $phql = 'insert into robots (name,type,year) values(:name:,:type:,:year:)';
        $status = $app->modelsManager->executeQuery($phql,array(
            'name'=>$robot->name,
            'type'=>$robot->type,
            'year'=>$robot->year
        ));
        $response = new Phalcon\Http\Response();
        if ($status->success() == true) {
            $response->setStatusCode(200,"Created");
            $robot->id = $status->getModel()->id;
            $response->setJsonContent(array('status'=>"OK",'data'=>$robot));
        } else {
            $response->setStatusCode(400,"Conflict");
            $errors = array();
            foreach($status->getMessages() as $message){
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(array('status'=>'ERROR','messages'=>$errors));
        }
        return $response;
    });
    $app->put('/api/robots/{id:[0-9]+}',function($id) use ($app){
        $robot = $app->request->getJsonRawBody();
        $phql = 'update robots set name = :name:,type = :type:,year = :year: where id = :id:';
        $status = $app->modelsManager->executeQuery($phql,array(
            'id'  =>$id,
            'name'=>$robot->name,
            'type'=>$robot->type,
            'year'=>$robot->year
        ));
        $response = new Phalcon\Http\Response();
        if ($status->success() == true) {
            $response->setJsonContent(array('status'=>"OK"));
        } else {
            $response->setStatusCode(400,"Conflict");
            $errors = array();
            foreach($status->getMessages() as $message){
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(array('status'=>'ERROR','messages'=>$errors));
        }
        return $response;
    });
    $app->delete('/api/robots/{id:[0-9]+}',function($id) use ($app){
        $phql = 'delete from robots where id = :id:';
        $status = $app->modelsManager->executeQuery($phql,array(
            'id'=>$id
        ));
        $response = new Phalcon\Http\Response();
        if ($status->success()) {
            $response->setJsonContent(array('status'=>'OK'));
        } else {
            $response->setStatusCode(200,"Created");
            $errors = array();
            foreach($status->getMessages() as $message){
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(array('status'=>'ERROR','messages'=>$errors));
        }
        return $response;
    });
    $app->handle();
?>
