<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoadPredictionController extends Controller
{
    //負荷予測製造課選択
    public function department_select()
    {
        return view('load_prediction.department_select');
    }
}
