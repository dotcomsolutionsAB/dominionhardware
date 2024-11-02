<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\OrderDetail;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\SmsTemplate;
use Auth;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Utility\NotificationUtility;
use CoreComponentRepository;
use App\Utility\SmsUtility;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_orders|view_inhouse_orders|view_seller_orders|view_pickup_point_orders'])->only('all_orders');
        $this->middleware(['permission:view_order_details'])->only('show');
        $this->middleware(['permission:delete_order'])->only('destroy','bulk_order_delete');
    }

    // All Orders
    public function all_orders(Request $request)
    {

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';

        $orders = Order::orderBy('id', 'desc');
        $admin_user_id = User::where('user_type', 'admin')->first()->id;


        if (
            Route::currentRouteName() == 'inhouse_orders.index' &&
            Auth::user()->can('view_inhouse_orders')
        ) {
            $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
        } else if (
            Route::currentRouteName() == 'seller_orders.index' &&
            Auth::user()->can('view_seller_orders')
        ) {
            $orders = $orders->where('orders.seller_id', '!=', $admin_user_id);
        } else if (
            Route::currentRouteName() == 'pick_up_point.index' &&
            Auth::user()->can('view_pickup_point_orders')
        ) {
            if (get_setting('vendor_system_activation') != 1) {
                $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
            }
            $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');
            if (
                Auth::user()->user_type == 'staff' &&
                Auth::user()->staff->pick_up_point != null
            ) {
                $orders->where('shipping_type', 'pickup_point')
                    ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id);
            }
        } else if (
            Route::currentRouteName() == 'all_orders.index' &&
            Auth::user()->can('view_all_orders')
        ) {
            if (get_setting('vendor_system_activation') != 1) {
                $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
            }
        } else {
            abort(403);
        }

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
                ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        }
        $orders = $orders->paginate(15);
        return view('backend.sales.index', compact('orders', 'sort_search', 'payment_status', 'delivery_status', 'date'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();
        return view('backend.sales.show', compact('order', 'delivery_boys'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    // public function store(Request $request)
    // {
    //     \Log::info('OrderController@store started');
    //     $carts = Cart::where('user_id', Auth::user()->id)
    //         ->get();

    //     if ($carts->isEmpty()) {
    //         flash(translate('Your cart is empty'))->warning();
    //         return redirect()->route('home');
    //     }

    //     $address = Address::where('id', $carts[0]['address_id'])->first();
    //     $weight = 0;
    //     $shippingAddress = [];
    //     if ($address != null) {
    //         $shippingAddress['name']        = Auth::user()->name;
    //         $shippingAddress['email']       = Auth::user()->email;
    //         $shippingAddress['address']     = $address->address;
    //         $shippingAddress['country']     = $address->country->name;
    //         $shippingAddress['state']       = $address->state->name;
    //         $shippingAddress['city']        = $address->city->name;
    //         $shippingAddress['postal_code'] = $address->postal_code;
    //         $shippingAddress['phone']       = $address->phone;
    //         $shippingAddress['gstin']       = $address->gstin;
    //         if ($address->latitude || $address->longitude) {
    //             $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
    //         }
    //     }

    //     $combined_order = new CombinedOrder;
    //     $combined_order->user_id = Auth::user()->id;
    //     $combined_order->shipping_address = json_encode($shippingAddress);
    //     $combined_order->save();

    //     $seller_products = array();
    //     foreach ($carts as $cartItem) {
    //         $product_ids = array();
    //         $product = Product::find($cartItem['product_id']);
    //         if (isset($seller_products[$product->user_id])) {
    //             $product_ids = $seller_products[$product->user_id];
    //         }
    //         array_push($product_ids, $cartItem);
    //         $seller_products[$product->user_id] = $product_ids;
    //     }

    //     foreach ($seller_products as $seller_product) {
    //         $order = new Order;
    //         $order->combined_order_id = $combined_order->id;
    //         $order->user_id = Auth::user()->id;
    //         $order->shipping_address = $combined_order->shipping_address;

    //         $order->additional_info = $request->additional_info;

    //         // $order->shipping_type = $carts[0]['shipping_type'];
    //         // if ($carts[0]['shipping_type'] == 'pickup_point') {
    //         //     $order->pickup_point_id = $cartItem['pickup_point'];
    //         // }
    //         // if ($carts[0]['shipping_type'] == 'carrier') {
    //         //     $order->carrier_id = $cartItem['carrier_id'];
    //         // }

    //         $order->payment_type = $request->payment_option;
    //         $order->delivery_viewed = '0';
    //         $order->payment_status_viewed = '0';
    //         $order->code = date('Ymd-His') . rand(10, 99);
    //         $order->date = strtotime('now');
    //         $order->save();

    //         $shiprocket_payment_mode = 'Prepaid';
    //         if($order->payment_type == 'cash_on_delivery'){
    //             $shiprocket_payment_mode = 'cod';
    //         }

    //         $subtotal = 0;
    //         $tax = 0;
    //         $shipping = 0;
    //         $coupon_discount = 0;

    //         //Order Details Storing
    //         foreach ($seller_product as $cartItem) {
    //             $product = Product::find($cartItem['product_id']);

    //             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
    //             $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
    //             $coupon_discount += $cartItem['discount'];

    //             $product_variation = $cartItem['variation'];

    //             $product_stock = $product->stocks->where('variant', $product_variation)->first();
    //             if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
    //                 flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
    //                 $order->delete();
    //                 return redirect()->route('cart')->send();
    //             } elseif ($product->digital != 1) {
    //                 $product_stock->qty -= $cartItem['quantity'];
    //                 $product_stock->save();
    //             }

    //             $weight += $product->weight * $cartItem['quantity'];

    //             $order_detail = new OrderDetail;
    //             $order_detail->order_id = $order->id;
    //             $order_detail->seller_id = $product->user_id;
    //             $order_detail->product_id = $product->id;
    //             $order_detail->variation = $product_variation;
    //             $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
    //             $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
    //             $order_detail->shipping_type = $cartItem['shipping_type'];
    //             $order_detail->product_referral_code = $cartItem['product_referral_code'];
    //             $order_detail->shipping_cost = $cartItem['shipping_cost'];

    //             $shipping += $order_detail->shipping_cost;
    //             //End of storing shipping cost
    //             $line_item_sr['name'] = $product->name;
    //             $line_item_sr['sku'] = $product->sku;
    //             $line_item_sr['units'] = $cartItem['quantity'];
    //             $line_item_sr['selling_price'] =cart_product_price($cartItem, $product, false, false)+cart_product_tax($cartItem, $product, false);
    //             $line_item_sr['discount'] = 0;
    //             $line_item_sr['tax'] = "18";
    //             $line_item_sr['hsn'] = "";

    //             $order_items_arr[] = $line_item_sr;

    //             $order_detail->quantity = $cartItem['quantity'];

    //             if (addon_is_activated('club_point')) {
    //                 $order_detail->earn_point = $product->earn_point;
    //             }
                
    //             $order_detail->save();

    //             $product->num_of_sale += $cartItem['quantity'];
    //             $product->save();

    //             $order->seller_id = $product->user_id;
    //             $order->shipping_type = $cartItem['shipping_type'];
                
    //             if ($cartItem['shipping_type'] == 'pickup_point') {
    //                 $order->pickup_point_id = $cartItem['pickup_point'];
    //             }
    //             if ($cartItem['shipping_type'] == 'carrier') {
    //                 $order->carrier_id = $cartItem['carrier_id'];
    //             }

    //             if ($product->added_by == 'seller' && $product->user->seller != null) {
    //                 $seller = $product->user->seller;
    //                 $seller->num_of_sale += $cartItem['quantity'];
    //                 $seller->save();
    //             }

    //             if (addon_is_activated('affiliate_system')) {
    //                 if ($order_detail->product_referral_code) {
    //                     $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

    //                     $affiliateController = new AffiliateController;
    //                     $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
    //                 }
    //             }
    //         }
    //         $cod_fee = 0;
    //         $shiping=0;

    //         if($request->payment_option == 'cash_on_delivery')
    //         {
    //             if ($subtotal <= 5000) { // Example: charge a COD fee for orders over 1000
    //                 $cod_fee = 100;
    //                 // $shiping = 100;  // Example COD fee
    //             }else{
    //                 $cod_fee = $subtotal * 0.02;
    //                 // $shipping = $subtotal * 0.02;
    //             }
    //             // $tax += (0.18 * $cod_fee);
    //         }

    //         if ($subtotal < 5000) {
    //             $shiping = 100;  // Example COD fee
    //         }else{
    //             $shipping = $subtotal * 0.02;
    //         }

    //         $tax += (0.18 * $shipping);

    //         $grand_total = $subtotal + $tax + $shipping + $cod_fee ;
    //         $rounded_grand_total = round($grand_total);
    //         $round_off = $rounded_grand_total - $grand_total;

    //         $order->cod_fee = $cod_fee;
    //         $order->total_shipping = $shipping;
    //         $order->tax = $tax;
    //         $order->round_off = $round_off;
    //         $order->grand_total = $rounded_grand_total;

    //         if ($seller_product[0]->coupon_code != null) {
    //             $order->coupon_discount = $coupon_discount;
    //             $order->grand_total -= $coupon_discount;

    //             $coupon_usage = new CouponUsage;
    //             $coupon_usage->user_id = Auth::user()->id;
    //             $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
    //             $coupon_usage->save();
    //         }

    //         $combined_order->grand_total += $order->grand_total;

    //           // if (!isset($shippingAddress['name'])) {
    //         //     throw new Exception("Undefined array key 'name' in \$shippingAddress");
    //         // }

    //         // Shiprocket Integration
    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/auth/login',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS =>'{
    //             "email": "kmohammed2307@gmail.com",
    //             "password": "yp$duLBeZjE7qAn"
    //         }',
    //         // CURLOPT_POSTFIELDS =>'{
    //         //     "email": "dotcomsolutions.apps@gmail.com",
    //         //     "password": "Rh]DqqHR4/<=#"
    //         // }',
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json'
    //         ),
    //         ));

    //         $response = curl_exec($curl);
    //         curl_close($curl);

    //         $responseArray = json_decode($response, true);
    //         $token = $responseArray['token'];

    //         $order->token = $token;

    //         // "pickup_location" : "Dot Com Solutions",
    //         // "channel_id" : "342406", 

    //         // "pickup_location": "Global M",
    //         // "channel_id": "825274",

    //         // if($shipping = 100){
    //         //     $order->grand_total=$order->grand_total-$shipping;
    //         // }

    //         $post_fields = '{
    //             "order_id": "'.$order->code.'",
    //             "order_date": "'.date('Y-m-d', $order->date).'",
                

    //             "comment": "Order from E-Commerce Website",

    //             "pickup_location":"SHOWROOM",
    //             "channel_id":"244252",

    //             "reseller_name": "Dominion Hardware Stores",
    //             "company_name": "'.$shippingAddress['name'].'",
    //             "billing_customer_name": "'.$shippingAddress['name'].' ",
    //             "billing_last_name": "",
    //             "billing_address": "'.$shippingAddress['address'].'",
    //             "billing_address_2": "",
    //             "billing_isd_code": "",
    //             "billing_city": "'.$shippingAddress['city'].'",
    //             "billing_pincode": "'.$shippingAddress['postal_code'].'",
    //             "billing_state": "'.$shippingAddress['state'].'",
    //             "billing_country": "'.$shippingAddress['country'].'",
    //             "billing_email": "'.$shippingAddress['email'].'",
    //             "billing_phone": "'.$shippingAddress['phone'].'",
    //             "billing_alternate_phone":"",
    //             "shipping_is_billing": true,
    //             "shipping_customer_name": "",
    //             "shipping_last_name": "",
    //             "shipping_address": "",
    //             "shipping_address_2": "",
    //             "shipping_city": "",
    //             "shipping_pincode": "",
    //             "shipping_country": "",
    //             "shipping_state": "",
    //             "shipping_email": "",
    //             "shipping_phone": "",
    //             "order_items": '.json_encode($order_items_arr).',
    //             "payment_method": "'.$shiprocket_payment_mode.'",
    //             "shipping_charges": "'.$shipping.'",
    //             "cod_charges":"'.$cod_fee.'",
    //             "giftwrap_charges": 0,
    //             "transaction_charges": 0,
    //             "total_discount": 0,
    //             "sub_total": '.(($shipping == 100) ? ($order->grand_total - $shipping) : $order->grand_total).',
    //             "length": 10,
    //             "breadth": 10,
    //             "height": 15,
    //             "weight": '.$weight.',
    //             "ewaybill_no": "",
    //             "customer_gstin": "",
    //             "invoice_number":"",
    //             "order_type":""
    //         }';

    //         //Punch Order to Shiprocket
    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $post_fields,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json',
    //             'Authorization: Bearer '.$token
    //         ),
    //         ));

    //         $response = curl_exec($curl);

    //         curl_close($curl);

    //         $order->save();
    //     }

    //     $combined_order->save();

    //     foreach($combined_order->orders as $order){
    //         NotificationUtility::sendOrderPlacedNotification($order);
    //     }

    //     $request->session()->put('combined_order_id', $combined_order->id);
    //     \Log::info('Set combined_order_id in session', ['combined_order_id' => session('combined_order_id')]);
    // }

    public function store(Request $request)
    {
        \Log::info('OrderController@store started');

        // Get cart items for the authenticated user
        $carts = Cart::where('user_id', Auth::user()->id)->get();

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        // Fetch the address associated with the cart's first item
        $address = Address::where('id', $carts[0]['address_id'])->first();
        $weight = 0;
        $shippingAddress = [];

        if ($address !== null) {
            $shippingAddress = [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'address' => $address->address,
                'country' => $address->country->name ?? 'N/A',
                'state' => $address->state->name ?? 'N/A',
                'city' => $address->city->name ?? 'N/A',
                'postal_code' => $address->postal_code,
                'phone' => $address->phone,
                'gstin' => $address->gstin,
                'lat_lang' => ($address->latitude && $address->longitude) 
                                ? $address->latitude . ',' . $address->longitude 
                                : null
            ];
        }

        // Create a new combined order
        $combined_order = new CombinedOrder;
        $combined_order->user_id = Auth::user()->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        // Organize cart items by seller
        $seller_products = [];
        foreach ($carts as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $seller_products[$product->user_id][] = $cartItem;
        }

        // Process each seller's product group
        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::user()->id;
            $order->shipping_address = $combined_order->shipping_address;
            $order->additional_info = $request->additional_info;
            $order->payment_type = $request->payment_option;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->save();

            $shiprocket_payment_mode = $order->payment_type === 'cash_on_delivery' ? 'cod' : 'Prepaid';
            $subtotal = $tax = $shipping = $coupon_discount = 0;

            $order_items_arr = [];

            // Store each product's order details
            foreach ($seller_product as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                // Update product stock
                $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
                if ($product->digital !== 1 && $cartItem['quantity'] > $product_stock->qty) {
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    $order->delete();
                    return redirect()->route('cart')->send();
                } elseif ($product->digital !== 1) {
                    $product_stock->qty -= $cartItem['quantity'];
                    $product_stock->save();
                }

                // Update product weight
                $weight += $product->weight * $cartItem['quantity'];

                // Store order details
                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $cartItem['variation'];
                $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $cartItem['shipping_cost'];
                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                // Update order item data for Shiprocket integration
                $line_item_sr = [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'units' => $cartItem['quantity'],
                    'selling_price' => cart_product_price($cartItem, $product, false, false) + cart_product_tax($cartItem, $product, false),
                    'discount' => 0,
                    'tax' => 18,
                    'hsn' => ''
                ];

                $order_items_arr[] = $line_item_sr;
                $shipping += $order_detail->shipping_cost;
            }

            // Calculate fees and taxes
            $cod_fee = $request->payment_option === 'cash_on_delivery' ? ($subtotal <= 5000 ? 100 : $subtotal * 0.02) : 0;
            $shipping_fee = $subtotal < 5000 ? 100 : $subtotal * 0.02;
            $tax += 0.18 * $shipping_fee;

            // Calculate grand total
            $grand_total = $subtotal + $tax + $shipping_fee + $cod_fee;
            $order->cod_fee = $cod_fee;
            $order->total_shipping = $shipping_fee;
            $order->tax = $tax;
            $order->round_off = round($grand_total) - $grand_total;
            $order->grand_total = round($grand_total);

            $combined_order->grand_total += $order->grand_total;

            // Save coupon usage if applied
            if (!empty($seller_product[0]->coupon_code)) {
                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                $coupon_usage->save();
            }

            // Shiprocket Integration
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/auth/login',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(['email' => 'kmohammed2307@gmail.com', 'password' => 'yp$duLBeZjE7qAn']),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            $token = json_decode($response, true)['token'];
            $order->token = $token;

            $post_fields = json_encode([
                "order_id" => $order->code,
                "order_date" => date('Y-m-d', $order->date),
                "comment" => "Order from E-Commerce Website",
                "pickup_location" => "SHOWROOM",
                "channel_id" => "244252",
                "reseller_name" => "Dominion Hardware Stores",
                "company_name" => $shippingAddress['name'],
                "billing_customer_name" => $shippingAddress['name'],
                "billing_address" => $shippingAddress['address'],
                "billing_city" => $shippingAddress['city'],
                "billing_pincode" => $shippingAddress['postal_code'],
                "billing_state" => $shippingAddress['state'],
                "billing_country" => $shippingAddress['country'],
                "billing_email" => $shippingAddress['email'],
                "billing_phone" => $shippingAddress['phone'],
                "order_items" => $order_items_arr,
                "payment_method" => $shiprocket_payment_mode,
                "shipping_charges" => $shipping_fee,
                "cod_charges" => $cod_fee,
                "sub_total" => $order->grand_total,
                "length" => 10,
                "breadth" => 10,
                "height" => 15,
                "weight" => $weight
            ]);

            // Punch Order to Shiprocket
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post_fields,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $token]
            ]);
            curl_exec($curl);
            curl_close($curl);

            $order->save();
        }

        $combined_order->save();

        // Notify user of order placement
        foreach ($combined_order->orders as $order) {
            NotificationUtility::sendOrderPlacedNotification($order);
        }

        $request->session()->put('combined_order_id', $combined_order->id);
        \Log::info('Set combined_order_id in session', ['combined_order_id' => session('combined_order_id')]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                try {

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                } catch (\Exception $e) {
                }

                $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();
        return view('seller.order_details_seller', compact('order'));
    }

    public function update_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        // Check if the delivery status is empty and the new status is 'confirmed'
        if (empty($order->inv_no) && $request->status == "confirmed") {

            // Get the last order with the same prefix in the invoice number
            $lastOrder = Order::where('inv_no', 'LIKE', 'DH/ES/%')
                            ->orderBy('inv_no', 'desc') // or use 'id' if applicable
                            ->first();

            if ($lastOrder) {
                // Get the last generated inv_no
                $inv_no = $lastOrder->inv_no;

                // Remove "DH/ES/" from the beginning and "/24-25" from the end to get the numeric part
                $count = str_replace(array("DH/ES/", "/24-25"), "", $inv_no);

                // Increment the numeric part
                $count = intval($count) + 1;

                // Generate the new inv_no
                $prefix = "DH/ES/";
                $postfix = "/24-25";
                $new_inv_no = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT) . $postfix;
                // $new_inv_no = $prefix . "0" . $postfix;
                // Set the generated inv_no to the order
                $order->inv_no = $new_inv_no;

                echo "New inv_no: " . $new_inv_no . "<br>";
            }
        }

        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if (addon_is_activated('affiliate_system')) {
                    if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                        $orderDetail->product_referral_code
                    ) {

                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($request->status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($request->status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
        }
        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        return 1;
    }

    public function update_tracking_code(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->tracking_code = $request->tracking_code;
        $order->save();

        return 1;
    }

    public function update_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if (
            $order->payment_status == 'paid' &&
            $order->commission_calculated == 0
        ) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }
        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date("Y-m-d H:i:s");
            $order->save();

            $delivery_history = \App\Models\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\Models\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (env('MAIL_USERNAME') != null && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = translate('You are assigned to delivery an order. Order code') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {
                }
            }

            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'assign_delivery_boy')->first()->status == 1) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {
                }
            }
        }

        return 1;
    }
}
