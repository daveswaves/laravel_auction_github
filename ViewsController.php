<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AuctionDate;
use App\Models\Seller;
use App\Models\Lot;

class ViewsController extends Controller
{
    private $dates_sorted = [];
    private $years = [];
    
    public function __construct()
    {
        $auction_dates = AuctionDate::all();
        
        $tmp = [];
        foreach( $auction_dates as $rec ){
            $tmp[] = [
                'id' => $rec['f_unique_id'],
                'date' => $rec['f_auction_date'],
            ];
        }
        $auction_dates = $tmp;
        
        // Sort 'auction_dates' by 'date' in reverse, natural order
        uasort($auction_dates, function ($a, $b) {
            return strnatcmp($b['date'], $a['date']);
        });
        
        $dates_arr = [];
        $dates_id_lookup = [];
        foreach ($auction_dates as $rec) {
            list($y,$m,$d) = explode('-', $rec['date']);
            $dates_arr[ $rec['date'] ]['y'] = $y;
            $dates_arr[ $rec['date'] ]['m'] = $m;
            $dates_arr[ $rec['date'] ]['d'] = $d;
            $dates_id_lookup[ $rec['date'] ] = $rec['id'];
        }
        
        foreach ($dates_arr as $rec) {
            $this->dates_sorted[ $rec['y'] ][] = $rec['d'].'-'.$rec['m'].'-'.$rec['y'];
        }
        
        foreach ($this->dates_sorted as $key => $_) {
            $this->years[] = $key;
        }
        
        $top_nav_dropdown = [];
        
        if( !isset($_POST['date_current']) ){
            $top_nav_dropdown['year_dates'] = $this->dates_sorted[ $this->years[0]];
            $_POST['date_current'] = $top_nav_dropdown['year_dates'][0];
        }
        
        if( isset($_POST['post_date']) ){
            if( preg_match("/^\d{4}$/", $_POST['post_date']) ){
                $top_nav_dropdown['year_dates'] = $this->dates_sorted[ $this->years[$_POST['post_date']]];
                $_POST['date_current'] = $top_nav_dropdown['year_dates'][0];
            }
            elseif( preg_match("/^\d{2}-\d{2}-\d{4}$/", $_POST['post_date']) ){
                $_POST['date_current'] = $_POST['post_date'];
            }
        }
        
        $year_current = substr($_POST['date_current'], -4);
        // Sets the year in the navbar.
        $top_nav_dropdown['year_current'] = $year_current; // 2021
        // Sets the date in the navbar.
        $top_nav_dropdown['date_current'] = $_POST['date_current']; // 29-06-2021
        // Sets the dropdown years in the navbar.
        $top_nav_dropdown['years'] = $this->years; // [ 2021, 2020 ]
        // Sets the dropdown dates in the navbar.
        $top_nav_dropdown['year_dates'] = $this->dates_sorted[$year_current]; // [ 29-06-2021, 18-05-2021, 20-04-2021 ]
        
        
        $dropdown = [];
        $dropdown['years'][] = '<select>';
        foreach( $top_nav_dropdown['years'] as $year ){
            $dropdown['years'][] = "<option value='$year'>$year</option>";
        }
        $dropdown['years'][] = '</select>';
        
        $dropdown['year_dates'][] = '<li class="dropdown">';
        $dropdown['year_dates'][] = '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">29-06-2021 <span class="caret"></span></a>';
        $dropdown['year_dates'][] = '<ul class="dropdown-menu">';
        foreach( $top_nav_dropdown['year_dates'] as $year_date ){
            $dropdown['year_dates'][] = '<li><a href="http://127.0.0.1:8000/sellers/'.$year_date.'">'.$year_date.'</a></li>';
        }
        $dropdown['year_dates'][] = '</ul>';
        $dropdown['year_dates'][] = '</li>';
        
        // $dropdown['year_dates'][] = '<select name="date_current" onchange="this.form.submit()">';
        // foreach( $top_nav_dropdown['year_dates'] as $year_date ){
        //     $dropdown['year_dates'][] = "<option value='$year_date'>$year_date</option>";
        // }
        // $dropdown['year_dates'][] = '</select>';
        
        // This enables dropdowns to be accessed on the layout.blade.php file.
        view()->share('dd_years', implode('', $dropdown['years']) );
        view()->share('dd_year_dates', implode('', $dropdown['year_dates']) );
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($top_nav_dropdown); echo '</pre>'; die(); //DEBUG
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( $this->years ); echo '</pre>';
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( $this->dates_sorted ); echo '</pre>'; die();
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( config('user.auction_dates') ); echo '</pre>'; die();
    }
    
    public function sellers($date)
    {
        // https://laravel.com/docs/9.x/eloquent#retrieving-models
        // $sellers = Seller::all();
        $sellers = Seller::select('f_seller_id','f_address','f_commission','f_carriage')->where('f_id', '163')->get();
        
        $tmp = [];
        foreach( $sellers as $seller ){
            $tmp[] = [
                'id'           => $seller['f_seller_id'],
                'name_address' => $seller['f_address'],
                'commission'   => $seller['f_commission'],
                'carriage'     => 0 == $seller['f_carriage'] ? '' : '&pound;'. number_format($seller['f_carriage'], 2, '.', ','),
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
        $lots = Lot::select("*")->where([['f_seller_id',$id],['f_id',163]])->get();
        
        $seller = Seller::select('f_address')->where([['f_seller_id',$id],['f_id',163]])->get();
        
        $seller = ['id' => $id,'name_address' => $seller[0]['f_address']];
        
        $tmp1 = [];
        foreach( $lots as $lot ){
            $checked = $lot['f_wd'] ? ' checked=""' : '';
            $withdrawn_cbx = '<input type="checkbox" name="wd_input_'.$lot['f_lot_no'].'" value="'.$lot['f_wd'].'"'.$checked.'>';
            
            $tmp2 = [];
            foreach( range(0, 4) as $opt ){
                $sel = $lot['f_electric'] == $opt ? ' selected=""' : '';
                $tmp2[] = "<option value='$opt'$sel>$opt</option>";
            }
            $elec_opts = implode('', $tmp2);
            $lot_price = number_format($lot['f_lot_price'], 2, '.', ',');
            
            $tmp1[] = [
                'lot_no'    => $lot['f_lot_no'],
                'lot_name'  => $lot['f_lot_name'],
                'withdrawn_cbx' => $withdrawn_cbx,
                'electric_dropdown' => '<select name="elec_input_'. $lot['f_lot_no'] .'">'. $elec_opts .'</select>',
                'lot_price_txtbx' => '<input type="text" name="price_input_'.$lot['f_lot_no'].'" value="'.$lot_price.'" id="price_input" class="txtbox lot_price_input">',
            ];
        }
        $lots = $tmp1;
        
        uasort($lots, function ($a, $b) {
            return strnatcmp($a['lot_no'], $b['lot_no']);
        });
        
        return view('lots', [
            'seller' => $seller,
            'lots' => $lots,
        ]);
    }
}
