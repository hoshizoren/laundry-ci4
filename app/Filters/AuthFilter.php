<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Database;

class AuthFilter implements FilterInterface
{
    protected $db;

    public function __construct()
    {
        // Initialize database
        $this->db = Database::connect();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Check user session
        $session = session();
        $session_id = $session->get('id');
        $session_name = $session->get('name');
        $session_role = $session->get('role');

        // If session is not set, redirect to login page
        if (!$session_id || !$session_name || !$session_role) {
            return redirect()->to('/');
        } else {
            // If session is set, check role permission for accessing the page
            $role_id = $session->get('role');
            $segment = $request->uri->getSegment(1);

            // Query the menu table to get the menu based on the segment
            $menu = $this->db->table('menu')->where('name', $segment)->get()->getRowArray();

            if (!$menu) {
                return redirect()->to('/404');
            } else {
                $menu_id = $menu['id'];
            }

            // Check if the user has access to the menu
            $has_access = $this->db->table('menu_roles')->where(['menu_id' => $menu_id, 'role_id' => $role_id])->get()->getRowArray();

            if (!$has_access) {
                return redirect()->to('/403');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }
}
