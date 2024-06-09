<?php

namespace App\Controllers;

use App\Models\Pemasukan;

class CashflowInController extends BaseController
{
  protected $Pemasukan;

  public function __construct()
  {
    $this->Pemasukan = new Pemasukan();
  }

  public function index()
  {
    // Get the current page number from the request
    $currentPage = $this->request->getVar('page') ?: 1;

    // Fetch paginated results
    $pemasukan = $this->Pemasukan->orderBy('tanggal', 'DESC')->paginate(5);

    // Prepare the data array to pass to the view
    $data = [
      'title' => 'Cash Flow',
      'header' => 'Pemasukan',
      'pemasukan' => $pemasukan,
      'pager' => $this->Pemasukan->pager,
      'currentPage' => $currentPage,
      'perPage' => 5,
      'totalRows' => $this->Pemasukan->countAllResults()
    ];

    // Render the view with the data
    return view('cashflow/in/index', $data);
  }

  //--------------------------------------------------------------------
}
