<?php

namespace App\Controllers;

use App\Models\Role;

class RoleController extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    public function index()
    {
        $keyword = $this->request->getVar('keyword');
        $role = $keyword ? $this->roleModel->like('name', $keyword)->orLike('display_name', $keyword) : $this->roleModel;

        $currentPage = $this->request->getVar('page');
        $data = [
            'title' => 'Roles Management',
            'header' => 'Browse Roles',
            'roles' => $role->orderBy('name', 'ASC')->paginate(5),
            'pager' => $role->pager,
            'currentPage' => $currentPage ? $currentPage : 1,
            'perPage' => 5
        ];
        return view('roles/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Roles Management',
            'header' => 'Add Role',
            'validation' => service('validation')
        ];
        return view('roles/create', $data);
    }

    public function save()
    {
        $validation = $this->validate($this->_rules());

        if (!$validation) {
            return redirect()->to('/roles/create')->withInput()->with('errors', service('validation')->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'display_name' => $this->request->getPost('display_name'),
        ];
        $this->roleModel->save($data);

        session()->setFlashdata('success', 'Data berhasil disimpan!');
        return redirect()->to('/roles');
    }

    public function detail($roleID)
    {
        $data = [
            'title' => 'Roles Management',
            'header' => 'Role Detail',
            'role' => $this->roleModel->find($roleID)
        ];
        return view('roles/detail', $data);
    }

    public function edit($roleID)
    {
        $data = [
            'title' => 'Roles Management',
            'header' => 'Edit Role',
            'role' => $this->roleModel->find($roleID),
            'validation' => service('validation')
        ];
        return view('roles/edit', $data);
    }

    public function update($roleID)
    {
        $validation = $this->validate($this->_rules($roleID));

        if (!$validation) {
            return redirect()->to('/roles/edit/' . $roleID)->withInput()->with('errors', service('validation')->getErrors());
        }

        $data = [
            'id' => $roleID,
            'name' => $this->request->getPost('name'),
            'display_name' => $this->request->getPost('display_name')
        ];
        $this->roleModel->save($data);

        session()->setFlashdata('success', 'Data berhasil diubah!');
        return redirect()->to('/roles/detail/' . $roleID);
    }

    public function delete($roleID)
    {
        $this->roleModel->delete($roleID);
        session()->setFlashdata('success', 'Data berhasil dihapus!');
        return redirect()->to('/roles');
    }

    private function _rules($id = null)
    {
        return [
            'name' => [
                'label' => 'Role Name',
                'rules' => "required|regex_match[/^[a-z-]*$/]" . ($id ? "|is_unique[roles.name,id,$id]" : "|is_unique[roles.name]"),
                'errors' => [
                    'required' => '{field} is required!',
                    'regex_match' => '{field} must only contain lowercase letters and dashes (-).',
                    'is_unique' => '{field} already exists!'
                ]
            ],
            'display_name' => [
                'label' => 'Display Name',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} is required!'
                ]
            ]
        ];
    }
}
