<?php

namespace App\Controllers;

use App\Models\Menu;
use App\Models\MenuRole;
use App\Models\Role;

class MenuAccessController extends BaseController
{
  protected $Role;
  protected $Menu;
  protected $MenuRole;

  public function __construct()
  {
    $this->Role = new Role();
    $this->Menu = new Menu();
    $this->MenuRole = new MenuRole();
  }

  public function index()
  {
    $data = [
      'title' => 'Access Management',
      'header' => 'Browse Access',
      'roles' => $this->Role->orderBy('display_name', 'ASC')->findAll(),
      'all_menu' => $this->Menu->orderBy('display_name', 'ASC')->findAll()
    ];
    return view('access/index', $data);
  }

  public function edit($roleID)
  {
    $data = [
      'title' => 'Access Management',
      'header' => 'Browse Access',
      'role' => $this->Role->find($roleID),
      'all_menu' => $this->Menu->orderBy('display_name', 'ASC')->findAll()
    ];
    return view('access/edit', $data);
  }

  public function update($roleID)
  {
    $is_exist = $this->MenuRole->where('role_id', $roleID)->findAll();
    // Check if any menu_id is sent
    if (!$this->request->getPost('menu_id')) {
      // Check if role_id exists in the table
      if ($is_exist) {
        // If yes, delete data
        $this->MenuRole->where('role_id', $roleID)->delete();
        session()->setFlashdata('success', 'Access successfully updated!');
        return redirect()->to('/tools/access');
      }
      return redirect()->to('/tools/access');
    }

    // If menu_id is sent
    $menu_id = $this->request->getPost('menu_id');
    $data = [];
    foreach ($menu_id as $key => $m_id) {
      $data[] = [
        'menu_id' => $m_id,
        'role_id' => $roleID
      ];
    }

    // Check if role_id exists in the table
    if ($is_exist) {
      // If yes, delete data
      $this->MenuRole->where('role_id', $roleID)->delete();
    }

    // Insert batch new access
    $this->MenuRole->insertBatch($data);

    session()->setFlashdata('success', 'Access successfully updated!');
    return redirect()->to('/tools/access');
  }
}
