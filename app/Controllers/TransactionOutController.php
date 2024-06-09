<?php

namespace App\Controllers;

use App\Models\Pemasukan;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiPengambilan;

class TransactionOutController extends BaseController
{
  protected $transaksiMasukModel;
  protected $transaksiPengambilanModel;
  protected $pemasukanModel;

  public function __construct()
  {
    $this->transaksiMasukModel = new TransaksiMasuk();
    $this->transaksiPengambilanModel = new TransaksiPengambilan();
    $this->pemasukanModel = new Pemasukan();
  }

  public function index()
  {
    $keyword = $this->request->getVar('keyword');
    $currentPage = $this->request->getVar('page') ?? 1;

    if ($keyword) {
      $pengambilan = $this->transaksiPengambilanModel->searchTransaction($keyword);
      $totalRows = $this->transaksiPengambilanModel->countRows($keyword);
    } else {
      $pengambilan = $this->transaksiPengambilanModel->getAllTransactions();
      $totalRows = $this->transaksiPengambilanModel->countRows();
    }

    $data = [
      'title' => 'Transactions',
      'header' => 'Transaksi Pengambilan',
      'pengambilan' => $pengambilan->orderBy('transaksi_pengambilan.tgl_ambil', 'DESC')->paginate(5),
      'pager' => $pengambilan->pager,
      'currentPage' => $currentPage,
      'perPage' => 5,
      'totalRows' => $totalRows,
    ];

    return view('transactions/out/index', $data);
  }

  public function create()
  {
    $data = [
      'title' => 'Transactions',
      'header' => 'Tambah Transaksi Pengambilan',
      'validation' => service('validation'),
    ];

    return view('transactions/out/create', $data);
  }

  public function save()
  {
    if (!$this->validate('transaksiPengambilanRules')) {
      return redirect()->to('/transactions/transaksi-pengambilan/create')->withInput();
    }

    $transaksiId = $this->request->getPost('transaksi_masuk_id');
    $transaksiMasuk = $this->transaksiMasukModel->find($transaksiId);

    if (!$transaksiMasuk) {
      session()->setFlashdata('error', 'Transaksi tidak ditemukan!');
      return redirect()->to('/transactions/transaksi-pengambilan/create');
    }

    $dataPengambilan = [
      'transaksi_masuk_id' => $transaksiId,
      'tgl_ambil' => $this->request->getPost('tgl_ambil'),
      'user_creator' => strtoupper(session('name')),
    ];

    $this->transaksiPengambilanModel->save($dataPengambilan);

    if ($transaksiMasuk['lunas'] == 0) {
      $dataPemasukan = [
        'transaksi_masuk_id' => $transaksiId,
        'tanggal' => $this->request->getPost('tgl_ambil'),
        'keterangan' => 'Pembayaran Transaksi',
        'jumlah' => $transaksiMasuk['total_harga'],
      ];
      $this->pemasukanModel->save($dataPemasukan);
    }

    $this->transaksiMasukModel->update($transaksiId, ['status' => 1]);

    session()->setFlashdata('success', 'Data berhasil disimpan!');
    return redirect()->to('/transactions/transaksi-pengambilan');
  }

  public function delete($transaksiId)
  {
    $this->transaksiMasukModel->update($transaksiId, ['status' => 0]);

    $transaksiMasuk = $this->transaksiMasukModel->find($transaksiId);
    if ($transaksiMasuk['lunas'] == 0) {
      $this->pemasukanModel->where('transaksi_masuk_id', $transaksiId)->delete();
    }

    $this->transaksiPengambilanModel->where('transaksi_masuk_id', $transaksiId)->delete();

    session()->setFlashdata('success', 'Data berhasil dihapus!');
    return redirect()->to('/transactions/transaksi-pengambilan');
  }
}
