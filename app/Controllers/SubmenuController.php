<?php

namespace App\Controllers;

use App\Models\Menu;
use App\Models\Submenu;

class SubmenuController extends BaseController
{
  protected $menuModel;
  protected $submenuModel;

  public function __construct()
  {
    $this->menuModel = new Menu();
    $this->submenuModel = new Submenu();
  }

  public function index()
  {
    $keyword = $this->request->getVar('keyword');
    if ($keyword) {
      $submenu = $this->submenuModel->searchSubmenu($keyword);
    } else {
      $submenu = $this->submenuModel->getAllSubmenu();
    }

    $currentPage = $this->request->getVar('page') ?? 1;
    $data = [
      'title' => 'Submenu Management',
      'header' => 'Browse Submenu',
      'all_submenu' => $submenu->orderBy('submenu.display_name')->paginate(5),
      'pager' => $submenu->pager,
      'currentPage' => $currentPage,
      'perPage' => 5,
    ];

    return view('submenu/index', $data);
  }

  public function create()
  {
    $data = [
      'title' => 'Menu Management',
      'header' => 'Add Submenu',
      'validation' => \Config\Services::validation(),
      'all_menu' => $this->menuModel->select('id, display_name')->where('type', 'dynamic')->findAll()
    ];

    return view('submenu/create', $data);
  }

  public function save()
  {
    if (!$this->validate($this->submenuRules())) {
      return redirect()->to('/tools/submenu/create')->withInput();
    }

    $data = [
      'menu_id' => $this->request->getPost('menu_id'),
      'name' => $this->request->getPost('name'),
      'display_name' => $this->request->getPost('display_name'),
      'url' => $this->request->getPost('url') ?? null,
      'is_active' => $this->request->getPost('is_active') ? 1 : 0
    ];

    $this->submenuModel->save($data);

    session()->setFlashdata('success', 'Data berhasil disimpan');
    return redirect()->to('/tools/submenu');
  }

  public function edit($submenuID)
  {
    $data = [
      'title' => 'Submenu Management',
      'header' => 'Submenu Detail',
      'submenu' => $this->submenuModel->find($submenuID),
      'all_menu' => $this->menuModel->select('id, display_name')->where('type', 'dynamic')->findAll(),
      'validation' => \Config\Services::validation()
    ];

    return view('submenu/edit', $data);
  }

  public function update($submenuID)
  {
    if (!$this->validate($this->_rules($submenuID))) {
      return redirect()->to('/tools/submenu/edit/' . $submenuID)->withInput();
    }

    $data = [
      'id' => $submenuID,
      'menu_id' => $this->request->getPost('menu_id'),
      'name' => $this->request->getPost('name'),
      'display_name' => $this->request->getPost('display_name'),
      'url' => $this->request->getPost('url') ?? null,
      'is_active' => $this->request->getPost('is_active') ? 1 : 0,
    ];

    $this->submenuModel->save($data);

    session()->setFlashdata('success', 'Data berhasil diubah!');
    return redirect()->to('/tools/submenu');
  }

  public function delete($submenuID)
  {
    $this->submenuModel->delete($submenuID);
    session()->setFlashdata('success', 'Data berhasil dihapus!');
    return redirect()->to('/tools/submenu');
  }

  private function _rules($id = null)
  {
    return [
      'name' => [
        'label' => 'submenu name',
        'rules' => "required|regex_match[/^[a-z-]*$/]|is_unique[submenu.name,id,$id]",
        'errors' => [
          'required' => '{field} is required!',
          'regex_match' => '{field} must be only lowercase letter and can contain dash (-).',
          'is_unique' => '{field} already exists!'
        ]
      ],
      'display_name' => [
        'label' => 'display name',
        'rules' => 'required',
        'errors' => [
          'required' => '{field} is required!'
        ]
      ],
    ];
  }

  private function submenuRules()
  {
    return [
      'name' => [
        'label' => 'submenu name',
        'rules' => 'required|regex_match[/^[a-z-]*$/]|is_unique[submenu.name]',
        'errors' => [
          'required' => '{field} is required!',
          'regex_match' => '{field} must be only lowercase letters and can contain dash (-).',
          'is_unique' => '{field} already exists!'
        ]
      ],
      'display_name' => [
        'label' => 'display name',
        'rules' => 'required',
        'errors' => [
          'required' => '{field} is required!'
        ]
      ],
    ];
  }
}
