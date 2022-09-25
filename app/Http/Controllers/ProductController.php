<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\ProductOperationController;

class ProductController extends Controller
{

    public function index(){
        $product=Product::get();
        $like=Like::where('user_id',Auth::id())->get();
        foreach($product as $pr){
            $list_discounts=$pr->discounts()->get();
            foreach($list_discounts as $discount){
            if(Carbon::now() >= Carbon::parse($discount->discount_date)){
                    $pr->current_price=$pr->price -
                    ($pr->price*($discount->discount_value/100));
            }
            }
            $pr=$pr->update(['like'=>0]);
        }
        foreach($like as $li){
            $product=Product::where('id',$li->product_id)->update(['like'=>1]);
        }
        $product= product::where('expiration_date', '>=' , date('Y-n-j'))
        ->select('id','name','image_url','views', 'like', 'price' , 'current_price' , 'created_at')
        ->get();

        return response()->json($product,Response::HTTP_OK);
    }


    public function store(Request $request)
    {
        $value = $request->store;
        $value = json_decode($value, true);
        $Validator=Validator::make($value,[
            'name'=> ['required','string', 'max:255'],
            'price'=>['required','numeric'],
            'details'=>['required'],
            'quantity'=>['required','numeric'],
            'expiration_date'=>['required','date','after:yesterday'],
            //'period_one'=>['required','numeric','min:1','max:365'],
            //'period_two'=>['required','numeric','min:1','before_or_equal:period_one'],
            //'discounts_one'=>['required','numeric','min:0','max:100'],
            //'discounts_two'=>['required','numeric','after_or_equal:discounts_one','max:100'],
            //'discounts_three'=>['required','numeric','after_or_equal:discounts_two','max:100']
        ]);

        if($Validator->fails()){
            return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY); //خطا بالداتا المدخلة
        }

        $validator2 = Validator::make($request->all(), [
            'photo'=>'required|mimes:png,jpg,jpeg,bmp,gif',
        ]);
        if($validator2->fails()){
            return response()->json($validator2->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY); //خطا بالداتا المدخلة
        }
        $file_extension = $request->photo->getClientOriginalExtension();
        $file_name = time().'.'.$file_extension;
        $path = 'images/products';
        $request->photo->move($path, $file_name);
        $image_file = "public/images/products/$file_name";
        
        $product= Product::create([
            'user_id'=> Auth::id(),
            'name'=> $value['name'],
            'price'=> $value['price'],
            'details'=> $value['details'],
            'image_url'=>$file_name,
            'quantity'=> $value['quantity'],
            'expiration_date'=> $value['expiration_date'],
            'category_id'=> $value['category_id'],
            //'period_one'=> $value['period_one'],
            //'period_two'=> $value['period_two'],
            //'discounts_one'=>$value['discounts_one'],
            //'discounts_two'=>$value['discounts_two'],
            //'discounts_three'=>$value['discounts_three']
        ]);

        foreach($value['list_discounts'] as $discount){
            $product->discounts()->create([
                'discount_date' => $discount['discount_date'],
                'discount_value' => $discount['discount_value']
            ]);
        }

        return response()->json([$product, $image_file],Response::HTTP_OK);
    }

    public function show(Product $product)
    {
        $product->increment('views');

       /* $data['product']['current_price']=$product->price -
        ($product->price*(ProductOperationController::CalculatDiscount($product)/100));*/
        
        $list_discounts=$product->discounts()->get();
        foreach($list_discounts as $discount){
           if(Carbon::now() >= Carbon::parse($discount->discount_date)){
                $product->current_price=$product->price -
                ($product->price*($discount->discount_value/100));
           }
        }

        $product->update(["like"=>0]);
        $like=$product->likes()->where('user_id', Auth::id())->get()->first();
        if($like){
            $product->update([
                "like"=>1
            ]);
        }

        $user['user']=$product->user()->select('id','name','phone_number','facebook_url')->get();

        return response()->json([$product,$user],Response::HTTP_OK);
    }


    public function update(Request $request, Product $product )
    {
        if(Product::where('user_id',Auth::id())){
            if($request->has('expiration_date')){
                return response()->json([
                    "message"=>"you can't change expiration date"
                ] , Response:: HTTP_UNPROCESSABLE_ENTITY);
              }
            else{
                if($request->has('name')){
                    $Validator=Validator::make($request->all(),[
                        'name'=> ['string', 'max:255'],
                    ]);
                    if($Validator->fails()){
                        return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $name = $request->input('name');
                    $product->update([
                        'name'=> $name,
                    ]);
                }

                if($request->has('price')){
                    $Validator=Validator::make($request->all(),[
                        'price'=>['numeric'],
                    ]);
                    if($Validator->fails()){
                        return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $price = $request->input('price');
                    $product->update([
                        'price'=> $price,
                    ]);
                }

                if($request->has('quantity')){
                    $Validator=Validator::make($request->all(),[
                        'quantity'=>['numeric'],
                    ]);
                    if($Validator->fails()){
                        return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $quantity = $request->input('quantity');
                    $product->update([
                        'quantity'=> $quantity,
                    ]);
                }

                if($request->has('details')){
                    $Validator=Validator::make($request->all(),[
                        'details'=>['string'],
                    ]);
                    if($Validator->fails()){
                        return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $details = $request->input('details');
                    $product->update([
                        'details'=> $details,
                    ]);
                }
                $image_file = null;
                if($request->photo){
                    $validator2 = Validator::make($request->all(), [
                        'photo'=>'mimes:png,jpg,jpeg,bmp,gif'
                    ]);
                    if($validator2->fails()){
                        return response()->json($validator2->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY); //خطا بالداتا المدخلة
                    }
                    $file_extension = $request->photo->getClientOriginalExtension();
                    $file_name = time().'.'.$file_extension;
                    $path = 'images/products';
                    $request->photo->move($path, $file_name);
                    $product->update([
                        'image_url'=> $file_name,
                    ]);

                    $image_file = "public/images/products/$file_name";

                }
                $data['product']=$product;
                $data['product']['current_price']=$product->price -
                ($product->price*(ProductOperationController::CalculatDiscount($product)/100));
                if($image_file)
                    $data['photo'] = $image_file;

                return response()->json($data,Response::HTTP_OK);
            }
        }
        return response()->json(["message"=>"you can't access"], 401);
    }

    public function search(Request $request)
    {
        $name = $request->name;
        $categoryId = $request->categoryId;
        $date = $request->date;

        if($categoryId || $name || $date){
          /*  if($category){
                $category = Category::where('name', $category)->select('id')->get()->first();
                $category=$category->id;
            }*/
            $products = Product::where('name', 'LIKE','%'. $name .'%')
            ->where('category_id','LIKE',$categoryId)
            ->where('expiration_date','LIKE',$date)
            ->where('expiration_date', '>=' , date('Y-n-j'))
            ->select('id','name', 'image_url' , 'views')
            ->get();

            if(Empty($products)){
                return response()->json(["message"=>"no result"], Response::HTTP_NO_CONTENT);
            }
            return response()->json($products,Response::HTTP_OK);
        }
    }

    public function sort(Request $request){
        $sort=$request->sort;

        if($sort == "expiration date"){
            $sort="expiration_date";
        }
    
        $product=Product::get();
        $like=Like::where('user_id',Auth::id())->get();
        foreach($product as $pr){
            $list_discounts=$pr->discounts()->get();
            foreach($list_discounts as $discount){
            if(Carbon::now() >= Carbon::parse($discount->discount_date)){
                    $pr->current_price=$pr->price -
                    ($pr->price*($discount->discount_value/100));
            }
            }
            $pr=$pr->update(['like'=>0]);
        }
        foreach($like as $li){
            $product=Product::where('id',$li->product_id)->update(['like'=>1]);
        }
        $product= product::orderBy($sort)
        ->where('expiration_date', '>=' , date('Y-n-j'))
        ->select('id','name','image_url','views', 'like', 'price' , 'current_price' , 'created_at')
        ->get();

        return response()->json($product,Response::HTTP_OK);
    }

    public function destroy(Product $product)
    {
        if($product->user_id != Auth::id()){
            return response()->json(["message"=>"you can't access"], 401 ); //UNAUTHORIZED
        }
        $product->delete();
        return response()->json($product,Response::HTTP_NO_CONTENT);
    }

}
