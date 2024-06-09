<?php

namespace App\Controllers;

use App\Models\Item;
use App\Models\Layanan;
use App\Models\Pemasukan;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiPengambilan;

class TransactionInController extends BaseController
{
  protected $layananModel;
  protected $transaksiMasukModel;
  protected $transaksiPengambilanModel;
  protected $itemModel;
  protected $pemasukanModel;

  public function __construct()
  {
    $this->layananModel = new Layanan();
    $this->transaksiMasukModel = new TransaksiMasuk();
    $this->transaksiPengambilanModel = new TransaksiPengambilan();
    $this->itemModel = new Item();
    $this->pemasukanModel = new Pemasukan();
  }

  public function index()
  {
    $keyword = $this->request->getVar('keyword');
    $currentPage = $this->request->getVar('page') ?? 1;

    if ($keyword) {
      $transaksiMasuk = $this->transaksiMasukModel->searchTransaction($keyword);
      $totalRows = $this->transaksiMasukModel->countRows($keyword);
    } else {
      $transaksiMasuk = $this->transaksiMasukModel->getAllTransactions();
      $totalRows = $this->transaksiMasukModel->countRows();
    }

    $data = [
      'title' => 'Transactions',
      'header' => 'Transaksi Masuk',
      'transaksi_masuk' => $transaksiMasuk->orderBy('transaksi_masuk.id', 'DESC')->paginate(5),
      'pager' => $transaksiMasuk->pager,
      'currentPage' => $currentPage,
      'perPage' => 5,
      'totalRows' => $totalRows,
    ];

    return view('transactions/in/index', $data);
  }

  public function create()
  {
    $maxId = $this->transaksiMasukModel->selectMax('id')->get()->getRowArray();

    $data = [
      'title' => 'Transactions',
      'header' => 'Tambah Transaksi Masuk',
      'validation' => service('validation'),
      'all_layanan' => $this->layananModel->findAll(),
      'no_transaksi' => $maxId['id'] ? $maxId['id'] + 1 : 1,
    ];

    return view('transactions/in/create', $data);
  }

  public function save()
  {
    if (!$this->validate('transaksiMasukRules')) {
      return redirect()->to('/transactions/transaksi-masuk/create')->withInput();
    }

    $data = [
      'nama' => strtoupper($this->request->getPost('nama')),
      'layanan_id' => $this->request->getPost('layanan_id'),
      'tgl_masuk' => $this->request->getPost('tgl_masuk'),
      'tgl_selesai' => $this->request->getPost('tgl_selesai'),
      'lunas' => $this->request->getPost('lunas'),
      'jumlah_item' => $this->request->getPost('jumlah_item'),
      'total_harga' => $this->request->getPost('total_harga'),
      'status' => 0,
      'user_creator' => strtoupper(session('name')),
      'user_editor' => null,
    ];

    $items = [
      'item' => $this->request->getPost('item'),
      'jumlah' => $this->request->getPost('jumlah'),
      'satuan' => $this->request->getPost('satuan'),
      'harga' => $this->request->getPost('harga'),
    ];

    // Insert to transaksi_masuk
    $this->transaksiMasukModel->save($data);
    $transaksiId = $this->transaksiMasukModel->insertID();

    // Insert to items
    $this->itemModel->insertItem($transaksiId, $items);

    // Insert to pemasukan if lunas == 1
    if ($data['lunas'] == 1) {
      $pemasukan = [
        'transaksi_masuk_id' => $transaksiId,
        'tanggal' => $data['tgl_masuk'],
        'keterangan' => 'Pembayaran Lunas Transaksi',
        'jumlah' => $data['total_harga'],
      ];
      $this->pemasukanModel->save($pemasukan);
    }

    session()->setFlashdata('success', 'Transaksi berhasil disimpan!');
    return redirect()->to('/transactions/transaksi-masuk');
  }

  public function edit($id)
  {
    $data = [
      'title' => 'Transactions',
      'header' => 'Edit Transaksi Masuk',
      'validation' => service('validation'),
      'transaksi' => $this->transaksiMasukModel->find($id),
      'all_layanan' => $this->layananModel->findAll(),
      'items' => $this->itemModel->where('transaksi_id', $id)->findAll(),
    ];

    return view('transactions/in/edit', $data);
  }

  public function update($id)
  {
    if (!$this->validate('transaksiMasukRules')) {
      return redirect()->to('/transactions/transaksi-masuk/edit/' . $id)->withInput();
    }

    $data = [
      'id' => $id,
      'nama' => strtoupper($this->request->getPost('nama')),
      'layanan_id' => $this->request->getPost('layanan_id'),
      'tgl_masuk' => $this->request->getPost('tgl_masuk'),
      'tgl_selesai' => $this->request->getPost('tgl_selesai'),
      'lunas' => $this->request->getPost('lunas'),
      'jumlah_item' => $this->request->getPost('jumlah_item'),
      'total_harga' => $this->request->getPost('total_harga'),
      'user_editor' => strtoupper(session('name')),
    ];

    $items = [
      'item' => $this->request->getPost('item'),
      'jumlah' => $this->request->getPost('jumlah'),
      'satuan' => $this->request->getPost('satuan'),
      'harga' => $this->request->getPost('harga'),
    ];

    // Update transaksi_masuk
    $this->transaksiMasukModel->save($data);
    $transaksiId = $id;

    // Delete old items
    $this->itemModel->where('transaksi_id', $transaksiId)->delete();

    // Re-insert new items
    $this->itemModel->insertItem($transaksiId, $items);

    // Update pemasukan
    $existingPemasukan = $this->pemasukanModel->where('transaksi_masuk_id', $transaksiId)->first();
    if ($data['lunas'] == 1) {
      if (!$existingPemasukan) {
        $pemasukan = [
          'transaksi_masuk_id' => $transaksiId,
          'tanggal' => $data['tgl_masuk'],
          'keterangan' => 'Pembayaran Lunas Transaksi',
          'jumlah' => $data['total_harga'],
        ];
        $this->pemasukanModel->save($pemasukan);
      }
    } else {
      if ($existingPemasukan) {
        $this->pemasukanModel->where('transaksi_masuk_id', $transaksiId)->delete();
      }
    }

    session()->setFlashdata('success', 'Transaksi berhasil diubah!');
    return redirect()->to('/transactions/transaksi-masuk/detail/' . $id);
  }

  public function detail($id)
  {
    $data = [
      'title' => 'Transactions',
      'header' => 'Detail Transaksi',
      'transaksi' => $this->transaksiMasukModel->getTransactionDetail($id)->first(),
      'items' => $this->itemModel->where('transaksi_id', $id)->findAll(),
    ];

    return view('transactions/in/detail', $data);
  }

  public function delete($id)
  {
    // Delete items
    $this->itemModel->where('transaksi_id', $id)->delete();

    // Delete pemasukan if exists
    $existingPemasukan = $this->pemasukanModel->where('transaksi_masuk_id', $id)->first();
    if ($existingPemasukan) {
      $this->pemasukanModel->where('transaksi_masuk_id', $id)->delete();
    }

    // Delete pengambilan if exists
    $existingPengambilan = $this->transaksiPengambilanModel->where('transaksi_masuk_id', $id)->first();
    if ($existingPengambilan) {
      $this->transaksiPengambilanModel->where('transaksi_masuk_id', $id)->delete();
    }

    // Delete transaksi_masuk
    $this->transaksiMasukModel->delete($id);

    session()->setFlashdata('success', 'Transaksi berhasil dihapus!');
    return redirect()->to('/transactions/transaksi-masuk');
  }
}
