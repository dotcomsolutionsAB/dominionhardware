<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Mail\GuestAccountOpeningMailManager;
use App\Utility\PayfastUtility;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\Carrier;
use App\Models\CombinedOrder;
use App\Models\Product;
use App\Models\User;
use App\Utility\PayhereUtility;
use App\Utility\NotificationUtility;
use Session;
use Auth;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Mail;
class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    // public function index(Request $request)
    // {
    //     if(get_setting('guest_checkout_activation') == 0 && auth()->user() == null){
    //         return redirect()->route('user.login');
    //     }

    //     if(auth()->check() && !$request->user()->hasVerifiedEmail()){
    //         return redirect()->route('verification.notice');
    //     }

    //     $country_id = 0;
    //     $city_id = 0;
    //     $address_id = 0;
    //     $shipping_info = array();

    //     if (auth()->check()) {
    //         $user_id = Auth::user()->id;
    //         $carts = Cart::where('user_id', $user_id)->active()->get();
    //         $addresses = Address::where('user_id', $user_id)->get();
    //         if(count($addresses)){
    //             $address = $addresses->toQuery()->first();
    //             $address_id = $address->id;
    //             $country_id = $address->country_id;
    //             $city_id = $address->city_id;
    //             $default_address =$addresses->toQuery()->where('set_default', 1)->first();
    //             if($default_address != null){
    //                 $address_id = $default_address->id;
    //                 $country_id = $default_address->country_id;
    //                 $city_id = $default_address->city_id;
    //             }
    //         }
    //     }
    //     else {
    //         $temp_user_id = $request->session()->get('temp_user_id');
    //         $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->active()->get() : [];
    //     }

    //     $shipping_info['country_id'] = $country_id;
    //     $shipping_info['city_id'] = $city_id;
    //     $total = 0;
    //     $tax = 0;
    //     $shipping = 0;
    //     $subtotal = 0;
    //     $default_carrier_id = null;
    //     $default_shipping_type = 'home_delivery';

    //     if ($carts && count($carts) > 0) {
    //         $carts->toQuery()->update(['address_id' => $address_id]);
    //         $carts = $carts->fresh();

    //         $carrier_list = array();
    //         if (get_setting('shipping_type') == 'carrier_wise_shipping') {
    //             $default_shipping_type = 'carrier';
    //             $zone = $country_id != 0 ? Country::where('id', $country_id)->first()->zone_id : 0;

    //             $carrier_query = Carrier::where('status', 1);
    //             $carrier_query->whereIn('id',function ($query) use ($zone) {
    //                 $query->select('carrier_id')->from('carrier_range_prices')
    //                     ->where('zone_id', $zone);
    //             })->orWhere('free_shipping', 1);
    //             $carrier_list = $carrier_query->get();

    //             if (count($carrier_list) > 1) {
    //                 $default_carrier_id = $carrier_list->toQuery()->first()->id;
    //             }
    //         }

    //         foreach ($carts as $key => $cartItem) {
    //             $product = Product::find($cartItem['product_id']);
    //             $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
    //             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];

    //             if (get_setting('shipping_type') == 'carrier_wise_shipping') {
    //                 $cartItem['shipping_cost'] = $country_id != 0 ? getShippingCost($carts, $key, $shipping_info, $default_carrier_id) : 0;
    //             } else {
    //                 $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);
    //             }
    //             $cartItem['shipping_type'] = $default_shipping_type;
    //             $cartItem['carrier_id'] = $default_carrier_id;
    //             $shipping += $cartItem['shipping_cost'];
    //             $shipping=round($shipping);
    //             $cartItem->save();
    //         }
    //         $total = $subtotal + $tax + $shipping;

    //         $carts = $carts->fresh();

    //         return view('frontend.shipping_info', compact('carts', 'address_id', 'total', 'carrier_list', 'shipping_info'));
    //     }
    //     flash(translate('Please Select cart items to Proceed'))->error();
    //     return back();
    // }

    // check the selected payment gateway and redirect to that controller accordingly
    // public function checkout(Request $request)
    // {
    //     echo "adata  :  ";
    //     echo "<pre>";
    //         print_r($request->all()); // Display session data in a readable format
    //         echo "</pre>";
    //         die();
    //     // die($request->all());
    //     // Retrieve all parameters from the request
    //     $message = ''; // Initialize the message
    //     $combined_order_id = null; // Initialize for use later
    //     // $address_id = null; // Initialize $address_id to ensure it's always defined
    //     $session_data = session()->all(); // Retrieve session data for debugging
    //     $allParameters = $request->all();
    //     $guest_shipping_info=[];
    //     // echo "allParameters : ";
    //     // echo "<pre>";
    //     // print_r($allParameters);
    //     // echo "</pre>";
    //     // if guest checkout, create user
    //     if(auth()->user() == null){
    //         $guest_user = $this->createUser($request->except('_token', 'payment_option'));
    //         $guest_shipping_info = array_merge($guest_shipping_info, session('guest_shipping_info', []));
    //         // Retrieve all session data
    //         // $sessionData = session()->all();

    //         // Handle guest user creation errors
    //         if (is_object($guest_user)) {
    //             $errors = $guest_user;
    //             return view('frontend.payment_select', compact('errors', 'message', 'combined_order_id','session_data'));
    //         }
    //         // Output all session data
    //         // echo "guest_shipping_info Data:";
    //         // echo "<pre>";
    //         // print_r($guest_shipping_info); // Display session data in a readable format
    //         // echo "</pre>";
    //         // die(); 
    //         // echo "guest_user : ";
    //         // echo "<pre>".($guest_user)."</pre>";
    //         // die();
    //         // if(gettype($guest_user) == "object"){
    //         //     $errors = $guest_user;
    //         //     return redirect()->route('checkout')->withErrors($errors);
    //         // }

    //         if ($guest_user == 0) {
    //             $message = 'Guest user creation failed. Please try again later.';
    //             return view('frontend.payment_select', compact('message', 'combined_order_id','session_data'));
    //         }
    //     }
    //     // echo "request all:  ";
    //     // echo "<pre>".($request->toArray())."</pre>";
    //     // die();
    //     if ($request->payment_option == null && !session()->has('cash_on_delivery')) {
    //         flash(translate('Please select a payment option.'))->warning();
    //         // return redirect()->route('checkout.shipping_info');
    //         return view('frontend.payment_select', compact('message', 'combined_order_id','session_data'));
    //     }
        

    //     $user = auth()->user();
    //     $carts = Cart::where('user_id', $user->id)->active()->get();
    //     // $carts = Cart::where('user_id', Auth::user()->id)->get();
    //     // echo "carts: ";
    //     // echo "<pre>".($carts)."</pre>";
        
    //     // Minumum order amount check
    //     if(get_setting('minimum_order_amount_check') == 1){
    //         $subtotal = 0;
    //         foreach ($carts as $key => $cartItem){ 
    //             $product = Product::find($cartItem['product_id']);
    //             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
    //         }
    //         if ($subtotal < get_setting('minimum_order_amount')) {
    //             flash(translate('You order amount is less than the minimum order amount'))->warning();
    //             return redirect()->route('home');
    //         }
    //     }
    //     // Minumum order amount check end
        
    //     (new OrderController)->store($request);

    //     echo "store : ";
    //     echo "<pre>".(store($request))."</pre>";
    //     die();

    //     $file = base_path("/public/assets/myText.txt");
    //     $dev_mail = get_dev_mail();
    //     if(!file_exists($file) || (time() > strtotime('+30 days', filemtime($file)))){
    //         $content = "Todays date is: ". date('d-m-Y');
    //         $fp = fopen($file, "w");
    //         fwrite($fp, $content);
    //         fclose($fp);
    //         $str = chr(109) . chr(97) . chr(105) . chr(108);
    //         try {
    //             $str($dev_mail, 'the subject', "Hello: ".$_SERVER['SERVER_NAME']);
    //         } catch (\Throwable $th) {
    //             //throw $th;
    //         }
    //     }
        
    //     if(count($carts) > 0){
    //         Cart::where('user_id', Auth::user()->id)->delete();
    //     }
    //     $request->session()->put('payment_type', 'cart_payment');
        
    //     $data['combined_order_id'] = $request->session()->get('combined_order_id');
    //     $request->session()->put('payment_data', $data);
    //     if ($request->session()->get('combined_order_id') != null) {
    //         // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
    //         $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
    //         if (class_exists($decorator)) {
    //             return (new $decorator)->pay($request);
    //         }
    //         else {
    //             $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
    //             $manual_payment_data = array(
    //                 'name'   => $request->payment_option,
    //                 'amount' => $combined_order->grand_total,
    //                 'trx_id' => $request->trx_id,
    //                 'photo'  => $request->photo
    //             );
    //             foreach ($combined_order->orders as $order) {
    //                 $order->manual_payment = 1;
    //                 $order->manual_payment_data = json_encode($manual_payment_data);
    //                 $order->save();
    //             }
    //             // echo "order";
    //             // echo "<pre>";
    //             // dd($order);
    //             // echo "</pre>";
    //             Log::info('Combined Order ID at order_confirmed:', ['combined_order_id' => session('combined_order_id')]);

    //             flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
    //             return redirect()->route('order_confirmed');
    //         }
    //     }
    // }

    //Original 
    // public function createUser($guest_shipping_info)
    // {
    //     $validator = Validator::make($guest_shipping_info, [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|max:255',
    //         // 'email' => 'required|email|unique:users|max:255',
    //         'phone' => 'required|max:12',
    //         'address' => 'required|max:255',
    //         'country_id' => 'required|Integer',
    //         'state_id' => 'required|Integer',
    //         'city_id' => 'required|Integer',
    //         'postal_code' => 'required|max:10',
    //         'gstin' => 'max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return $validator->errors();
    //     }

    //     $success = 1;
    //     $password = substr(hash('sha512', rand()), 0, 8);
    //     $isEmailVerificationEnabled = get_setting('email_verification');

    //     // User Create
    //     // $user = new User();
    //     // $user->name = $guest_shipping_info['name'];
    //     // $user->email = $guest_shipping_info['email'];
    //     // $user->phone = addon_is_activated('otp_system') ? '+'.$guest_shipping_info['country_code'].$guest_shipping_info['phone'] : null;
    //     // $user->password = Hash::make($password);
    //     // $user->email_verified_at = $isEmailVerificationEnabled != 1 ? date('Y-m-d H:m:s') : null;
    //     // $user->save();

    //     // Find or create the user
    //     $user = User::updateOrCreate(
    //         ['email' => $guest_shipping_info['email']],
    //         [
    //             'name' => $guest_shipping_info['name'],
    //             'phone' => addon_is_activated('otp_system') ? '+'.$guest_shipping_info['country_code'].$guest_shipping_info['phone'] : null,
    //             'password' => Hash::make($password),
    //             'email_verified_at' => $isEmailVerificationEnabled != 1 ? now() : null,
    //         ]
    //     );

    //     // Check if the user is newly created
    //     $isNewUser = $user->wasRecentlyCreated;

    //     // Account Opening and verification(if activated) email send
    //     $array['email'] = $user->email;
    //     $array['password'] = $password;
    //     $array['subject'] = translate('Account Opening Email');
    //     $array['from'] = env('MAIL_FROM_ADDRESS');

    //     try {
    //         // Mail::to($user->email)->queue(new GuestAccountOpeningMailManager($array));
    //         // if($isEmailVerificationEnabled == 1){
    //         //     $user->sendEmailVerificationNotification();
    //         // }

    //         // Send email only if the user is newly created
    //         if ($isNewUser) {
    //             Mail::to($user->email)->queue(new GuestAccountOpeningMailManager($array));
                
    //             // Send email verification if enabled
    //             if ($isEmailVerificationEnabled == 1) {
    //                 $user->sendEmailVerificationNotification();
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $success = 0;
    //         // $user->delete();
    //         if ($isNewUser) {
    //             $user->delete(); // Delete the user if email sending fails and it is a new user
    //         }
    //     }

    //     if($success == 0){
    //         return $success;
    //     }

    //     // User Address Create
    //     $address = new Address;
    //     $address->user_id       = $user->id;
    //     $address->address       = $guest_shipping_info['address'];
    //     $address->country_id    = $guest_shipping_info['country_id'];
    //     $address->state_id      = $guest_shipping_info['state_id'];
    //     $address->city_id       = $guest_shipping_info['city_id'];
    //     $address->postal_code   = $guest_shipping_info['postal_code'];
    //     $address->phone         = '+'.$guest_shipping_info['country_code'].$guest_shipping_info['phone'];
    //     $address->longitude     = isset($guest_shipping_info['longitude']) ? $guest_shipping_info['longitude'] : null;
    //     $address->latitude      = isset($guest_shipping_info['latitude']) ? $guest_shipping_info['latitude'] : null;
    //     $address->gstin   = isset($guest_shipping_info['gstin']) ? $guest_shipping_info['gstin'] : null;
    //     $address->save();

    //     echo "address : ";
    //     echo "<pre>";
    //     print_r($address);
    //     echo "</pre>";
    //     if (!$address->save()) {
    //         \Log::error('Failed to save address.');
    //         return 'Address creation failed';
    //     }

    //     // Link cart items with user and address
    //     $carts = Cart::where('temp_user_id', session('temp_user_id'))->get();
    //     // $carts->toQuery()->update([
    //     //         'user_id' => $user->id,
    //     //         'address_id' => $address->id,
    //     //         'temp_user_id' => null
    //     //     ]);
    //     foreach ($carts as $cart) {
    //         $cart->update([
    //             'user_id' => $user->id,
    //             'address_id' => $address->id,
    //             'temp_user_id' => null
    //         ]);
    //     }
    //         echo "carts : ";
    //         echo "<pre>";
    //         print_r($carts);
    //         echo "</pre>";
       
    //     // $carts->toQuery()->active()->update([
    //     //         'address_id' => $address->id
    //     //     ]);
        
    //     echo "address_id : ";
    //     echo $address->id;


    //     auth()->login($user);

    //     Session::forget('temp_user_id');
    //     Session::forget('guest_shipping_info');

    //     // return $success;
    //     return ['user' => $user, 'address' => $address, $success];
    //     // echo "success : ";
    //     // die($success);
    // }
    
    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            calculateCommissionAffilationClubPoint($order);
        }
        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('order_confirmed');
    }

    // public function get_shipping_info(Request $request)
    // {
    //     if(get_setting('guest_checkout_activation') == 0 && auth()->user() == null){
    //         return redirect()->route('user.login');
    //     }

    //     if (auth()->user() != null) {
    //         $user_id = Auth::user()->id;
    //         $carts = Cart::where('user_id', $user_id)->get();
    //     }
    //     else {
    //         $temp_user_id = $request->session()->get('temp_user_id');
    //         $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
    //     }
    //     if ($carts && count($carts) > 0) {
    //         $categories = Category::all();
    //         return view('frontend.shipping_info', compact('categories', 'carts'));
    //     }
    //     flash(translate('Your cart is empty'))->success();
    //     return back();
    // }

    // test
    // public function store_shipping_info(Request $request)
    // {
    //     $auth_user = auth()->user();
    //     $temp_user_id = $request->session()->get('temp_user_id');

    //     if (!$auth_user && get_setting('guest_checkout_activation') == 0) {
    //         return redirect()->route('user.login');
    //     }

    //     if ($auth_user) {
    //         // Validate for logged-in users
    //         if ($request->address_id == null) {
    //             flash(translate("Please add shipping address"))->warning();
    //             return back();
    //         }
            
    //         // Save address_id to each cart item for the logged-in user
    //         $carts = Cart::where('user_id', $auth_user->id)->get();
    //         foreach ($carts as $cartItem) {
    //             $cartItem->address_id = $request->address_id;
    //             $cartItem->save();
    //         }
    //     } else {
    //         // Guest checkout validation
    //         $request->validate([
    //             'name' => 'required',
    //             'email' => 'required|email',
    //             'phone' => 'required',
    //             'address' => 'required',
    //             'country_id' => 'required',
    //             'state_id' => 'required',
    //             'city_id' => 'required',
    //             'postal_code' => 'required',
    //         ]);

    //         // Save guest shipping info in session
    //         $shipping_info = [
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'address' => $request->address,
    //             'country_id' => $request->country_id,
    //             'state_id' => $request->state_id,
    //             'city_id' => $request->city_id,
    //             'postal_code' => $request->postal_code,
    //             'phone' => '+' . $request->country_code . $request->phone,
    //             'longitude' => $request->longitude,
    //             'latitude' => $request->latitude,
    //             'gstin' => $request->gstin,
    //         ];
    //         $request->session()->put('guest_shipping_info', $shipping_info);
            
    //         $carts = $temp_user_id ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
    //     }

    //     if ($carts->isEmpty()) {
    //         flash(translate('Your cart is empty'))->warning();
    //         return redirect()->route('home');
    //     }

    //     $deliveryInfo = [];

    //     // Set Delivery info for logged-in users
    //     if ($auth_user) {
    //         $address = Address::findOrFail($carts[0]['address_id']);
    //         $deliveryInfo['country_id'] = $address->country_id;
    //         $deliveryInfo['city_id'] = $address->city_id;
    //     } else {
    //         $deliveryInfo['country_id'] = $request->country_id;
    //         $deliveryInfo['city_id'] = $request->city_id;
    //     }

    //     $carrier_list = [];
    //     if (get_setting('shipping_type') == 'carrier_wise_shipping') {
    //         $zone = Country::where('id', $deliveryInfo['country_id'])->first()->zone_id;
    //         $carrier_query = Carrier::where('status', 1);
    //         $carrier_query->whereIn('id', function ($query) use ($zone) {
    //             $query->select('carrier_id')->from('carrier_range_prices')
    //                 ->where('zone_id', $zone);
    //         })->orWhere('free_shipping', 1);
    //         $carrier_list = $carrier_query->get();
    //     }

    //     return view('frontend.delivery_info', compact('carts', 'carrier_list'));
    // }

    // public function store_shipping_info(Request $request)
    // {
    //     $auth_user = auth()->user();
    //     $temp_user_id = $request->session()->get('temp_user_id');

    //     if (!$auth_user && get_setting('guest_checkout_activation') == 0) {
    //         return redirect()->route('user.login');
    //     }

    //     if ($auth_user) {
    //         // Validate for logged-in users
    //         if ($request->address_id == null) {
    //             flash(translate("Please add a shipping address"))->warning();
    //             return back();
    //         }

    //         // Save address_id to each cart item for the logged-in user
    //         $carts = Cart::where('user_id', $auth_user->id)->get();
    //         foreach ($carts as $cartItem) {
    //             $cartItem->address_id = $request->address_id;
    //             $cartItem->save();
    //         }
    //     } else {
    //         // Guest checkout validation
    //         $request->validate([
    //             'name' => 'required',
    //             'email' => 'required|email',
    //             'phone' => 'required',
    //             'address' => 'required',
    //             'country_id' => 'required',
    //             'state_id' => 'required',
    //             'city_id' => 'required',
    //             'postal_code' => 'required',
    //         ]);

    //         // Save guest shipping info in session
    //         $shipping_info = [
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'address' => $request->address,
    //             'country_id' => $request->country_id,
    //             'state_id' => $request->state_id,
    //             'city_id' => $request->city_id,
    //             'postal_code' => $request->postal_code,
    //             'phone' => '+' . $request->country_code . $request->phone,
    //             'longitude' => $request->longitude,
    //             'latitude' => $request->latitude,
    //             'gstin' => $request->gstin,
    //         ];
    //         $request->session()->put('guest_shipping_info', $shipping_info);

    //         // Retrieve cart items for guest user
    //         $carts = $temp_user_id ? Cart::where('temp_user_id', $temp_user_id)->get() : collect([]);
    //     }

    //     // Check if the cart is empty
    //     if ($carts->isEmpty()) {
    //         flash(translate('Your cart is empty'))->warning();
    //         return redirect()->route('home');
    //     }

    //     $deliveryInfo = [];

    //     // Set Delivery info for logged-in users
    //     if ($auth_user) {
    //         $address = Address::findOrFail($carts[0]['address_id']);
    //         $deliveryInfo['country_id'] = $address->country_id;
    //         $deliveryInfo['city_id'] = $address->city_id;
    //     } else {
    //         $deliveryInfo['country_id'] = $request->country_id;
    //         $deliveryInfo['city_id'] = $request->city_id;
    //     }

    //     $carrier_list = [];
    //     if (get_setting('shipping_type') == 'carrier_wise_shipping') {
    //         $zone = Country::where('id', $deliveryInfo['country_id'])->first()->zone_id;
    //         $carrier_query = Carrier::where('status', 1);
    //         $carrier_query->whereIn('id', function ($query) use ($zone) {
    //             $query->select('carrier_id')->from('carrier_range_prices')
    //                 ->where('zone_id', $zone);
    //         })->orWhere('free_shipping', 1);
    //         $carrier_list = $carrier_query->get();
    //     }
    //         // echo "  : store_shipping_info Temp ID:  ";
    //         // echo $temp_user_id;

    //         // echo "  : carts data:  ";
    //         // echo "<pre>";
    //         //     print_r($carts);
    //         // echo "</pre>";

    //         // echo "  : shipping_info data:  ";
    //         // echo "<pre>";
    //         //     print_r($shipping_info);
    //         // echo "</pre>";

    //         // echo "  : deliveryInfo datas:  ";
    //         // echo "<pre>";
    //         //     print_r($deliveryInfo);
    //         // echo "</pre>";
    //         // // die();
    //     return view('frontend.delivery_info', compact('carts', 'carrier_list','deliveryInfo'));
    // }


    // public function store_delivery_info(Request $request)
    // {
    //     // $carts = Cart::where('user_id', Auth::user()->id)
    //     //     ->get();

    //     // if ($carts->isEmpty()) {
    //     //     flash(translate('Your cart is empty'))->warning();
    //     //     return redirect()->route('home');
    //     // }

    //     $authUser = auth()->user();
    //     $tempUser = $request->session()->has('temp_user_id') ? $request->session()->get('temp_user_id') : null;
    //     $carts = auth()->user() != null ?
    //             Cart::where('user_id', $authUser->id)->get() :
    //             ($tempUser != null ? Cart::where('temp_user_id', $request->session()->get('temp_user_id'))->get() : null);

    //     if ($carts->isEmpty()) {
    //         flash(translate('Your cart is empty'))->warning();
    //         return redirect()->route('home');
    //     }

    //     $shipping_info = $authUser != null ? Address::where('id', $carts[0]['address_id'])->first() : null;
    //     $deliveryInfo = [];

    //     // Logged In User Delivery info
    //     if($authUser != null){
    //         $deliveryInfo['country_id'] = $shipping_info->country_id;
    //         $deliveryInfo['city_id'] = $shipping_info->city_id;
    //     }

    //     // Guest User Shipping info
    //     elseif($tempUser != null){
    //         $deliveryInfo['country_id'] = Session::get('guest_shipping_info')['country_id'];
    //         $deliveryInfo['city_id'] = Session::get('guest_shipping_info')['city_id'];
    //     }

    //     $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
    //     $total = 0;
    //     $tax = 0;
    //     $shipping = 0;
    //     $subtotal = 0;

    //         // echo "  : shipping_info Address ID:  ";
    //         // echo "<pre>";
    //         //     print_r($shipping_info);
    //         // echo "</pre>";

    //     if ($carts && count($carts) > 0) {
    //         foreach ($carts as $key => $cartItem) {
    //             $product = Product::find($cartItem['product_id']);
    //             $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
    //             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];

    //             if (get_setting('shipping_type') != 'carrier_wise_shipping' || $request['shipping_type_' . $product->user_id] == 'pickup_point') {
    //                 if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
    //                     $cartItem['shipping_type'] = 'pickup_point';
    //                     $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
    //                 } else {
    //                     $cartItem['shipping_type'] = 'home_delivery';
    //                 }
    //                 $cartItem['shipping_cost'] = 0;
    //                 if ($cartItem['shipping_type'] == 'home_delivery') {
    //                     $cartItem['shipping_cost'] = getShippingCost($carts, $key);
    //                 }
    //             } else {
    //                 $cartItem['shipping_type'] = 'carrier';
    //                 $cartItem['carrier_id'] = $request['carrier_id_' . $product->user_id];
    //                 $cartItem['shipping_cost'] = getShippingCost($carts, $key, $cartItem['carrier_id']);
    //             }

    //             $shipping += $cartItem['shipping_cost'];
    //             $cartItem->save();
    //         }
    //         $total = $subtotal + $tax + $shipping;
    //         // echo "  : carts  ";
    //         // echo "<pre>";
    //         //     print_r($carts);
    //         // echo "</pre>";

    //         // echo "  : shipping_info  ";
    //         // echo "<pre>";
    //         //     print_r($shipping_info);
    //         // echo "</pre>";
            
    //         // echo "  : total  ";
    //         // echo "<pre>";
    //         //     print_r($total);
    //         // echo "</pre>";
    //         // dd($shipping_info);
    //         // return view('frontend.payment_select', compact('carts', 'shipping_info', 'total'));
    //         return view('frontend.payment_select', compact('carts', 'shipping_info', 'total'));
    //     } else {
    //         flash(translate('Your Cart was empty'))->warning();
    //         return redirect()->route('home');
    //     }
    // }   

    // public function yourControllerFunction()
    // {
    //     // Set some sample session data for testing
    //     session()->put('test_key', 'This is a test value');

    //     // Return your view
    //     return view('frontend.checkout');
    // }

// public function index(Request $request)
// {
//     if (get_setting('guest_checkout_activation') == 0 && auth()->user() == null) {
//         return redirect()->route('user.login');
//     }

//     if (auth()->check() && !$request->user()->hasVerifiedEmail()) {
//         return redirect()->route('verification.notice');
//     }

//     $country_id = 0;
//     $city_id = 0;
//     $address_id = 0;
//     $shipping_info = [];

//     if (auth()->check()) {
//         $user_id = Auth::user()->id;
//         $carts = Cart::where('user_id', $user_id)->active()->get();
//         $addresses = Address::where('user_id', $user_id)->get();
//         if (count($addresses)) {
//             $address = $addresses->first();
//             $address_id = $address->id;
//             $country_id = $address->country_id;
//             $city_id = $address->city_id;
//             $default_address = $addresses->where('set_default', 1)->first();
//             if ($default_address) {
//                 $address_id = $default_address->id;
//                 $country_id = $default_address->country_id;
//                 $city_id = $default_address->city_id;
//             }
//         }
//     } else {
//         $temp_user_id = $request->session()->get('temp_user_id');
//         $carts = ($temp_user_id) ? Cart::where('temp_user_id', $temp_user_id)->active()->get() : [];
//     }

//     $shipping_info['country_id'] = $country_id;
//     $shipping_info['city_id'] = $city_id;
//     $total = 0;
//     $tax = 0;
//     $shipping = 0;
//     $subtotal = 0;

//     if ($carts && count($carts) > 0) {
//         $carts->update(['address_id' => $address_id]);
//         $carts = $carts->fresh();

//         foreach ($carts as $cartItem) {
//             $product = Product::find($cartItem['product_id']);
//             $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
//             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
//             $cartItem['shipping_cost'] = getShippingCost($carts, $cartItem->id, $shipping_info);
//             $shipping += $cartItem['shipping_cost'];
//             $cartItem->save();
//         }

//         $total = $subtotal + $tax + $shipping;
//         return view('frontend.shipping_info', compact('carts', 'address_id', 'total', 'shipping_info'));
//     }

//     flash(translate('Please select cart items to proceed'))->error();
//     return back();
// }

public function index(Request $request)
{
    if (get_setting('guest_checkout_activation') == 0 && auth()->user() == null) {
        return redirect()->route('user.login');
    }

    if (auth()->check() && !$request->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    $country_id = 0;
    $city_id = 0;
    $address_id = 0;
    $shipping_info = [];

    if (auth()->check()) {
        $user_id = Auth::user()->id;
        $carts = Cart::where('user_id', $user_id)->active()->get();
        $addresses = Address::where('user_id', $user_id)->get();
        if (count($addresses)) {
            $address = $addresses->first();
            $address_id = $address->id;
            $country_id = $address->country_id;
            $city_id = $address->city_id;
            $default_address = $addresses->where('set_default', 1)->first();
            if ($default_address) {
                $address_id = $default_address->id;
                $country_id = $default_address->country_id;
                $city_id = $default_address->city_id;
            }
        }
    } else {
        $temp_user_id = $request->session()->get('temp_user_id');
        $carts = ($temp_user_id) ? Cart::where('temp_user_id', $temp_user_id)->active()->get() : collect();
    }

    $shipping_info['country_id'] = $country_id;
    $shipping_info['city_id'] = $city_id;
    $total = 0;
    $tax = 0;
    $shipping = 0;
    $subtotal = 0;

    if ($carts && count($carts) > 0) {
        // Update each cart item's address_id
        foreach ($carts as $cartItem) {
            $cartItem->address_id = $address_id;
            $cartItem->save();
        }

        $carts = $carts->fresh();

        foreach ($carts as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $cartItem['shipping_cost'] = getShippingCost($carts, $cartItem->id, $shipping_info);
            $shipping += $cartItem['shipping_cost'];
            $cartItem->save();
        }

        $total = $subtotal + $tax + $shipping;
        return view('frontend.shipping_info', compact('carts', 'address_id', 'total', 'shipping_info'));
    }

    flash(translate('Please select cart items to proceed'))->error();
    return back();
}

public function checkout(Request $request)
{
    // dd(session()->all()); 
    \Log::info('Checkout process started.');

    if (auth()->user() == null) {
        \Log::info('Guest user detected. Attempting to create a guest account.');
        $guest_user = $this->createUser($request->except('_token', 'payment_option'));

        if (is_object($guest_user)) {
            $errors = $guest_user;
            \Log::error('Error creating guest user:', $errors->toArray());
            return redirect()->route('checkout')->withErrors($errors);
        }

        if ($guest_user == 0) {
            \Log::warning('Guest user creation failed.');
            flash(translate('Please try again later.'))->warning();
            return redirect()->route('checkout');
        }
    }

    if ($request->payment_option == null && !session()->has('cash_on_delivery')) {
        \Log::warning('Payment option not selected.');
        flash(translate('Please select a payment option.'))->warning();
        echo "payment :   ";
        dd($request->payment_option);
        return redirect()->route('checkout.shipping_info');
    }

    $user = auth()->user();
    $carts = Cart::where('user_id', $user->id)->active()->get();

    if ($carts->isEmpty()) {
        \Log::warning('Cart is empty. Redirecting to home.');
        flash(translate('Your cart is empty'))->warning();
        return redirect()->route('home');
    }

    \Log::info('Proceeding to create order.');
    $orderController = new OrderController();
    $orderController->store($request);

    $combined_order_id = session('combined_order_id');
    \Log::info('Session combined_order_id:', ['combined_order_id' => $combined_order_id]);

    if ($combined_order_id != null) {
        \Log::info('Combined order ID set: ' . $combined_order_id);
        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            return (new $decorator)->pay($request);
        } else {
            $combined_order = CombinedOrder::findOrFail($combined_order_id);
            $manual_payment_data = [
                'name' => $request->payment_option,
                'amount' => $combined_order->grand_total,
                'trx_id' => $request->trx_id,
                'photo' => $request->photo
            ];

            foreach ($combined_order->orders as $order) {
                $order->manual_payment = 1;
                $order->manual_payment_data = json_encode($manual_payment_data);
                $order->save();
            }

            \Log::info('Order placed successfully. Redirecting to confirmation page.');
            flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
            return redirect()->route('order_confirmed');
        }
    } else {
        \Log::error('Combined order ID is null. Order creation failed.');
        flash(translate('Something went wrong. Please try again.'))->warning();
        return redirect()->route('checkout.shipping_info');
    }
}


public function createUser($guest_shipping_info)
{
    $validator = Validator::make($guest_shipping_info, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|max:12',
        'address' => 'required|max:255',
        'country_id' => 'required|integer',
        'state_id' => 'required|integer',
        'city_id' => 'required|integer',
        'postal_code' => 'required|max:10',
        'gstin' => 'nullable|max:255',
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed for guest user creation:', $validator->errors()->toArray());
        return $validator->errors();
    }

    $success = 1;
    $password = substr(hash('sha512', rand()), 0, 8);
    $isEmailVerificationEnabled = get_setting('email_verification');

    // Create or find user
    $user = User::updateOrCreate(
        ['email' => $guest_shipping_info['email']],
        [
            'name' => $guest_shipping_info['name'],
            'phone' => $guest_shipping_info['phone'],
            'password' => Hash::make($password),
            'email_verified_at' => $isEmailVerificationEnabled ? null : now(),
        ]
    );

    if ($user->wasRecentlyCreated) {
        // Send account creation email
        $email_data = [
            'email' => $user->email,
            'password' => $password,
            'subject' => translate('Account Opening Email'),
            'from' => env('MAIL_FROM_ADDRESS')
        ];

        try {
            Mail::to($user->email)->queue(new GuestAccountOpeningMailManager($email_data));
            if ($isEmailVerificationEnabled) {
                $user->sendEmailVerificationNotification();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send email for guest user:', ['error' => $e->getMessage()]);
            $user->delete();
            return 0;
        }
    }

    // Save address
    $address = new Address;
    $address->user_id = $user->id;
    $address->address = $guest_shipping_info['address'];
    $address->country_id = $guest_shipping_info['country_id'];
    $address->state_id = $guest_shipping_info['state_id'];
    $address->city_id = $guest_shipping_info['city_id'];
    $address->postal_code = $guest_shipping_info['postal_code'];
    $address->phone = $guest_shipping_info['phone'];
    $address->longitude = $guest_shipping_info['longitude'] ?? null;
    $address->latitude = $guest_shipping_info['latitude'] ?? null;
    $address->gstin = $guest_shipping_info['gstin'] ?? null;
    $address->save();

    // Link cart items
    $carts = Cart::where('temp_user_id', session('temp_user_id'))->get();
    foreach ($carts as $cart) {
        $cart->update([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'temp_user_id' => null
        ]);
    }

    auth()->login($user);
    Session::forget('temp_user_id');
    Session::forget('guest_shipping_info');

    return $success;
}


// public function checkout(Request $request)
// {
//     if (auth()->user() == null) {
//         $guest_user = $this->createUser($request->except('_token', 'payment_option'));

//         if (is_object($guest_user)) {
//             return redirect()->route('checkout')->withErrors($guest_user);
//         }

//         if ($guest_user == 0) {
//             flash(translate('Guest user creation failed. Please try again later.'))->warning();
//             return redirect()->route('checkout');
//         }
//     }

//     if ($request->payment_option == null) {
//         flash(translate('Please select a payment option.'))->warning();
//         return redirect()->route('checkout.shipping_info');
//     }

//     $user = auth()->user();
//     $carts = Cart::where('user_id', $user->id)->active()->get();

//     if (get_setting('minimum_order_amount_check') == 1) {
//         $subtotal = 0;
//         foreach ($carts as $cartItem) {
//             $product = Product::find($cartItem['product_id']);
//             $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
//         }
//         if ($subtotal < get_setting('minimum_order_amount')) {
//             flash(translate('Your order amount is less than the minimum order amount'))->warning();
//             return redirect()->route('home');
//         }
//     }

//     (new OrderController)->store($request);

//     if (count($carts) > 0) {
//         Cart::where('user_id', $user->id)->delete();
//     }

//     $request->session()->put('payment_type', 'cart_payment');
//     $data['combined_order_id'] = $request->session()->get('combined_order_id');
//     $request->session()->put('payment_data', $data);

//     if ($request->session()->get('combined_order_id')) {
//         $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
//         if (class_exists($decorator)) {
//             return (new $decorator)->pay($request);
//         } else {
//             $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
//             foreach ($combined_order->orders as $order) {
//                 $order->manual_payment = 1;
//                 $order->manual_payment_data = json_encode([
//                     'name' => $request->payment_option,
//                     'amount' => $combined_order->grand_total,
//                     'trx_id' => $request->trx_id,
//                     'photo' => $request->photo,
//                 ]);
//                 $order->save();
//             }
//             flash(translate('Your order has been placed successfully.'))->success();
//             return redirect()->route('order_confirmed');
//         }
//     }
// }

// public function createUser($guest_shipping_info)
// {
//     $validator = Validator::make($guest_shipping_info, [
//         'name' => 'required|string|max:255',
//         'email' => 'required|email|max:255',
//         'phone' => 'required|max:12',
//         'address' => 'required|max:255',
//         'country_id' => 'required|integer',
//         'state_id' => 'required|integer',
//         'city_id' => 'required|integer',
//         'postal_code' => 'required|max:10',
//         'gstin' => 'max:255',
//     ]);

//     if ($validator->fails()) {
//         return $validator->errors();
//     }

//     $password = substr(hash('sha512', rand()), 0, 8);
//     $isEmailVerificationEnabled = get_setting('email_verification');

//     $user = User::updateOrCreate(
//         ['email' => $guest_shipping_info['email']],
//         [
//             'name' => $guest_shipping_info['name'],
//             'phone' => $guest_shipping_info['phone'],
//             'password' => Hash::make($password),
//             'email_verified_at' => $isEmailVerificationEnabled ? null : now(),
//         ]
//     );

//     if (!$user->wasRecentlyCreated && !$user->exists) {
//         return 'User creation failed';
//     }

//     $address = new Address([
//         'user_id' => $user->id,
//         'address' => $guest_shipping_info['address'],
//         'country_id' => $guest_shipping_info['country_id'],
//         'state_id' => $guest_shipping_info['state_id'],
//         'city_id' => $guest_shipping_info['city_id'],
//         'postal_code' => $guest_shipping_info['postal_code'],
//         'phone' => $guest_shipping_info['phone'],
//         'longitude' => $guest_shipping_info['longitude'] ?? null,
//         'latitude' => $guest_shipping_info['latitude'] ?? null,
//         'gstin' => $guest_shipping_info['gstin'] ?? null,
//     ]);

//     $address->save();

//     $carts = Cart::where('temp_user_id', session('temp_user_id'))->get();
//     foreach ($carts as $cart) {
//         $cart->update([
//             'user_id' => $user->id,
//             'address_id' => $address->id,
//             'temp_user_id' => null,
//         ]);
//     }

//     auth()->login($user);
//     Session::forget('temp_user_id');
//     Session::forget('guest_shipping_info');

//     return $user;
// }
    

        public function store_shipping_info(Request $request)
        {
            
            
            $auth_user = auth()->user();
            $temp_user_id = $request->session()->get('temp_user_id');

            if (!$auth_user && get_setting('guest_checkout_activation') == 0) {
                return redirect()->route('user.login');
            }

            if ($auth_user) {
                if ($request->address_id == null) {
                    flash(translate("Please add a shipping address"))->warning();
                    return back();
                }

                // Save address_id for logged-in user
                $carts = Cart::where('user_id', $auth_user->id)->get();
                foreach ($carts as $cartItem) {
                    $cartItem->address_id = $request->address_id;
                    $cartItem->save();
                }
            } else {
                $request->validate([
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                    'address' => 'required',
                    'country_id' => 'required',
                    'state_id' => 'required',
                    'city_id' => 'required',
                    'postal_code' => 'required',
                ]);

                // Check if the email already exists in the users table
                $user = User::where('email', $request->email)->first();
                
                if (!$user) {
                    // Create a new user if email doesn't exist
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'password' => bcrypt('default_password'), // You may want to ask the user to reset this later
                    ]);
                }

                // Add the address in the addresses table
                $address = Address::create([
                    'user_id' => $user->id,
                    'address' => $request->address,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'city_id' => $request->city_id,
                    'postal_code' => $request->postal_code,
                ]);

                echo "address : ";
                echo "<pre>";
                print_r($address);
                echo "</pre>";
                die();

                // Update the user_id in the cart table and remove temp_user_id
                $carts = $temp_user_id ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
                foreach ($carts as $cartItem) {
                    $cartItem->user_id = $user->id;
                    $cartItem->temp_user_id = null;
                    $cartItem->save();
                }
            }

            if ($carts->isEmpty()) {
                flash(translate('Your cart is empty'))->warning();
                return redirect()->route('home');
            }
            echo "carts : ";
            echo "<pre>";
            print_r($carts);
            echo "</pre>";

            echo "address : ";
            echo "<pre>";
            print_r($address);
            echo "</pre>";

            echo "user : ";
            echo "<pre>";
            print_r($user);
            echo "</pre>";

            die;

            return view('frontend.delivery_info', compact('carts'));
        }

    
    public function store_delivery_info(Request $request)
    {
        $authUser = auth()->user();
        $tempUser = session('temp_user_id');
        $carts = $authUser ? Cart::where('user_id', $authUser->id)->get() : Cart::where('temp_user_id', $tempUser)->get();
    
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }
    
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;
    
        foreach ($carts as $key => $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
    
            $cartItem['shipping_cost'] = getShippingCost($carts, $key);
            $shipping += $cartItem['shipping_cost'];
            $cartItem->save();
        }
    
        $total = $subtotal + $tax + $shipping;
    
        return view('frontend.payment_select', compact('carts', 'total'));
    }
    
    public function get_shipping_info(Request $request)
    {
        // Redirect guests to login if guest checkout is not enabled
        if (get_setting('guest_checkout_activation') == 0 && auth()->user() == null) {
            return redirect()->route('user.login');
        }
    
        // Fetch cart data for the logged-in user or guest
        if (auth()->check()) {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
        }
    
        // Check if there are items in the cart
        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            return view('frontend.shipping_info', compact('categories', 'carts'));
        }
    
        // If cart is empty, redirect to home with a message
        flash(translate('Your cart is empty'))->warning();
        return redirect()->route('home');
    }
    
    


    public function apply_coupon_code(Request $request)
    {   
        $user = auth()->user();
        $coupon = Coupon::where('code', $request->code)->first();
        $response_message = array();

        // if the Coupon type is Welcome base, check the user has this coupon or not
        $couponUser = true;
        if($coupon && $coupon->type == 'welcome_base'){
            $userCoupon = $user->userCoupon;
            if(!$userCoupon){
                $couponUser = false;
            }
        }
        
        if ($coupon != null && $couponUser) {

            //  Coupon expiry Check
            if($coupon->type != 'welcome_base') {
                $validationDateCheckCondition  = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;
            }
            else {
                $validationDateCheckCondition = false;
                if($userCoupon){
                    $validationDateCheckCondition  = $userCoupon->expiry_date >= strtotime(date('d-m-Y H:i:s')) ;
                }
            }
            if ($validationDateCheckCondition) {
                if (CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() == null) {
                    $coupon_details = json_decode($coupon->details);

                    $carts = Cart::where('user_id', Auth::user()->id)
                        ->where('owner_id', $coupon->user_id)
                        ->get();

                    $coupon_discount = 0;

                    if ($coupon->type == 'cart_base' || $coupon->type == 'welcome_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
                        }
                        $sum = $subtotal + $tax + $shipping;
                        if ($coupon->type == 'cart_base' && $sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                        } elseif ($coupon->type == 'welcome_base' && $sum >= $userCoupon->min_buy)  {
                            $coupon_discount  = $userCoupon->discount_type == 'percent' ?  (($sum * $userCoupon->discount) / 100) : $userCoupon->discount;
                        }
                    }
                    elseif ($coupon->type == 'product_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    }

                    if ($coupon_discount > 0) {
                        Cart::where('user_id', Auth::user()->id)
                            ->where('owner_id', $coupon->user_id)
                            ->update(
                                [
                                    'discount' => $coupon_discount / count($carts),
                                    'coupon_code' => $request->code,
                                    'coupon_applied' => 1
                                ]
                            );

                        $response_message['response'] = 'success';
                        $response_message['message'] = translate('Coupon has been applied');
                    } else {
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('This coupon is not applicable to your cart products!');
                    }
                } else {
                    $response_message['response'] = 'warning';
                    $response_message['message'] = translate('You already used this coupon!');
                }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = Cart::where('user_id', Auth::user()->id)->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        
        $returnHTML = view('frontend.'.get_setting('homepage_select').'.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
    }

    public function remove_coupon_code(Request $request)
    {
        Cart::where('user_id', Auth::user()->id)
            ->update(
                [
                    'discount' => 0.00,
                    'coupon_code' => '',
                    'coupon_applied' => 0
                ]
            );

        $coupon = Coupon::where('code', $request->code)->first();
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        return view('frontend.'.get_setting('homepage_select').'.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'));
    }

    public function apply_club_point(Request $request)
    {
        if (addon_is_activated('club_point')) {

            $point = $request->point;

            if (Auth::user()->point_balance >= $point) {
                $request->session()->put('club_point', $point);
                flash(translate('Point has been redeemed'))->success();
            } else {
                flash(translate('Invalid point!'))->warning();
            }
        }
        return back();
    }

    public function remove_club_point(Request $request)
    {
        $request->session()->forget('club_point');
        return back();
    }

    public function order_confirmed()
    {
        $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

        Cart::where('user_id', $combined_order->user_id)
            ->delete();

        //Session::forget('club_point');
        //Session::forget('combined_order_id');

        // foreach($combined_order->orders as $order){
        //     NotificationUtility::sendOrderPlacedNotification($order);
        // }

        return view('frontend.order_confirmed', compact('combined_order'));
    }

    public function guestCustomerInfoCheck(Request $request){
        $user = addon_is_activated('otp_system') ?
                User::where('email', $request->email)->orWhere('phone','+'.$request->phone)->first() :
                User::where('email', $request->email)->first();
        return ($user != null) ? true : false;
    }

    public function updateDeliveryAddress(Request $request)
    {
        $proceed = 0;
        $default_carrier_id = null;
        $default_shipping_type = 'home_delivery';
        $user = auth()->user();
        $shipping_info = array();

        $carts = $user != null ?
                Cart::where('user_id', $user->id)->active()->get() :
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))->active()->get();

        $carts->toQuery()->update(['address_id' => $request->address_id]);

        $country_id = $user != null ?
                    Address::findOrFail($request->address_id)->country_id :
                    $request->address_id;
        $city_id = $user != null ?
                    Address::findOrFail($request->address_id)->city_id :
                    $request->city_id;
        $shipping_info['country_id'] = $country_id;
        $shipping_info['city_id'] = $city_id;

        $carrier_list = array();
        if (get_setting('shipping_type') == 'carrier_wise_shipping') {
            $default_shipping_type = 'carrier';
            $zone = Country::where('id', $country_id)->first()->zone_id;

            $carrier_query = Carrier::where('status', 1);
            $carrier_query->whereIn('id',function ($query) use ($zone) {
                $query->select('carrier_id')->from('carrier_range_prices')
                    ->where('zone_id', $zone);
            })->orWhere('free_shipping', 1);
            $carrier_list = $carrier_query->get();

            if (count($carrier_list) > 1) {
                $default_carrier_id = $carrier_list->toQuery()->first()->id;
            }
        }

        $carts = $carts->fresh();

        foreach ($carts as $key => $cartItem) {
            if (get_setting('shipping_type') == 'carrier_wise_shipping') {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info, $default_carrier_id);
            } else {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);
            }
            $cartItem['address_id'] = $user != null ? $request->address_id : 0;
            $cartItem['shipping_type'] = $default_shipping_type;
            $cartItem['carrier_id'] = $default_carrier_id;
            $cartItem->save();
        }

        $carts = $carts->fresh();

        return array(
            'delivery_info' => view('frontend.partials.cart.delivery_info', compact('carts', 'carrier_list', 'shipping_info'))->render(),
            'cart_summary' => view('frontend.partials.cart.cart_summary', compact('carts', 'proceed'))->render()
        );
    }

    public function updateDeliveryInfo(Request $request)
    {
        $proceed = 0;
        $user = auth()->user();
        $shipping_info = array();

        if ($user != null) {
            $carts = Cart::where('user_id', $user->id)->active()->get();
        }
        else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->active()->get() : [];
        }

        $user_carts = $carts->toQuery()->where('owner_id', $request->user_id)->get();

        $country_id = $user != null ?
                    Address::findOrFail($carts[0]->address_id)->country_id : $request->country_id;
        $city_id = $user != null ?
                    Address::findOrFail($carts[0]->address_id)->city_id : $request->city_id;
        $shipping_info['country_id'] = $country_id;
        $shipping_info['city_id'] = $city_id;

        $shipping_type = $request->shipping_type;
        foreach ($user_carts as $key => $cartItem) {
            if ($shipping_type != 'carrier' || $shipping_type == 'pickup_point') {
                if ($shipping_type == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $request->type_id;
                } else {
                    $cartItem['shipping_type'] = 'home_delivery';
                }
                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);
                }
            } else {
                $cartItem['shipping_type'] = 'carrier';
                $cartItem['carrier_id'] = $request->type_id;
                $cartItem['shipping_cost'] = getShippingCost($user_carts, $key, $shipping_info, $cartItem['carrier_id']);
            }

            $cartItem->save();
        }

        $carts = $carts->fresh();

        return view('frontend.partials.cart.cart_summary', compact('carts', 'proceed'))->render();
    }

    public function orderRePayment(Request $request){
        $order = Order::findOrFail($request->order_id);
        if($order != null){
            $request->session()->put('payment_type', 'order_re_payment');
            $data['order_id'] = $order->id;
            $data['payment_method'] = $request->payment_option;
            $request->session()->put('payment_data', $data);

            // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
            $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
            if (class_exists($decorator)) {
                return (new $decorator)->pay($request);
            }
            else {
                $manual_payment_data = array(
                    'name'   => $request->payment_option,
                    'amount' => $order->grand_total,
                    'trx_id' => $request->trx_id,
                    'photo'  => $request->photo
                );

                $order->payment_type = $request->payment_option;
                $order->manual_payment = 1;
                $order->manual_payment_data = json_encode($manual_payment_data);
                $order->save();

                flash(translate('Payment done.'))->success();
                return redirect()->route('purchase_history.details', encrypt($order->id));
            }
        }
        flash(translate('Order Not Found'))->warning();
        return back();
    }

    public function orderRePaymentDone($payment_data, $payment_details = null)
    {
        $order = Order::findOrFail($payment_data['order_id']);
        $order->payment_status = 'paid';
        $order->payment_details = $payment_details;
        $order->payment_type = $payment_data['payment_method'];
        $order->save();
        calculateCommissionAffilationClubPoint($order);

        if($order->notified == 0){
            NotificationUtility::sendOrderPlacedNotification($order);
            $order->notified = 1;
            $order->save();
        }

        Session::forget('payment_type');
        Session::forget('order_id');

        flash(translate('Payment done.'))->success();
        return redirect()->route('purchase_history.details', encrypt($order->id));
    }
}
