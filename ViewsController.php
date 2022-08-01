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
        
        
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( $this->years ); echo '</pre>';
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( $this->dates_sorted ); echo '</pre>'; die();
        // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( config('user.auction_dates') ); echo '</pre>'; die();
    }
    
    public function sellers()
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
