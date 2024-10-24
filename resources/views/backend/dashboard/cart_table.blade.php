<div class="col-lg-12">
    <div class="row gutters-16" >
        <div class="dashboard-box px-0 mb-2rem overflow-hidden" style="background:aliceblue;">
            <h3 style="margin-left: 5px;">Cart Table</h3>
             @php
                     $lang = App::getLocale();
                    $carts = App\Models\Cart::with('user', 'product')
                        ->orderBy('id', 'desc')
                        ->take(20)
                        ->get();
                    // $user = App\Models\User::Where('user_id', $$carts->user_id);
            @endphp
            <table class="col-lg-12 table table-bordered table-striped" style="width:100%; text-align:center;">
                <thead>
                    <th>Cart Id</th>
                    <th>User Id</th>
                    <th>Product Id</th>
                    <th>Price</th>
                    <th>Time</th>
                </thead>
                @php
                    $sl=1;
                @endphp
                @foreach($carts as $cart)
                    <tr>
                        <td>{{ $sl }}</td>
                        <td>{{ $cart->user->name ?? 'N/A' }}</td>
                        <td>{{ $cart->product->name ?? 'N/A' }}</td>
                        <td>{{ $cart->price }}</td>
                        <td>{{ $cart->created_at }}</td>
                    </tr>
                @php
                $sl++;
                @endphp
                @endforeach
            </table>
        </div>
    </div>
</div>


