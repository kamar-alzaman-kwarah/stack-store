<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    public function createAccount(Request $request)
    {
        $value = $request->store;
        $value = json_decode($value, true);

        $Validator = Validator::make($value, [
            'name'=> ['required', 'string', 'max:255'],
            'email'=>['required', 'string', 'email', 'unique:users,email'],
            'password'=>['required', 'string', 'min:9'],
            'facebook_url'=>['nullable', 'url'],
            'phone_number'=>['nullable','min:9','max:9']
        ]);
        if($Validator->fails()) {
            return $Validator->errors()->all();
        }

        if(!array_key_exists('facebook_url', $value) && !array_key_exists('phone_number', $value)) {
            $Validator_communication = Validator::make($value, [
                'facebook_url'=>['required'],
                'phone_number'=>['required']
            ]);
            if($Validator_communication->fails()) {
                return $Validator_communication->errors()->all();
            }
        }

        $validator2 = Validator::make($request->all(), [
            'photo'=>'mimes:png,jpg,jpeg,bmp,gif'
        ]);
        if($validator2->fails()) {
            return response()->json($validator2->errors()->all(), Response:: HTTP_UNPROCESSABLE_ENTITY);
        }

        $file_name = null;
        $image_file = null;
        if($request->photo) {
            $file_extension = $request->photo->getClientOriginalExtension();
            $file_name = time().'.'.$file_extension;
            $path = 'images/users';
            $request->photo->move($path, $file_name);
            $image_file = "public/images/users/$file_name";

        }

        $name = $value['name'];
        $email = $value['email'];
        if(array_key_exists('phone_number', $value))
            $phone_number = $value['phone_number'];
        else
            $phone_number = null;
        if(array_key_exists('facebook_url', $value))
            $facebook_url = $value['facebook_url'];
        else
            $facebook_url = null;

        $value['password'] = Hash::make($value['password']);
        $password = $value['password'];

        $user = User::create([
            'name'=> $name,
            'image_url'=> $file_name,
            'email'=> $email,
            'password'=>  $password,
            'phone_number'=> $phone_number,
            'facebook_url'=> $facebook_url,
        ]);

        $tokenResult = $user->createToken('Personal Access Token');
        $data["user"] = $user;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        if($image_file)
            $data['image_file'] = $image_file;

        return response()->json([$data , Response::HTTP_OK]);
    }

    public function login(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'email'=>['required','string','email'],
            'password'=>['required', 'string', 'min:9'],
        ]);
        if($Validator->fails()) {
            return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
        }

        $email = $request->input('email');
        $password = $request->input('password');

        if(!Auth::attempt(['email' => $email, 'password' => $password])){
            return response()->json([["message"=> "Invalid account"] , Response:: HTTP_UNPROCESSABLE_ENTITY]);
        }
        $user=$request->user();
        $tokenResult=$user->createToken('Personal Access Token');

        $data["user"]=$user;
        $data["token_type"]='Bearer';
        $data["access_token"]=$tokenResult->accessToken;

        return response()->json([$data , Response::HTTP_OK]);

    }

    public function Editprofile(Request $request)
    {
        $user=User::where('id',Auth::id());
        
        $Validator=Validator::make($request->all(),[
            'name'=> ['nullable','string', 'max:255'],
            'image_url'=>['nullable', 'max:255', 'min:3'],
            'facebook_url'=>['nullable','string','email'],
            'phone_number'=>['nullable','string']
        ]);
        if($Validator->fails()){
            return $Validator->errors()->all();
        }

        if($request->has('name')){
            $name = $request->input('name');
            $user->update([
                'name'=> $name,
            ]);
        }
        if($request->has('facebook_url')){
            $facebook_url = $request->input('facebook_url');
            $user->update([
                'facebook_url'=> $facebook_url,
            ]);
        }

        if($request->has('phone_number')){
            $phone_number = $request->input('phone_number');
            $user->update([
                'phone_number'=> $phone_number,
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
            $path = 'images/users';
            $request->photo->move($path, $file_name);
            $user->update([
                'image_url'=> $file_name,
            ]);

            $image_file = "public/images/users/$file_name";

        }
        $data['user']=$user;
        if($image_file)
            $data['photo'] = $image_file;


        return response()->json($data,Response::HTTP_OK);
        
    }

    public function showProfile()
    {
        $user=User::where('id',Auth::id())->select('id','name','phone_number','facebook_url', 'image_url')->get();
        return response()->json($user,Response::HTTP_OK);
    }

    public function myProduct(User $user)
    {
        //$user=User::where('id',Auth::id())->get()->first();
        
        $product=$user->products()
        ->select('id','name','image_url','views', 'like', 'price' , 'current_price' , 'created_at')
        ->get();

        foreach($product as $pr){
            $list_discounts=$pr->discounts()->where('product_id',$pr->id)->get();
            foreach($list_discounts as $discount){
            if(Carbon::now() >= Carbon::parse($discount->discount_date)){
                    $pr->current_price=$pr->price -
                    ($pr->price*($discount->discount_value/100));
            }
            }
        }
        
        return response()->json($product,Response::HTTP_OK);
    }

    public function logout(Request $request){
        $token=$request->user()->token();
        $token->revoke();
        return response()->json(['message'=>'logout successfully'],Response::HTTP_OK);
    }

    public function deleteAccount(Request $request, User $user)
    {
        $user->delete();
        return response()->json($user,Response::HTTP_NO_CONTENT);
    }
}
