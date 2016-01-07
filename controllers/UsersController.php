<?php
namespace PhalconRest\Controllers;
use PhalconRest\Exceptions\HTTPException;
use PhalconRest\Models\Users;


class UsersController extends RESTController
{

    protected $allowedFields = array(
        'search' => array('full_name','user_type'),
        'partials' => array('full_name')
    );
    protected $excludeFields = array('password');
    protected $isSearch = true;


    function getModel()
    {
        return new Users();
    }

    function login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $user=$this->model->findFirst(array(
            "(email = :email:) AND password = :password:",
            'bind' => array('email' => $email, 'password' => md5($password))
        ));
        if ($user != false) {
           return $this->respond($user->toArray());
        }

        throw new HTTPException("Unauthorized",HTTPException::HTTP_UNAUTHORIZED);
    }




}
