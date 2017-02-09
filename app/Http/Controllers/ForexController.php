<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Forex;

class ForexController extends Controller
{
    /*
    * Main forex holders view
    *
    * @return \Illuminate\View\View
    */
 public function index(Request $request)
 {
   $holders=Forex::get();
   return view('forex.index',compact('holders'));
 }

}
