<?php
// app/Http/Controllers/ViewsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Seller;

class ViewsController extends Controller
{
    public function sellers()
    {
        // https://laravel.com/docs/9.x/eloquent#retrieving-models
        // $sellers = Seller::orderBy('id')->get();
        $sellers = Seller::all();
        
        $tmp = [];
        foreach( $sellers as $seller ){
            $tmp[] = [
                'id'           => $seller['id'],
                'name_address' => $seller['name_address'],
                'commission'   => $seller['commission'],
                'carriage'     => 0 == $seller['carriage'] ? '' : '&pound;'. number_format($seller['carriage'], 2, '.', ','),
            ];
        }
        $sellers = $tmp;
        
        // Sort sellers by ID using a natural sort: 1,2,3 etc. not 1,10,111,2 etc.
        uasort($sellers, function ($a, $b) {
            return strnatcmp($a['id'], $b['id']);
        });
        
        return view('sellers', [
            'sellers' => $sellers
        ]);
    }
    
    public function lots($id)
    {
        //DEBUG
        echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r('LOTS'); echo '</pre>';
    }
}
