<?php
// app/Http/Controllers/GoogleMerchantFeedController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Product; // Assuming your products are in the Product model

class GoogleMerchantFeedController extends Controller
{
    public function generateFeed()
    {
        // Fetch all published products
        $products = Product::where('published', 1)->get();

        // Create the root XML element
        $xml = new \SimpleXMLElement('<rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        // Create the channel element
        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'Global Mart');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Power Tools');

        // Iterate through products and their stocks
        foreach ($products as $product) {
            foreach ($product->stocks as $stock) { // Assuming 'stocks' is the relationship method name
                $item = $channel->addChild('item');
                $item->addChild('g:id', $stock->sku, 'http://base.google.com/ns/1.0');
                $item->addChild('g:title', htmlspecialchars($product->name, ENT_XML1, 'UTF-8') . ' - ' . $stock->sku, 'http://base.google.com/ns/1.0');
                $item->addChild('g:description', htmlspecialchars($product->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                $item->addChild('g:link', url('/product/' . htmlspecialchars($product->slug, ENT_XML1, 'UTF-8') . '?id=' . $stock->sku), 'http://base.google.com/ns/1.0');
                $item->addChild('g:image_link', uploaded_asset($product->thumbnail_img), 'http://base.google.com/ns/1.0');
                $item->addChild('g:price', round($stock->price + ($stock->price * 0.18)) . ' INR', 'http://base.google.com/ns/1.0');
                $item->addChild('g:google_product_category', '111', 'http://base.google.com/ns/1.0');
                $item->addChild('g:brand', $product->brand->name, 'http://base.google.com/ns/1.0');
                $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
                $item->addChild('g:availability', 'in_stock', 'http://base.google.com/ns/1.0');
            }
        }

        // Return the XML response
        return Response::make($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }
}
