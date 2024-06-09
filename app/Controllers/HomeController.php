<?php

namespace App\Controllers;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiPengambilan;
use App\Models\User;

class HomeController extends BaseController
{
  protected $TransaksiMasuk;
  protected $Pengambilan;
  protected $Pemasukan;
  protected $Pengeluaran;
  protected $User;

  public function __construct()
  {
    $this->TransaksiMasuk = new TransaksiMasuk();
    $this->Pengambilan = new TransaksiPengambilan();
    $this->Pemasukan = new Pemasukan();
    $this->Pengeluaran = new Pengeluaran();
    $this->User = new User();
  }

  public function index()
  {
    $transaksiMasuk = $this->TransaksiMasuk->getAllTransactions();
    $Pengambilan = $this->Pengambilan->getAllTransactions();
    $totalTransaksi = $this->TransaksiMasuk->countAll();
    $totalPengambilan = $this->Pengambilan->countAll();
    $totalPemasukan = $this->Pemasukan->selectSum('jumlah')->get()->getRowArray();
    $totalPengeluaran = $this->Pengeluaran->selectSum('jumlah')->get()->getRowArray();
    $totalUser = $this->User->countAll();
    $recentTransaksiMasuk = $transaksiMasuk->orderBy('id', 'DESC')->findAll(3);
    $recentTransaksiPengambilan = $Pengambilan->orderBy('tgl_ambil', 'DESC')->findAll(3);
    $chartTransaksiMasuk = $this->TransaksiMasuk->getCharts()->findAll();
    $chartPemasukan = $this->Pemasukan->getCharts()->findAll();

    $data = [
      'title' => 'Dashboard',
      'header' => 'Dashboard',
      'total_transaksi' => $totalTransaksi,
      'pengambilan' => $totalPengambilan,
      'pemasukan' => $totalPemasukan,
      'pengeluaran' => $totalPengeluaran,
      'user' => $totalUser,
      'transaksi_masuk' => $recentTransaksiMasuk,
      'transaksi_pengambilan' => $recentTransaksiPengambilan,
      'chart_transaksi_masuk' => $chartTransaksiMasuk,
      'chart_pemasukan' => $chartPemasukan
    ];

    $data['saldo'] = $totalPemasukan['jumlah'] - $totalPengeluaran['jumlah'];

    return view('home/index', $data);
  }

  //--------------------------------------------------------------------

}
