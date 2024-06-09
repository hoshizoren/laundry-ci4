<?php

namespace App\Controllers;

use App\Models\Role;
use App\Models\User;

class ProfileController extends BaseController
{
    protected $User;

    public function __construct()
    {
        $this->User = new User();
    }

    public function show($id)
    {
        $user = $this->User->getUserDetail($id)->first();
        if (!$user) {
            return redirect()->to('/404');
        }
        
        $data = [
            'title' => 'Profile',
            'header' => 'User Profile',
            'user' => $user
        ];
        
        return view('users/profile', $data);
    }

    public function edit($id)
    {
        if (session('id') != $id) {
            return redirect()->to('/403');
        }
        
        $roles = new Role();
        $data = [
            'title' => 'Profile',
            'header' => 'Edit Profile',
            'user' => $this->User->getUserDetail($id)->first(),
            'validation' => service('validation'),
            'roles' => $roles->findAll()
        ];
        
        return view('users/edit_profile', $data);
    }

    public function update($id)
    {
        if (session('id') != $id) {
            return redirect()->to('/403');
        }
        
        if (!$this->validate($this->_profileRules($id))) {
            return redirect()->to('/profile/edit/' . $id)->withInput();
        }

        $data = [
            'id' => $id,
            'role_id' => $this->request->getPost('role_id'),
            'username' => $this->request->getPost('username'),
            'display_name' => $this->request->getPost('display_name'),
            'email' => $this->request->getPost('email') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
        ];
        
        $this->User->save($data);

        session()->setFlashdata('success', 'Profile successfully updated!');
        return redirect()->to('/profile/' . $id);
    }

    public function editpass($id)
    {
        $data = [
            'title' => 'Profile',
            'header' => 'Change Password',
            'validation' => service('validation'),
            'user' => $this->User->find($id)
        ];
        
        return view('users/change_password', $data);
    }

    public function changepass($id)
    {
        if (!$this->validate($this->_passwordRules())) {
            return redirect()->to('/profile/changepass/' . $id)->withInput();
        }

        $old_password = $this->request->getPost('current');
        $user = $this->User->find($id);
        
        if (!password_verify($old_password, $user->password)) {
            session()->setFlashdata('error', 'Incorrect current password!');
            return redirect()->to('/profile/changepass/' . $id)->withInput();
        }

        $this->User->update($id, ['password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)]);

        session()->setFlashdata('success', 'Password successfully changed!');
        return redirect()->to('/profile/' . $id);
    }

    private function _profileRules($id = null)
    {
        return [
            'username' => [
                'rules' => "required|is_unique[users.username,id,$id]",
                'errors' => [
                    'required' => '{field} is required!',
                    'is_unique' => '{field} is already taken!'
                ]
            ],
            'display_name' => [
                'label' => 'Name',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} is required!',
                ]
            ],
            'role_id' => [
                'label' => 'Role',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} is required!'
                ]
            ],
        ];
    }

    private function _passwordRules()
    {
        return [
            'current' => [
                'label' => 'Current Password',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} is required!'
                ]
            ],
            'password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[4]',
                'errors' => [
                    'required' => '{field} is required!',
                    'min_length' => '{field} is too short! (minimum 4 characters)'
                ]
            ],
            'confirmation' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} is required!',
                    'matches' => '{field} does not match!'
                ]
            ],
        ];
    }
}
