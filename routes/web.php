<?php

use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::group(['middleware' => 'auth'] , function(){
 
    Route::get('/dashboard',[ReportController::class,'index'])->name('dashboard');
    Route::get('/laporan', [ReportController::class,'list'])->name('report.list');
    Route::get('/laporan/add' , [ReportController::class,'create'])->name('report.create');
    Route::post('/laporan/add',[ReportController::class,'store'])->name('report.store');
    Route::post('/laporan/{id}/update',[ReportController::class,'update'])->name('report.update');
    Route::get('/laporan/{id}/delete',[ReportController::class,'destroy'])->name('report.destroy');

    Route::get('/rates',[ExchangeRateController::class,'index'])->name('rates.list');
    
    Route::get('/rates/add',[ExchangeRateController::class,'create'])->name('rates.create');
    Route::post('/rates/add',[ExchangeRateController::class,'store'])->name('rates.store');
    Route::get('/rates/{id}/edit',[ExchangeRateController::class,'edit'])->name('rates.edit');
    Route::post('/rates/{id}/edit',[ExchangeRateController::class,'update'])->name('rates.update');
    Route::get('/rates/{id}/delete',[ExchangeRateController::class,'destroy'])->name('rates.destroy');



});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
