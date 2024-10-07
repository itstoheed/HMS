<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$app = new \Slim\App();
$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('views', [
        'cache' => false
    ]);

    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container['db'] = function ($container) {
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'hms',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);

    $capsule->setEventDispatcher(new Dispatcher(new Container));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
});

$app->post('/doctors', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $email = $data['email'];
    $doctorname = $data['doctorname'];
    $dept = $data['dept'];

    $doctor = new Doctor();
    $doctor->email = $email;
    $doctor->doctorname = $doctorname;
    $doctor->dept = $dept;
    $doctor->save();

    // Flash message logic

    return $response->withRedirect('/doctors');
});

$app->post('/patients', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $email = $data['email'];
    $name = $data['name'];
    $gender = $data['gender'];
    $slot = $data['slot'];
    $disease = $data['disease'];
    $time = $data['time'];
    $date = $data['date'];
    $dept = $data['dept'];
    $number = $data['number'];

    $patient = new Patients();
    $patient->email = $email;
    $patient->name = $name;
    $patient->gender = $gender;
    $patient->slot = $slot;
    $patient->disease = $disease;
    $patient->time = $time;
    $patient->date = $date;
    $patient->dept = $dept;
    $patient->number = $number;
    $patient->save();

    // Flash message logic

    return $response->withRedirect('/patients');
});

$app->get('/bookings', function ($request, $response, $args) {
    $em = $this->db->getCurrentUser()->email;
    if ($this->db->getCurrentUser()->usertype == "Doctor") {
        $query = Patients::all();
        return $this->view->render($response, 'booking.html', ['query' => $query]);
    } else {
        $query = Patients::where('email', $em)->get();
        return $this->view->render($response, 'booking.html', ['query' => $query]);
    }
});

$app->post('/edit/{pid}', function ($request, $response, $args) {
    $pid = $args['pid'];
    $data = $request->getParsedBody();
    $email = $data['email'];
    $name = $data['name'];
    $gender = $data['gender'];
    $slot = $data['slot'];
    $disease = $data['disease'];
    $time = $data['time'];
    $date = $data['date'];
    $dept = $data['dept'];
    $number = $data['number'];

    $patient = Patients::find($pid);
    $patient->email = $email;
    $patient->name = $name;
    $patient->gender = $gender;
    $patient->slot = $slot;
    $patient->disease = $disease;
    $patient->time = $time;
    $patient->date = $date;
    $patient->dept = $dept;
    $patient->number = $number;
    $patient->save();

    // Flash message logic

    return $response->withRedirect('/bookings');
});

$app->post('/delete/{pid}', function ($request, $response, $args) {
    $pid = $args['pid'];

    $patient = Patients::find($pid);
    $patient->delete();

    // Flash message logic

    return $response->withRedirect('/bookings');
});

$app->get('/signup', function ($request, $response, $args) {
    // Handle user signup
});


$app->post('/login', function ($request, $response, $args) {
    // Handle user login
});

$app->get('/logout', function ($request, $response, $args) {
    // Handle user logout
});

$app->get('/pdetails', function ($request, $response, $args) {
    $posts = Trigr::all();
    return $this->view->render($response, 'trigers.html', ['posts' => $posts]);
});

$app->get('/ddetails', function ($request, $response, $args) {
    $posts = Doctors::all();
    return $this->view->render($response, 'ddetail.html', ['posts' => $posts]);
});

$app->post('/search', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $query = $data['search'];
    $dept = Doctors::where('dept', $query)->first();
    $name = Doctors::where('doctorname', $query)->first();
    if ($dept) {
        // Flash "Doctor is Available"
    } else {
        // Flash "Enu Lahore le Jao"
    }
    return $response->withRedirect('/');
});

$app->run();
