<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    $filings=\App\Filer::select('filename','10k.filing_id')
        ->whereRaw('filing_id not in (select 10k_id from forex)')
        ->groupBy('cik')
        ->orderBy('filing_date','DESC')
        ->paginate(1000);

    foreach($filings as $filing){

        $content=@file_get_contents('https://www.sec.gov/Archives/'.$filing->filename);

        if (strpos($content, 'Foreign Exchange') !== false) {

            $forex= new \App\Forex;
            $forex->{'10k_id'}=$filing->filing_id;
            $forex->trader=1;
        }

        else {

            $forex= new \App\Forex;
            $forex->{'10k_id'}=$filing->filing_id;
            $forex->trader=0;
        }
        $forex->save();

    }
});


Route::resource('/forex', 'ForexController');
