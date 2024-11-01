<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Auth;
use App\Utility\CartUtility;
use Session;
use Cookie;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // For temp user
        if (auth()->user() != null) {
            $user_id = Auth::user()->id;
            if ($request->session()->get('temp_user_id')) {
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                    ->update(
                        [
                            'user_id' => $user_id,
                            'temp_user_id' => null
                        ]
                    );

                Session::forget('temp_user_id');
            }
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
        }
        if (count($carts) > 0) {
            $carts->toQuery()->update(['shipping_cost' => 0]);
            $carts = $carts->fresh();
        }
        
        return view('frontend.view_cart', compact('carts'));


    }

    public function showCartModal(Request $request)
    {
        $product = Product::find($request->id);

        // Check if the product is found, else return a view with an error message
        if (!$product) {
            return response()->json([
                'status' => 0,
                'message' => 'Product not found.',
            ], 404);
        }

        return view('frontend.'.get_setting('homepage_select').'.partials.addToCart', compact('product'));
    }


    public function showCartModalAuction(Request $request)
    {
        $product = Product::find($request->id);
        return view('auction.frontend.addToCartAuction', compact('product'));
    }

    // public function addToCart(Request $request)
    // {
    //     // For temp user
    //     $authUser = auth()->user();
    //     if($authUser != null) {
    //         $user_id = $authUser->id;
    //         $data['user_id'] = $user_id;
    //         $carts = Cart::where('user_id', $user_id)->get();
    //     } else {
    //         if($request->session()->get('temp_user_id')) {
    //             $temp_user_id = $request->session()->get('temp_user_id');
    //         } else {
    //             $temp_user_id = bin2hex(random_bytes(10));
    //             $request->session()->put('temp_user_id', $temp_user_id);
    //         }
    //         $data['temp_user_id'] = $temp_user_id;
    //         $carts = Cart::where('temp_user_id', $temp_user_id)->get();
    //     }
    //     // $carts = Cart::where('user_id', auth()->user()->id)->get();
    //     $check_auction_in_cart = CartUtility::check_auction_in_cart($carts);
    //     $product = Product::find($request->id);
    //     $carts = array();
        
    //     if($check_auction_in_cart && $product->auction_product == 0) {
    //         return array(
    //             'status' => 0,
    //             'cart_count' => count($carts),
    //             'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.removeAuctionProductFromCart')->render(),
    //             'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
    //         );
    //     }
        
    //     $quantity = $request['quantity'];

    //     if ($quantity < $product->min_qty) {
    //         return array(
    //             'status' => 0,
    //             'cart_count' => count($carts),
    //             'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.minQtyNotSatisfied', ['min_qty' => $product->min_qty])->render(),
    //             'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
    //         );
    //     }

    //     //check the color enabled or disabled for the product
    //     $str = CartUtility::create_cart_variant($product, $request->all());
    //     $product_stock = $product->stocks->where('variant', $str)->first();

    //     // For temp user
    //     if($authUser != null) {
    //         $user_id = $authUser->id;
    //         $cart = Cart::firstOrNew([
    //             'variation' => $str,
    //             'user_id' => $user_id,
    //             'product_id' => $request['id']
    //         ]);
    //     } else {
    //         $temp_user_id = $request->session()->get('temp_user_id');
    //         $cart = Cart::firstOrNew([
    //             'variation' => $str,
    //             'temp_user_id' => $temp_user_id,
    //             'product_id' => $request['id']
    //         ]);
    //     }

    //     $cart = Cart::firstOrNew([
    //         'variation' => $str,
    //         'user_id' => auth()->user()->id,
    //         'product_id' => $request['id']
    //     ]);

    //     if ($cart->exists && $product->digital == 0) {
    //         if ($product->auction_product == 1 && ($cart->product_id == $product->id)) {
    //             return array(
    //                 'status' => 0,
    //                 'cart_count' => count($carts),
    //                 'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.auctionProductAlredayAddedCart')->render(),
    //                 'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
    //             );
    //         }
    //         if ($product_stock->qty < $cart->quantity + $request['quantity']) {
    //             return array(
    //                 'status' => 0,
    //                 'cart_count' => count($carts),
    //                 'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.outOfStockCart')->render(),
    //                 'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
    //             );
    //         }
    //         $quantity = $cart->quantity + $request['quantity'];
    //     }

    //     $price = CartUtility::get_price($product, $product_stock, $request->quantity);
    //     $tax = CartUtility::tax_calculation($product, $price);
        
    //     CartUtility::save_cart_data($cart, $product, $price, $tax, $quantity);
        
    //     // For temp user
    //     if($authUser != null) {
    //         $user_id = $authUser->id;
    //         $carts = Cart::where('user_id', $user_id)->get();
    //     } else {
    //         $temp_user_id = $request->session()->get('temp_user_id');
    //         $carts = Cart::where('temp_user_id', $temp_user_id)->get();
    //     }
    //     // $carts = Cart::where('user_id', auth()->user()->id)->get();
    //     return array(
    //         'status' => 1,
    //         'cart_count' => count($carts),
    //         'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.addedToCart', compact('product', 'cart'))->render(),
    //         'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
    //     );
    // }
    public function addToCart(Request $request)
    {
        $authUser = auth()->user();
        $product = Product::find($request->id);

        // Ensure product exists before proceeding
        if (!$product) {
            return response()->json([
                'status' => 0,
                'message' => 'Product not found.'
            ], 404);
        }

        // Set user ID or temp user ID for guest users
        if ($authUser) {
            $user_id = $authUser->id;
            $data['user_id'] = $user_id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id') ?: bin2hex(random_bytes(10));
            $request->session()->put('temp_user_id', $temp_user_id);
            $data['temp_user_id'] = $temp_user_id;
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        // Check for auction products already in the cart
        $check_auction_in_cart = CartUtility::check_auction_in_cart($carts);
        if ($check_auction_in_cart && $product->auction_product == 0) {
            return [
                'status' => 0,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.removeAuctionProductFromCart')->render(),
                'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
            ];
        }

        // Quantity validation
        $quantity = $request->input('quantity', 1);
        if ($quantity < $product->min_qty) {
            return [
                'status' => 0,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.minQtyNotSatisfied', ['min_qty' => $product->min_qty])->render(),
                'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
            ];
        }

        // Check variant selection and product stock availability
        $variation = CartUtility::create_cart_variant($product, $request->all());
        $product_stock = $product->stocks->where('variant', $variation)->first();
        if (!$product_stock) {
            return [
                'status' => 0,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.outOfStockCart')->render(),
                'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
            ];
        }

        // Find or create cart item based on user or temp user ID
        $cart = Cart::firstOrNew([
            'variation' => $variation,
            'user_id' => $authUser ? $user_id : null,
            'temp_user_id' => $authUser ? null : $temp_user_id,
            'product_id' => $product->id
        ]);

        // Update quantity if cart item already exists
        if ($cart->exists && $product->digital == 0) {
            if ($product->auction_product == 1 && $cart->product_id == $product->id) {
                return [
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.auctionProductAlredayAddedCart')->render(),
                    'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
                ];
            }
            if ($product_stock->qty < $cart->quantity + $quantity) {
                return [
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.outOfStockCart')->render(),
                    'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
                ];
            }
            $quantity = $cart->quantity + $quantity;
        }

        // Calculate price and tax
        $price = CartUtility::get_price($product, $product_stock, $quantity);
        $tax = CartUtility::tax_calculation($product, $price);
        CartUtility::save_cart_data($cart, $product, $price, $tax, $quantity);

        // Refresh cart items based on user or guest status
        if ($authUser) {
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        return [
            'status' => 1,
            'cart_count' => count($carts),
            'modal_view' => view('frontend.'.get_setting('homepage_select').'.partials.addedToCart', compact('product', 'cart'))->render(),
            'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
        ];
    }


    //removes from Cart
    public function removeFromCart(Request $request)
    {
        // Remove the cart item by ID
        Cart::destroy($request->id);

        // Determine the user's cart based on auth status or session temp_user_id
        if (auth()->check()) {
            $user_id = auth()->id();
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            // For guest user
            $temp_user_id = $request->session()->get('temp_user_id');
            
            // Check if temp_user_id exists in session
            if (!$temp_user_id) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No cart found for guest user.'
                ]);
            }

            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        // Return the updated cart view and count
        return [
            'cart_count' => $carts->count(),
            'cart_view' => view('frontend.' . get_setting('homepage_select') . '.partials.cart_details', compact('carts'))->render(),
            'nav_cart_view' => view('frontend.' . get_setting('homepage_select') . '.partials.cart')->render(),
        ];
    }


    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $cartItem = Cart::findOrFail($request->id);

        if ($cartItem['id'] == $request->id) {
            $product = Product::find($cartItem['product_id']);
            $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
            $quantity = $product_stock->qty;
            $price = $product_stock->price;

            //discount calculation
            $discount_applicable = false;

            if ($product->discount_start_date == null) {
                $discount_applicable = true;
            } elseif (
                strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
            ) {
                $discount_applicable = true;
            }

            if ($discount_applicable) {
                if ($product->discount_type == 'percent') {
                    $price -= ($price * $product->discount) / 100;
                } elseif ($product->discount_type == 'amount') {
                    $price -= $product->discount;
                }
            }

            if ($quantity >= $request->quantity) {
                if ($request->quantity >= $product->min_qty) {
                    $cartItem['quantity'] = $request->quantity;
                }
            }

            if ($product->wholesale_product) {
                $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                if ($wholesalePrice) {
                    $price = $wholesalePrice->price;
                }
            }

            $cartItem['price'] = $price;
            $cartItem->save();
        }

        if (auth()->user() != null) {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

        return array(
            'cart_count' => count($carts),
            'cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart_details', compact('carts'))->render(),
            'nav_cart_view' => view('frontend.'.get_setting('homepage_select').'.partials.cart')->render(),
        );
    }

    // public function updateCartStatus(Request $request)
    // {
    //     $product_ids = $request->product_id;

    //     if (auth()->user() != null) {
    //         $user_id = Auth::user()->id;
    //         $carts = Cart::where('user_id', $user_id)->get();
    //     } else {
    //         $temp_user_id = $request->session()->get('temp_user_id');
    //         $carts = Cart::where('temp_user_id', $temp_user_id)->get();
    //     }

    //     $coupon_applied = $carts->toQuery()->where('coupon_applied', 1)->first();
    //     if($coupon_applied != null){
    //         $owner_id = $coupon_applied->owner_id;
    //         $coupon_code = $coupon_applied->coupon_code;
    //         $user_carts = $carts->toQuery()->where('owner_id', $owner_id)->get();
    //         $coupon_discount = $user_carts->toQuery()->sum('discount');
    //         $user_carts->toQuery()->update(
    //             [
    //                 'discount' => 0.00,
    //                 'coupon_code' => '',
    //                 'coupon_applied' => 0
    //             ]
    //         );
    //     }

    //     $carts->toQuery()->update(['status' => 0]);
    //     if($product_ids != null){
    //         if($coupon_applied != null){
    //             $active_user_carts = $user_carts->toQuery()->whereIn('product_id', $product_ids)->get();
    //             if (count($active_user_carts) > 0) {
    //                 $active_user_carts->toQuery()->update(
    //                     [
    //                         'discount' => $coupon_discount / count($active_user_carts),
    //                         'coupon_code' => $coupon_code,
    //                         'coupon_applied' => 1
    //                     ]
    //                 );
    //             }
    //         }

    //         $carts->toQuery()->whereIn('product_id', $product_ids)->update(['status' => 1]);
    //     }
    //     $carts = $carts->fresh();

    //     return view('frontend.partials.cart.cart_details', compact('carts'))->render();
    // }
    
}
