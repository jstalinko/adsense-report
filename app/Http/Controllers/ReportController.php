<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Report;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
       
// --- Kalkulasi Total Keseluruhan ---

// 1. Buat query dasar yang akan digunakan untuk semua kalkulasi total.
$reportQuery = Report::query()
    // 2. Terapkan filter user HANYA JIKA pengguna saat ini BUKAN admin.
    ->when(!Auth::user()->is_admin, function ($query) {
        $query->where('user_id', Auth::user()->id);
    });

// 3. Lakukan semua kalkulasi dari query dasar yang sama menggunakan clone().
$totalModal = $reportQuery->clone()->sum('modal');
$totalRevenue = $reportQuery->clone()->sum('revenue');
$totalProfit = $reportQuery->clone()->sum(DB::raw('revenue - modal - (modal * tax / 100)'));

// Kalkulasi ROI dan ROAS (tidak berubah)
$totalRoi = ($totalModal > 0) ? round((($totalProfit / $totalModal) * 100), 2) : 0;
$totalRoas = ($totalModal > 0) ? round($totalProfit / $totalModal, 2) : 0;


// --- Kalkulasi Total Bulan Ini ---

// 1. Buat query dasar untuk bulan ini, termasuk filter user kondisional.
$thisMonthQuery = Report::query()
    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
    ->when(!Auth::user()->is_admin, function ($query) {
        $query->where('user_id', Auth::user()->id);
    });

// 2. Lakukan kalkulasi bulan ini dari query dasar yang sama.
$thisMonthModal = $thisMonthQuery->clone()->sum('modal');
$thisMonthRevenue = $thisMonthQuery->clone()->sum('revenue');
$thisMonthProfit = $thisMonthQuery->clone()->sum(DB::raw('revenue - modal - (modal * tax / 100)'));

// Kalkulasi ROI dan ROAS bulan ini (tidak berubah)
$thisMonthRoi = ($thisMonthModal > 0) ? round((($thisMonthProfit / $thisMonthModal) * 100), 2) : 0;
$thisMonthRoas = ($thisMonthModal > 0) ? round($thisMonthProfit / $thisMonthModal, 2) : 0;

        if(Auth::user()->is_admin)
        {
            
            $reports = Report::orderBy('id','desc')->with('user')->get();
        }else{
            
            $reports = Report::where('user_id',Auth::user()->id)->with('user')->orderBy('id','desc')->get();
        }
        
        // Mengirim data yang sudah diformat ke tampilan
        return Inertia::render('Dashboard', [
            'total_modal' => $totalModal,
            'total_revenue' => $totalRevenue,
            'total_roi' => $totalRoi,
            'total_roas' => $totalRoas,
            'this_month_modal' => $thisMonthModal,
            'this_month_revenue' => $thisMonthRevenue,
            'this_month_roi' => $thisMonthRoi,
            'this_month_roas' => $thisMonthRoas,
            'reports' => $reports,
            'rates' => ExchangeRate::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['rates'] = ExchangeRate::all();
        return Inertia::render('InputReport',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
        if($request->currency == 'IDR')
        {
            $modal = $request->modal;
            $revenue = $request->revenue;
        }else{
            $modal = ExchangeRate::convertIDR($request->currency,$request->modal);
            $revenue = ExchangeRate::convertIDR($request->currency,$request->revenue);
        }
        Report::create([
            'user_id' => Auth::user()->id,
            'domain' => $request->domain,
            'modal' => $modal,
            'revenue' => $revenue,
            'tax' => $request->tax ?? 0, // 3. Jika tax null/tidak ada, default ke 0
            'created_at' => $request->tanggal ?? now()
        ]);

        // 5. Redirect kembali ke halaman laporan dengan pesan sukses
        return redirect()->route('report.list')->with('success', 'Laporan berhasil ditambahkan.');

    }
    public function list()
    {
        $data['rates'] = ExchangeRate::all();
        if(Auth::user()->is_admin){
        $data['laporans'] = Report::with('user')->get();
        }else{
        $data['laporans'] = Report::where('user_id' , Auth::user()->id)->with('user')->get();
        }
        $data['domains'] = Report::select('domain','user_id')->where('user_id',Auth::user()->id)->groupBy('domain')->get();
        $data['users'] = User::all();
        
        return Inertia::render('Report',$data);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = Report::find($id);
        $report->domain = $request->domain;
        $report->modal = $request->modal;
        $report->revenue = $request->revenue;
        $report->tax = $request->tax;
        $report->created_at = $request->tanggal;
        $report->save();

        return redirect()->to('/laporan')->with('success','Berhasil memperbarui laporan~');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = Report::find($id);
        if($report->delete())
        {
            return redirect()->to('/laporan')->with('success','Berhasil menghapus laporan');
        }else{
            return redirect()->to('/laporan')->with('error','Gagal menghapus laporan');
        }
    }
}
