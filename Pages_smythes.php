<?php
// http://localhost/codeig-smythes/public_html/user
// http://www.smythes.net/user

/*
Methods:

| SELLERS PAGE
| LOTS PAGE
| NEW PAGE
| COMMISSION PAGE
| PRINT PAGE
| SEARCH PAGE
| WEBSITE PAGE
| WEBSITE_DIARY PAGE
| WEBSITE_ACTIVE PAGE
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Pages_smythes extends CI_Controller {
	protected $dates_sorted_arr = array();
	protected $years = array();

	public function __construct()
	{
		parent::__construct();
		session_start();

		if ( !password_verify(session_id(), $_SESSION['sess_id'])) { die(); }

		$this->load->helper( array('form','url'));

		$params = array(
			'tbl_name'	=> $this->config->item('inhouse_auction_dates'),
			'desc'		=> TRUE,
			'desc_fld'	=> 'f_auction_date',
			'sess_name'	=> 'inhouse_auction_dates',
			'key_fld'	=> 'f_unique_id',
		);
		make_session_ftn($params);

		// Sort 'inhouse_auction_dates' by 'f_auction_date' in reverse, natural order
		uasort($_SESSION['inhouse_auction_dates'], function ($a, $b) {
			return strnatcmp($b['f_auction_date'], $a['f_auction_date']);
		});

		$dates_arr = array();
		$_SESSION['dates_id_lookup'] = array();
		foreach ($_SESSION['inhouse_auction_dates'] as $key => $val) {
			list($y,$m,$d) = explode('-', $val['f_auction_date']);
			$dates_arr[ $val['f_auction_date'] ]['y'] = $y;
			$dates_arr[ $val['f_auction_date'] ]['m'] = $m;
			$dates_arr[ $val['f_auction_date'] ]['d'] = $d;
			$_SESSION['dates_id_lookup'][ $val['f_auction_date'] ] = $val['f_unique_id'];
		}

		/*	[2017-04-04] => array( [y] => 2017, [m] => 04, [d] => 04 )
			[2017-03-28] => array( [y] => 2017, [m] => 03, [d] => 28 )
			[2017-03-07] => array( [y] => 2017, [m] => 03, [d] => 07 ) */

		/*	$_SESSION['dates_id_lookup']:
			'2017-01-17' => '183'
			'2017-02-07' => '184'
			'2017-01-31' => '185'
			'2017-02-04' => '186'
			'2017-03-07' => '187'
			'2017-04-04' => '188'
			'2017-03-28' => '189' */

		// echo '<pre>'; print_r($_SESSION['dates_id_lookup']); echo '</pre>';

		foreach ($dates_arr as $key => $val) {
			$this->dates_sorted_arr[ $val['y'] ][] = $val['d'].'-'.$val['m'].'-'.$val['y'];
		}
		/*	04-04-2017
			28-03-2017
			07-03-2017 */

		foreach ($this->dates_sorted_arr as $key => $val) {
			$this->years[] = $key;
		}
		/*	2017
			2016
			2015 */
	}
	// __construct





	public function view($page = 'sellers', $lookup = NULL)
	{
		$data = array();

		// check page exists
		if ( 'get_db_records' != $page && !file_exists( APPPATH.'views/backend/'.$page.'.php') ) {
			show_404();
		}

		$_SESSION['view'] = $page;

		$urls = array(
			'sellers'		=> 'Sellers',
			'lots'			=> 'Lots',
			'new'			=> 'New Auction',
			'commission'	=> 'Commission',
			'print'			=> 'Print',
			'search'		=> 'Search',
			'website'		=> 'Website',
		);

		$nav_links = array();
		foreach( $urls as $url => $name) {
			$nav_links[$url]['active-class'] = $url == $page ? ' class="active"' : '';
			// This highlights the 'website' link when the 'Auction Diary' link on the 'website' page is selected.
			// Required because the 'Auction Diary' link uses a different view 'website_diary'.
			if ($url.'_diary' == $page) { $nav_links[$url]['active-class'] = ' class="active"'; }
			elseif ($url.'_active' == $page) { $nav_links[$url]['active-class'] = ' class="active"'; }
			$nav_links[$url]['sr-only'] = $url == $page ? ' <span class="sr-only">(current)</span>' : '';
			$nav_links[$url]['name'] = $name;
		}

		// die();

		$data['nav_links'] = $nav_links;

		/*
		|-----------------------------------------------------------
		| Create SESSION['date_current'] if !exist.
		| Update if new dropdown date selected.
		| NOTE: If new dropdown year selected, 'date_current' is set
		|       to most recent date from that year.
		|
		*/
		if ( !isset($_SESSION['date_current']) ) {
			$top_nav_dropdown['year_dates'] = $this->dates_sorted_arr[ $this->years[0]];
			$_SESSION['date_current'] = $top_nav_dropdown['year_dates'][0];
		}
		if ($lookup !== NULL) {
			if (preg_match("/^\d{4}$/", $lookup)) {
				$top_nav_dropdown['year_dates'] = $this->dates_sorted_arr[$lookup];
				$_SESSION['date_current'] = $top_nav_dropdown['year_dates'][0];
			}
			else if (preg_match("/^\d{2}-\d{2}-\d{4}$/", $lookup)) {
				$_SESSION['date_current'] = $lookup;
			}
		}

		$year_current = substr($_SESSION['date_current'], -4);
		// Sets the year in the navbar.
		$top_nav_dropdown['year_current'] = $year_current;
		// Sets the date in the navbar.
		$top_nav_dropdown['date_current'] = $_SESSION['date_current'];
		// Sets the dropdown years in the navbar.
		$top_nav_dropdown['years'] = $this->years; // 2017, 2016, 2015 etc.
		// Sets the dropdown dates in the navbar.
		$top_nav_dropdown['year_dates'] = $this->dates_sorted_arr[$year_current];
		$data['top_nav_dropdown'] = $top_nav_dropdown;





		/*
		|--------------------------------------------------------------------------
		| SELLERS PAGE
		|--------------------------------------------------------------------------
		|
		*/
		if ('sellers' == $page) {
			/*
			|-------------------
			| REQUIRED SESSIONs:
			|  * auction_dates
			|  * dates_id_lookup
			|  * date_current
			|  * seller_details
			|  * lot_details
			|  * total_sellers
			|  * view
			|
			*/

			$date_current_sql = mysql_date_format_ftn( $_SESSION['date_current'] );

			// Create $_SESSION['seller_details'] ...
			$params = array(
				'tbl_name'	=> $this->config->item('inhouse_seller_details'),
				'where'	=> array( 'f_id' => $_SESSION['dates_id_lookup'][ $date_current_sql ] ),
				'sess_name'	=> 'seller_details',
				'key_fld'	=> 'f_seller_id',
			);
			make_session_ftn($params);

			// ksort($_SESSION['seller_details'], SORT_NATURAL);
			ksort($_SESSION['seller_details']);

			// echo '<pre>'; print_r($_SESSION['seller_details']); echo '</pre>'; die();

			if ($lookup !== NULL) {
				unset($_SESSION['seller_details']);
				make_session_ftn($params);
			}

			// Create $_SESSION['lot_details'] ...
			$params = array(
				'tbl_name'	=> $this->config->item('inhouse_lot_details'),
				'where'	=> array( 'f_id' => $_SESSION['dates_id_lookup'][ $date_current_sql ] ),
				'sess_name'	=> 'lot_details',
				'key_fld'	=> 'f_lot_no',
			);
			make_session_ftn($params);

			if ($lookup !== NULL) {
				unset($_SESSION['lot_details']);
				make_session_ftn($params);
			}

			// echo '<pre>'; print_r($_SESSION['seller_details']); echo '</pre>'; die();

			$_SESSION['total_sellers'] = count( $_SESSION['seller_details'] );




			/*
			|--------------------------------------------------------------------------
			| Create Withdrawn lots array.
			|--------------------------------------------------------------------------
			|
			*/
			$tmp_array = $_SESSION['lot_details'];

			$wd_lots = array();
			$all_lots = array();
			foreach ($tmp_array as $record) {
				$all_lots[ $record['f_seller_id'] ]++;
				// if 'f_wd' == 1
				if ($record['f_wd']) { $wd_lots[ $record['f_seller_id'] ]++; }
			}

			// IMPORTANT: only sellers where all their lots have been withdrawn are displayed
			$wd_sellers = array();
			foreach ($wd_lots as $key => $val) {
				if ($wd_lots[$key] == $all_lots[$key]) {
					$wd_sellers[$key] = TRUE;
				}
			}

			$_SESSION['wd_seller_lots'] = array();
			$_SESSION['wd_seller_lots']['auction_date'] = $date_current_sql;
			foreach ($tmp_array as $record) {
				if ( isset($wd_sellers[ $record['f_seller_id'] ]) ) {
					$_SESSION['wd_seller_lots'][ $record['f_seller_id'] ]['seller_name'] = $_SESSION['seller_details'][$record['f_seller_id']]['f_address'];
					if ($record['f_wd']) {
						$_SESSION['wd_seller_lots'][ $record['f_seller_id'] ]['lots'][] = array(
							'f_lot_no' => $record['f_lot_no'],
							'f_lot_name' => $record['f_lot_name'],
						);
					}
				}
			}

			// echo '<pre>'; print_r($_SESSION['wd_seller_lots']); echo '</pre>'; die();
		}
		// END SELLERS PAGE





		/*
		|--------------------------------------------------------------------------
		| LOTS PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('lots' == $page) {
			/*
			|-------------------
			| REQUIRED SESSIONs:
			|  * auction_dates
			|  * dates_id_lookup
			|  * date_current
			|  * lot_details
			|  * seller_details
			|  * view
			|
			*/
			if (!isset($_SESSION['seller_details']) || count($_SESSION['seller_details']) === 0) {
				redirect( base_url('sellers') );
			}

			$seller_id = isset($lookup) ? $lookup : 1;
			$data['seller_id'] = $seller_id;
			$_SESSION['seller_id'] = $seller_id;

			$data['query_arr'] = array();
			$data['total_price'] = 0;
			$elec_nums = array();
			foreach ($_SESSION['lot_details'] as $val) {
				if ($val['f_seller_id'] == $seller_id) {
					$data['query_arr'][ $val['f_lot_no'] ] = $val;
					$data['total_price'] = $data['total_price'] + $val['f_lot_price'];
					$elec_nums[] = $val['f_electric'];
				}
			}

			// ksort($data['query_arr'], SORT_NATURAL);

			// ksort($data['query_arr']);
			ksort($data['query_arr'], SORT_NATURAL);

			// echo '<pre>'; print_r($data['query_arr']); echo '</pre>'; die();

			foreach ($_SESSION['inhouse_auction_dates'] as $val) {
				if ( mysql_date_format_ftn( $_SESSION['date_current'] ) == $val['f_auction_date'] ) {
					$vat_rate = $val['f_auction_vat'] / 1000;
					$elec_rate = $val['f_elec_check'] / 10;
					break;
				}
			}

			$total_elec = 0;
			foreach ($elec_nums as $val) {
				$total_elec = $total_elec + $val;
			}

			$data['elec_rate'] = $total_elec * $elec_rate;
			$comm_rate = $_SESSION['seller_details'][$seller_id]['f_commission'] / 100;
			$data['commission'] = $data['total_price'] * $comm_rate + $data['total_price'] * $comm_rate * $vat_rate;

			if ($data['total_price'] != 0 && $data['commission'] < 0.5 && $comm_rate > 0) {
				$data['commission'] = 0.5;
			}

			$data['carr_rate'] = $_SESSION['seller_details'][$seller_id]['f_carriage'];
			$data['total'] = $data['total_price'] - $data['commission'] - $data['carr_rate'] - $data['elec_rate'];

			$data['next_lot_num'] = inc_highest_val_ftn( array(
				'array' => $_SESSION['lot_details'],
				'field_name' => 'f_lot_no',
			));

			// echo '<pre>'; print_r($data['next_lot_num']); echo '</pre>'; die();
			// echo '<pre>'; print_r($_SESSION['lot_details']); echo '</pre>'; die();
		}
		// END LOTS PAGE





		elseif ('get_db_records' == $page) {
			// # lots: 35386
			// # sellers: 10545

			$auction_id = '113';

			$where = array(
				'f_id' => $auction_id,
			);

			$this->db->where( $where );
			$query = $this->db->get( $this->config->item('inhouse_seller_details') );

			$tmp_array = $query->result_array();

			echo '<pre>'; print_r($tmp_array); echo '</pre>'; die();

			// ############################################################




			$auction_id = '115';
			$seller_id = '107';

			$where = array(
				// 'f_address' => 'WITHDR',
				'f_id' => $auction_id,
				// 'f_seller_id' => $seller_id,
			);
			// $this->db->like( $where );
			$this->db->where( $where );
			$query = $this->db->get( $this->config->item('inhouse_lot_details') );

			$tmp_array = $query->result_array();



			// echo '<pre>'; print_r( count($tmp_array) ); echo '</pre>';
/*
			$all_auction_dates = array();
			foreach ($_SESSION['inhouse_auction_dates'] as $val) {
				$all_auction_dates[ $val['f_unique_id'] ] = $val['f_auction_date'];
			}

			echo '<pre>';

			print_r('AUCTION DATE: '.$all_auction_dates[$auction_id]);

			echo "\n========================\n";

			$withdrawn_str = '';
			foreach ($tmp_array as $val) {
				echo "{$val['f_lot_no']} | {$val['f_lot_name']}\n";
			}

			echo '</pre>';

			die();
*/





			$duplicate_lots = array();
			foreach ($tmp_array as $val) {
				$duplicate_lots[ $val['f_lot_no'] ]++;
			}

			foreach ($duplicate_lots as $key => $val) {
				if ($val > 1) {
					echo "Duplicate lot: $key<br>";
				}
			}

			die();

			echo '<pre>'; print_r($duplicate_lots); echo '</pre>'; die();

			echo '<pre>'; print_r( $tmp_array ); echo '</pre>'; die();






			$sql_str = "DELETE FROM `t_inhouse_seller_details_2014` WHERE `f_address` LIKE 'WITHDR%';<br><br>";
			foreach ($tmp_array as $rec) {
				// $sql_str .= "DELETE FROM `t_inhouse_lot_details_2014` WHERE `f_id` = '{$rec['f_id']}' AND `f_seller_id` = '{$rec['f_seller_id']}';<br>";
				$sql_str .= "SELECT * FROM `t_inhouse_lot_details_2014` WHERE `f_id` = '{$rec['f_id']}' AND `f_seller_id` = '{$rec['f_seller_id']}';<br>";
			}

			echo '<pre>'; print_r($sql_str); echo '</pre>'; die();


			// SELECT * FROM `t_inhouse_lot_details_2014` WHERE `f_id` = '115' AND `f_seller_id` = '107'

			$wd_lots = array();
			$all_lots = array();
			foreach ($tmp_array as $record) {
				$all_lots[ $record['f_seller_id'] ]++;
				if ($record['f_wd']) { $wd_lots[ $record['f_seller_id'] ]++; }
			}

			$wd_sellers = array();
			foreach ($wd_lots as $key => $val) {
				if ($wd_lots[$key] == $all_lots[$key]) {
					// echo "Withdrawn Sellers: $key<br>";
					$wd_sellers[] = $key;
				}
			}

			die();



			$str = "WITHDRAWN LIST:<br>";
			foreach ($tmp_array as $record) {
				$str .= "{$record['f_lot_no']} | {$record['f_lot_name']}<br>";
			}

			echo '<pre>'; print_r( $str ); echo '</pre>'; die();
		}





		/*
		|--------------------------------------------------------------------------
		| NEW PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('new' == $page) {
			/*
			|-------------------
			| REQUIRED SESSIONs:
			|  * auction_dates
			|  * dates_id_lookup
			|  * date_current
			|  * view
			|
			*/

			$this->load->helper('form');

			$six_recent_auctions = array();
			$i=0;
			foreach ($_SESSION['inhouse_auction_dates'] as $key => $val) {
				if ($i>5) {break;}
				$six_recent_auctions[$i]['date'] = mysql_date_format_ftn( $val['f_auction_date'] );
				$six_recent_auctions[$i]['type'] = $val['f_auction_type'] == 'g' ? 'General' : 'Antique';
				$six_recent_auctions[$i]['buyers_prem'] = $val['f_buyers_prem'];
				$six_recent_auctions[$i]['vat'] = $val['f_auction_vat'] / 10;
				$six_recent_auctions[$i]['pat_fee'] = number_format($val['f_elec_check'] / 10, 2, '.', '');
				$i++;
			}
			$data['auctions'] = $six_recent_auctions;
		}
		// END NEW PAGE





		/*
		|--------------------------------------------------------------------------
		| COMMISSION PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('commission' == $page) {
			$auction_date_id = $_SESSION['dates_id_lookup'][ mysql_date_format_ftn( $_SESSION['date_current'] ) ];
			$vat_rate = $_SESSION['inhouse_auction_dates'][$auction_date_id]['f_auction_vat'];
			$pat_rate = $_SESSION['inhouse_auction_dates'][$auction_date_id]['f_elec_check'];
			$buyers_rate = $_SESSION['inhouse_auction_dates'][$auction_date_id]['f_buyers_prem'];

			/*
			|-------------------------------------------------------------
			| COMMISSION:
			|    Sum t_inhouse_lot_details.f_lot_price for each seller.
			|    Multiply by sellers t_inhouse_seller_details.f_commission.
			| CARRIAGE:
			| ELECTRIC:
			|
			*/
			$data['total_comm_arr'] = array();
			$data['total_carr'] = 0;
			$data['total_elec'] = 0;

			if (!isset($_SESSION['seller_details'])) {
				redirect( base_url() );
			}

			$total_lots_all_sellers = 0;
			foreach ($_SESSION['seller_details'] as $seller_val) {
				$total_lots = 0;
				foreach ($_SESSION['lot_details'] as $lot_val) {
					if ($seller_val['f_seller_id'] == $lot_val['f_seller_id']) {
						$total_lots += $lot_val['f_lot_price'];
						$data['total_elec'] += $lot_val['f_electric'];
					}
				}

				$comm_price = $total_lots * $seller_val['f_commission'] / 100;
				$total_lots_all_sellers += $total_lots;

				$data['total_comm_arr'][] = array(
					$seller_val['f_commission'] => $comm_price + $comm_price * $vat_rate / 1000
				);

				$data['total_carr'] += $seller_val['f_carriage'];
			}

			//@@@@@@@@@@@@@@@@@@
			// echo '<pre style="background:#333; color:#fff;">'; print_r( $data['total_comm_arr'] ); echo '</pre>'; die();

			$data['total_elec'] = $data['total_elec'] * $pat_rate / 10;
			$data['buyers_premium'] = $total_lots_all_sellers * $buyers_rate/100;
			$data['total_comm'] = 0;

			foreach (range(0, 20) as $val) {
				$data['commissions'][$val] = 0;
			}

			foreach ($data['total_comm_arr'] as $vals) {
				foreach ($vals as $key => $val) {
					$data['total_comm'] += $val;
					$data['commissions'][$key] += $val;
				}
			}
		}
		// END COMMISSION PAGE





		/*
		|--------------------------------------------------------------------------
		| PRINT PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('print' == $page) {
			/*
			|--------------------------------------------------------------------------
			| FIX:	* The 'W/D' characters can end up overwriting the lot name.
			|       * Display 'print catalogue' footnote (PLEASE NOTE...) at the bottom
			|         of the left column - if space.
			|
			| NOTE:	As well as the 'print' view, the 'fpdf/create_pdf_smythes.php'
			|       and 'fpdf/create_pdf_smythes_cat.php' scripts are also required ...
			|
			*/

			/*
			|-------------------
			| REQUIRED SESSIONs:
			|  * seller_details
			|  * lot_details
			|  * date_current
			|  * auction_vat
			|  * auction_elec_check
			|  * auction_buyers_prem
			|
			*/

			$date_current_sql = mysql_date_format_ftn( $_SESSION['date_current'] );
			$auction_id = $_SESSION['dates_id_lookup'][ $date_current_sql ];

			$_SESSION['auction_vat'] = $_SESSION['inhouse_auction_dates'][$auction_id]['f_auction_vat'];
			$_SESSION['auction_elec_check'] = $_SESSION['inhouse_auction_dates'][$auction_id]['f_elec_check'];
			$_SESSION['auction_buyers_prem'] = $_SESSION['inhouse_auction_dates'][$auction_id]['f_buyers_prem'];

			// Create $_SESSION['seller_details'] ...
			$params = array(
				'tbl_name' => $this->config->item('inhouse_seller_details'),
				'where'	=> array( 'f_id' => $_SESSION['dates_id_lookup'][ $date_current_sql ] ),
				'sess_name'	=> 'seller_details',
				'key_fld'	=> 'f_seller_id',
			);
			make_session_ftn($params);

			// Create $_SESSION['lot_details'] ...
			$params = array(
				'tbl_name'	=> $this->config->item('inhouse_lot_details'),
				'where'	=> array( 'f_id' => $_SESSION['dates_id_lookup'][ $date_current_sql ] ),
				'sess_name'	=> 'lot_details',
				//@@@@@@@@@@@@@@@
				'key_fld'	=> 'f_lot_no',
			);
			make_session_ftn($params);
		}
		// END PRINT PAGE





		/*
		|--------------------------------------------------------------------------
		| SEARCH PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('search' == $page) {
			// $_SESSION['validation_errors'] = '';
		}
		// END SEARCH PAGE





		/*
		|--------------------------------------------------------------------------
		| WEBSITE PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('website' == $page) {
			$todays_date = date("Y-m-d");

			// Get t_active_auction & t_auction_dates...
			if ( !isset( $_SESSION['active_auction']) ) {
				$params = array(
					'tbl_name'	=> $this->config->item('active_auction'),
					'sess_name'	=> 'active_auction',
					'key_fld'	=> 'f_auction_type',
					'val_fld'	=> 'f_auction_active'
				);
				make_session_ftn($params);
				/*
				[cat] => f, c or n
				[gen] => y or n
				 */

				$params = array(
					'tbl_name'	=> $this->config->item('auction_dates'),
					'sess_name'	=> 'auction_dates',
					'key_fld'	=> 'f_auction_type',
					'val_fld'	=> 'f_auction_date'
				);
				make_session_ftn($params);
				/*
				[catalogue] => 2017-04-04
				[general] => 2017-03-28
				 */
			}

			// Get most recent catalogue & general auction dates ...
			$auction_dates = array();
			// This is `t_inhouse_auction_dates`
			foreach ($_SESSION['inhouse_auction_dates'] as $val) {
				if ('g' == $val['f_auction_type']) {
					$auction_dates['g'][] = $val['f_auction_date'];
				}
				elseif ('a' == $val['f_auction_type']) {
					$auction_dates['a'][] = $val['f_auction_date'];
				}
			}

			rsort($auction_dates['g'], 2);
			$last_gen_date = $auction_dates['g'][0];
			rsort($auction_dates['a'], 2);
			$last_cat_date = $auction_dates['a'][0];

			/*
			|--------------------------------------------------------------------------
			| Delete t_catalogue contents if `t_inhouse_auction_dates.f_auction_type` = 'a' AND
			| `t_inhouse_auction_dates.f_auction_date` > `t_auction_dates.f_auction_date`.
			| Copy most recent `t_inhouse_lot details` (`f_auction_type` = 'a') to t_catalogue
			| and update `t_auction_dates' catalogue date.
			| NOTE: If `t_inhouse_auction_dates.f_auction_date` = `t_auction_dates.f_auction_date`
			| and the date is not in the past, only copy new lots (don't overwrite existing content
			| that's already been copied). Only do this once per session.
			|
			*/

			// echo $_SESSION['auction_dates']['catalogue'].' <= '.$last_cat_date;
			// echo $_SESSION['auction_dates']['catalogue'] <= $last_cat_date ? 'TRUE' : 'FALSE';

			$tbl = $this->config->item('catalogue');
			// If $last_cat_date is more recent or equal to $_SESSION['auction_dates']['catalogue'] & not in past
			if ($_SESSION['auction_dates']['catalogue'] <= $last_cat_date && $todays_date < $last_cat_date ) {
				// If $last_cat_date is more recent than $_SESSION['auction_dates']['catalogue']
				if ($_SESSION['auction_dates']['catalogue'] < $last_cat_date) {

					// TRUNCATE t_catalogue contents
					$this->db->truncate($tbl);

					// Update t_auction_dates date (catalogue)
					$params = array(
						'update' => array(
							'f_auction_date' => $last_cat_date,
							'f_edit_date' => $todays_date,
						),
						'where' => array('f_auction_type' => 'catalogue'),
					);
					$this->smythes_model->update($this->config->item('auction_dates'), $params);
					$_SESSION['auction_dates']['catalogue'] = $last_cat_date;
				}

				// COPY t_inhouse_lot details WHERE f_id = $cat_id to t_catalogue
				// NOTE: Only copies non existing items ...
				$cat_id = $_SESSION['dates_id_lookup'][ $last_cat_date ];
				$this->smythes_model->copy($tbl, $cat_id);
			}



			//@@@
			// $last_cat_date = '2018-03-29';

			$tbl = $this->config->item('general');
			// If $last_gen_date is more recent or equal to $_SESSION['auction_dates']['general'] & not in past
			if ($_SESSION['auction_dates']['general'] <= $last_gen_date && $todays_date < $last_gen_date) {
				// If $last_gen_date is more recent than $_SESSION['auction_dates']['general']
				if ($_SESSION['auction_dates']['general'] < $last_gen_date) {
					// TRUNCATE t_general contents
					$this->db->truncate($tbl);
					//@@@
					// echo "truncate $tbl<br>";

					// Update t_auction_dates date (general)
					$params = array(
						'update' => array(
							'f_auction_date' => $last_gen_date,
							'f_edit_date' => $todays_date,
						),
						'where' => array('f_auction_type' => 'general'),
					);

					$this->smythes_model->update($this->config->item('auction_dates'), $params);
					$_SESSION['auction_dates']['general'] = $last_gen_date;
				}

				// COPY t_inhouse_lot details WHERE f_id = $gen_id to t_general
				// NOTE: Only copies non existing items ...
				$gen_id = $_SESSION['dates_id_lookup'][ $last_gen_date ];
				$this->smythes_model->copy($tbl, $gen_id);
			}

			make_session_ftn(array('tbl_name'=>$this->config->item('catalogue'),'sess_name'=>'catalogue','key_fld'=>'f_lot_no'));
			make_session_ftn(array('tbl_name'=>$this->config->item('general'),'sess_name'=>'general','key_fld'=>'f_lot_no'));
			make_session_ftn(array('tbl_name'=>$this->config->item('unassigned'),'sess_name'=>'unassigned','key_fld'=>'f_lot_no'));

			ksort($_SESSION['catalogue']);
			ksort($_SESSION['general']);

			$view = $lookup ? $lookup : '';

			$_SESSION['edit_view'] = $view;

			$data['tmp_array'] = array();
			if ('gen' == $_SESSION['edit_view']) {
				$data['tmp_array'] = $_SESSION['general'];
				$path = 'general_lots';
				$lots_date = $_SESSION['auction_dates']['general'].'_';
			}
			elseif ('cat' == $_SESSION['edit_view']) {
				$data['tmp_array'] = $_SESSION['catalogue'];
				$path = 'catalogue_lots';
				$lots_date = $_SESSION['auction_dates']['catalogue'].'_';
			}
			elseif ('una' == $_SESSION['edit_view']) {
				$data['tmp_array'] = $_SESSION['unassigned'];
				$path = 'unassigned_lots';
				$lots_date = '';
			}

			//@@@
			// Append 'codeig-smythes/public_html/' to $doc_root when running on 'development' server.
			$sub_url = 'development' == ENVIRONMENT ? str_replace('http://localhost/', '', site_url()) : '';
			$doc_root = "{$_SERVER['DOCUMENT_ROOT']}/$sub_url";

			foreach ($data['tmp_array'] as $val) {
				$img_url = "public/upload/$path/thumbs/{$lots_date}{$val['f_lot_no']}.jpg";

				// echo '<pre>'; print_r(strlen($val['f_lot_no'])); echo '</pre>';

				//@@@
				// echo '<pre>'; print_r( $doc_root.$img_url ); echo '</pre>'; die();

				if (file_exists( $doc_root.$img_url )) {
					$data['tmp_array'][$val['f_lot_no']]['img_url'] = $img_url;
				}
				else {
					$data['tmp_array'][$val['f_lot_no']]['img_url'] = '&nbsp;';
				}
			}

			// die();

			//@@@
			// echo '<pre>'; print_r($data['tmp_array']); echo '</pre>'; die();

			$data['anchor'] = isset($lookup) ? $lookup : '';
		}
		// END WEBSITE PAGE




		/*
		|--------------------------------------------------------------------------
		| WEBSITE_DIARY PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('website_diary' == $page) {
			make_session_ftn(array('tbl_name'=>$this->config->item('auction_diary'),'sess_name'=>'diary','key_fld'=>'f_date'));

			$year_month_arr = array();
			foreach ($_SESSION['diary'] as $val) {
				$year_month = substr($val['f_date'], 0, -3);
				$year_month_arr[$year_month][] = $val['f_date'];
			}

			$count_row = 0;
			foreach ($year_month_arr as $val) {
				if (count($val) > $count_row) {
					$count_row = count($val);
				}
			}

			$data['count_row'] = $count_row;
			$data['tmp_array'] = $year_month_arr;

			// Sort array by key ['2017-07', '2017-08', '2017-09']  etc...
			ksort($data['tmp_array']);

			// echo '<pre>'; print_r($data['tmp_array']); echo '</pre>'; die();
		}




		/*
		|--------------------------------------------------------------------------
		| WEBSITE_ACTIVE PAGE
		|--------------------------------------------------------------------------
		|
		*/
		elseif ('website_active' == $page) {
			make_session_ftn(array('tbl_name'=>$this->config->item('catalogue'),'sess_name'=>'catalogue','key_fld'=>'f_lot_no'));
			ksort($_SESSION['catalogue']);

			$data['cat_total'] = count($_SESSION['catalogue']);
			$data['home_cat_total'] = 0;
			$data['home_cat_lots'] = array();
			foreach ($_SESSION['catalogue'] as $val) {
				if ('' != $val['f_lot_info']) {
					$data['home_cat_total']++;
					$data['home_cat_lots'][] = "{$val['f_lot_no']} - {$val['f_lot_name']}";
				}
			}

			make_session_ftn(array('tbl_name'=>$this->config->item('general'),'sess_name'=>'general','key_fld'=>'f_lot_no'));
			ksort($_SESSION['general']);

			$data['gen_total'] = count($_SESSION['general']);
			$data['home_gen_total'] = 0;
			$data['home_gen_lots'] = array();
			foreach ($_SESSION['general'] as $val) {
				if ('' != $val['f_lot_info']) {
					$data['home_gen_total']++;
					$data['home_gen_lots'][] = "{$val['f_lot_no']} - {$val['f_lot_name']}";
				}
			}

			// echo '<pre>'; print_r($data['home_gen_lots']); echo '</pre>'; die();

			// echo "<pre>CATALOGUE LOTS:\n(total:#{$data['cat_total']} - homepage:#{$data['home_cat_total']})</pre>";
			// echo "<pre>GENERAL LOTS:\n(total:#{$data['gen_total']} - homepage:#{$data['home_gen_total']})</pre>";

			// echo '<pre>'; print_r($_SESSION['catalogue']); echo '</pre>'; die();
			// foreach ($_SESSION as $key => $val) {
			// 	echo "$key<br>";
			// }
			// die();
		}

		$_SESSION['data'] = $data;
		$this->load->view('common/smythes-main-layout', $data);
	}
}