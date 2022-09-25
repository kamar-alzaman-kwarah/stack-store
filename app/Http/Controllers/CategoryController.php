<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{

    public function index()
    {
        $category= Category::get();
        return response()->json($category,Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //المستخدم لن يقوم بادخال اصناف
        $value = $request->store;
        $value = json_decode($value, true);
        
        $validator2 = Validator::make($request->all(), [
            'photo'=>'required|mimes:png,jpg,jpeg,bmp,gif',
        ]);
        if($validator2->fails()){
            return response()->json($validator2->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY); //خطا بالداتا المدخلة
        }
        $file_extension = $request->photo->getClientOriginalExtension();
        $file_name = time().'.'.$file_extension;
        $path = 'images/categories';
        $request->photo->move($path, $file_name);
        $image_file = "public/images/categories/$file_name";

        $category= Category::create([
            'name'=> $value['name'],
            'image'=> $file_name
        ]);

        return response()->json($category,Response::HTTP_OK);
    }

    public function show(category $category)
    {
        return response()->json($category,Response::HTTP_OK);
    }

    public function update(Request $request, category $category)
    {
        if($request->has('name')){
            $name = $request->input('name');
            $category->update([
                'name'=> $name,
            ]);
        }

        return response()->json($category,Response::HTTP_OK);
    }

    public function destroy(category $category)
    {
        $category->delete();
        return response()->json($category,Response::HTTP_NO_CONTENT);
    }
}
