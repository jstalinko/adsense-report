<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['rates'] = ExchangeRate::all();
        return Inertia::render('Exchange', $data);
    }


    public function store(Request $request)
    {
        $rates = $request->rates;
        $currency = $request->currency;
        if ($rates == '' || $currency == '') {
            return redirect()->route('rates.list');
        }

        $store = ExchangeRate::updateOrCreate(
            [
                'currency' => strtoupper($currency)
            ],
            ['rates' => $rates]
        );
        if ($store) {
            return redirect()->route('rates.list')->with('success', 'Berhasil Menyimpan data');
        } else {
            return redirect()->route('rates.list')->with('error', 'Gagal menyimpan data');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rates = $request->rates;
        $currency = $request->currency;
        if ($rates == '' || $currency == '') {
            return redirect()->route('rates.list');
        }

        $store = ExchangeRate::where('id', $request->id)->update(
            ['rates' => $rates, 'currency' => strtoupper($currency)]
        );
        if ($store) {
            return redirect()->route('rates.list')->with('success', 'Berhasil Menyimpan data');
        } else {
            return redirect()->route('rates.list')->with('error', 'Gagal menyimpan data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rate = ExchangeRate::find($id);
        if ($rate->delete()) {
            return redirect()->route('rates.list')->with('success', 'Berhasil hapus data');
        } else {
            return redirect()->route('rates.list')->with('error', 'Gagal  Hapus data');
        }
    }
}
