<?php

namespace App\Controllers;

use App\Models\Role;
use App\Models\User;

class UserController extends BaseController
{
  protected $roleModel;
  protected $userModel;

  public function __construct()
  {
    $this->roleModel = new Role();
    $this->userModel = new User();
  }

  public function index()
  {
    $keyword = $this->request->getVar('keyword');
    $currentPage = $this->request->getVar('page') ?? 1;

    if ($keyword) {
      $users = $this->userModel->searchUser($keyword);
    } else {
      $users = $this->userModel->getAllUsers();
    }

    $data = [
      'title' => 'User Management',
      'header' => 'Browse Users',
      'users' => $users->orderBy('users.display_name')->paginate(5),
      'pager' => $users->pager,
      'currentPage' => $currentPage,
      'perPage' => 5
    ];

    return view('users/index', $data);
  }

  public function create()
  {
    $data = [
      'validation' => service('validation'),
      'title' => 'User Management',
      'header' => 'Add User',
      'roles' => $this->roleModel->orderBy('display_name', 'ASC')->findAll()
    ];

    return view('users/create', $data);
  }

  public function save()
  {
    if (!$this->validate('userRules')) {
      return redirect()->to('/users/create')->withInput();
    }

    $data = [
      'role_id' => $this->request->getPost('role_id'),
      'username' => $this->request->getPost('username'),
      'display_name' => $this->request->getPost('display_name'),
      'email' => $this->request->getPost('email') ?? null,
      'phone' => $this->request->getPost('phone') ?? null,
      'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
    ];

    $this->userModel->save($data);

    session()->setFlashdata('success', 'Berhasil menambahkan user!');
    return redirect()->to('/users');
  }

  public function detail($userID)
  {
    $data = [
      'title' => 'User Management',
      'header' => 'Detail User',
      'user' => $this->userModel->getUserDetail($userID)->first()
    ];

    return view('users/detail', $data);
  }

  public function edit($userID)
  {
    $data = [
      'title' => 'User Management',
      'header' => 'Edit User',
      'validation' => service('validation'),
      'user' => $this->userModel->getUserDetail($userID)->first(),
      'roles' => $this->roleModel->findAll()
    ];

    return view('users/edit', $data);
  }

  public function update($userID)
  {
    if (!$this->validate($this->_rules($userID))) {
      return redirect()->to('/users/edit/' . $userID)->withInput();
    }

    $data = [
      'id' => $userID,
      'role_id' => $this->request->getPost('role_id'),
      'username' => $this->request->getPost('username'),
      'display_name' => $this->request->getPost('display_name'),
      'email' => $this->request->getPost('email') ?? null,
      'phone' => $this->request->getPost('phone') ?? null,
    ];

    $this->userModel->save($data);

    session()->setFlashdata('success', 'Data berhasil diubah!');
    return redirect()->to('/users/detail/' . $userID);
  }

  public function delete($userID)
  {
    $this->userModel->delete($userID);
    session()->setFlashdata('success', 'User berhasil dihapus!');
    return redirect()->to('/users');
  }

  public function reset($userID)
  {
    $data = [
      'title' => 'Reset Password',
      'validation' => service('validation'),
      'user' => $this->userModel->select('id, username, display_name')->find($userID)
    ];

    return view('users/reset', $data);
  }

  public function changepass($userID)
  {
    if (!$this->validate([
      'password' => 'required|min_length[4]',
      'confirmation' => 'required|matches[password]',
    ])) {
      return redirect()->to('/users/reset/' . $userID)->withInput();
    }

    $this->userModel->update($userID, ['password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)]);

    session()->setFlashdata('success', 'Password berhasil diubah!');
    return redirect()->to('/users/detail/' . $userID);
  }

  private function _rules($id = null)
  {
    return [
      'role_id' => [
        'label' => 'Role',
        'rules' => 'required',
        'errors' => [
          'required' => '{field} is required!'
        ]
      ],
      'username' => [
        'rules' => "required|is_unique[users.username,id,$id]",
        'errors' => [
          'required' => '{field} is required!',
          'is_unique' => '{field} already exists!',
        ]
      ],
      'display_name' => [
        'label' => 'Display Name',
        'rules' => 'required',
        'errors' => [
          'required' => '{field} is required!'
        ]
      ],
    ];
  }
}
