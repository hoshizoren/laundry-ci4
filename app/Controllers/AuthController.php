<?php

namespace App\Controllers;

use App\Models\User;

class AuthController extends BaseController
{
  protected $User;

  public function __construct()
  {
    $this->User = new User();
  }

  public function login()
  {
    $session = session();
    $session_id = $session->get('id');
    $session_name = $session->get('name');
    $session_role = $session->get('role');

    if (is_string($session_id) && is_string($session_name) && is_string($session_role)) {
      return redirect()->to('/dashboard');
    }

    $data['title'] = 'Login Page';
    $data['validation'] = \Config\Services::validation();
    return view('auth/login', $data);
  }

  public function auth()
  {
    if (!$this->validate([
      'username' => 'required',
      'password' => [
        'rules' => 'required|min_length[4]',
        'label' => 'password'
      ]
    ])) {
      return redirect()->to('/')->withInput()->with('validation', $this->validator);
    }

    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    if (is_null($username) || is_null($password) || is_array($username) || is_array($password)) {
      session()->setFlashdata('error', 'Invalid input');
      return redirect()->to('/')->withInput();
    }

    $findUser = $this->User->where('username', $username)->first();
    if (!$findUser) {
      session()->setFlashdata('error', 'User doesn\'t exist');
      return redirect()->to('/')->withInput();
    }

    if (!password_verify($password, $findUser['password'])) {
      session()->setFlashdata('error', 'Invalid password');
      return redirect()->to('/')->withInput();
    }

    $session_data = [
      'id' => $findUser['id'],
      'name' => $findUser['display_name'],
      'role' => $findUser['role_id']
    ];
    session()->set($session_data);
    return redirect()->to('/dashboard');
  }

  public function logout()
  {
    session()->destroy();
    return redirect()->to('/');
  }

  public function blocked()
  {
    $data['title'] = '403 - Restricted';
    return view('auth/403', $data);
  }

  public function notfound()
  {
    $data['title'] = '404 - Not Found';
    return view('auth/404', $data);
  }
}
