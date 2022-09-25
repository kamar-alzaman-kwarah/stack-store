<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends Controller
{

    public function favorite(){
        $like=Like::where('user_id',Auth::id())->get();
        $data=[];
        $counter=0;
        foreach($like as $li){
             
            $product=Product::where('id',$li->product_id)
            ->where('expiration_date', '>=' , date('Y-n-j'))
            ->select('id','name','image_url','views', 'like', 'price' , 'current_price' , 'created_at')
            ->get()->first();
            
            $list_discounts=$product->discounts()->get();
            foreach($list_discounts as $discount){
            if(Carbon::now() >= Carbon::parse($discount->discount_date)){
                    $product->current_price=$product->price -
                    ($product->price*($discount->discount_value/100));
            }
            }
            $product->update(['like'=>1]);
            $data[$counter]=$product;
            $counter++;
            
        }
        
        return response()->json([$data ,Response::HTTP_OK]);
    }

    public function store(Request $request , Product $product)
    {
        $like=Like::where('user_id',Auth::id())-> where('product_id' , $product->id)->first();
        if($like == null) {
            $product->likes()->create([
                'user_id'=>Auth::id(),
            ]);

            return response()->json([Response::HTTP_OK]);
        }
        return LikeController::destroy($like);
    }

    public function destroy(Like $like)
    {
        if($like->user_id == Auth::id()){
            $like->delete();
            return response()->json($like,Response::HTTP_NO_CONTENT);
        }
        return response()->json($like,401);
    }
}
