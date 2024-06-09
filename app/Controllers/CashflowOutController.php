<?php

namespace App\Controllers;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;

class CashflowOutController extends BaseController
{
  protected $Pengeluaran;

  public function __construct()
  {
    $this->Pengeluaran = new Pengeluaran();
  }

  public function index()
  {
    $keyword  = $this->request->getVar('keyword');
    if ($keyword) {
      $pengeluaran = $this->Pengeluaran->like('keterangan', $keyword);
      $totalRows = $pengeluaran->countAllResults(false); // Ensure count without resetting the query
    } else {
      $pengeluaran = $this->Pengeluaran;
      $totalRows = $pengeluaran->countAllResults();
    }

    $currentPage = $this->request->getVar('page') ?: 1;

    $data = [
      'title' => 'Cash Flow',
      'header' => 'Pengeluaran',
      'pengeluaran' => $pengeluaran->orderBy('tanggal', 'DESC')->paginate(5),
      'pager' => $pengeluaran->pager,
      'currentPage' => $currentPage,
      'perPage' => 5,
      'totalRows' => $totalRows
    ];

    return view('cashflow/out/index', $data);
  }

  public function create()
  {
    $data = [
      'title' => 'Cash Flow',
      'header' => 'Tambah Data Pengeluaran',
      'validation' => \Config\Services::validation()
    ];

    return view('cashflow/out/create', $data);
  }

  public function save()
  {
    if (!$this->validate('pengeluaranRules')) {
      return redirect()->to('/cash-flow/pengeluaran/create')->withInput()->with('validation', \Config\Services::validation());
    }

    $data = [
      'keterangan' => $this->request->getPost('keterangan'),
      'tanggal' => $this->request->getPost('tanggal'),
      'jumlah' => $this->request->getPost('jumlah'),
    ];

    $this->Pengeluaran->save($data);

    session()->setFlashdata('success', 'Data berhasil disimpan!');
    return redirect()->to('/cash-flow/pengeluaran');
  }

  public function edit($pengeluaranID)
  {
    $pengeluaran = $this->Pengeluaran->find($pengeluaranID);
    if (!$pengeluaran) {
      throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Pengeluaran tidak ditemukan: ' . $pengeluaranID);
    }

    $data = [
      'title' => 'Cash Flow',
      'header' => 'Edit Data Pengeluaran',
      'validation' => \Config\Services::validation(),
      'pengeluaran' => $pengeluaran
    ];

    return view('cashflow/out/edit', $data);
  }

  public function update($pengeluaranID)
  {
    if (!$this->validate('pengeluaranRules')) {
      return redirect()->to('/cash-flow/pengeluaran/edit/' . $pengeluaranID)->withInput()->with('validation', \Config\Services::validation());
    }

    $data = [
      'id' => $pengeluaranID,
      'keterangan' => $this->request->getPost('keterangan'),
      'tanggal' => $this->request->getPost('tanggal'),
      'jumlah' => $this->request->getPost('jumlah'),
    ];

    $this->Pengeluaran->save($data);

    session()->setFlashdata('success', 'Data berhasil diubah!');
    return redirect()->to('/cash-flow/pengeluaran');
  }

  public function delete($pengeluaranID)
  {
    $this->Pengeluaran->delete($pengeluaranID);
    session()->setFlashdata('success', 'Data berhasil dihapus!');
    return redirect()->to('/cash-flow/pengeluaran');
  }

  //--------------------------------------------------------------------

}
