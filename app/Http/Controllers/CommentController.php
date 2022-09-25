<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{

    public function index(Product $product)
    {
        $comment=Comment::where('product_id',$product->id)->get();

        return response()->json($comment,Response::HTTP_OK);
    }

    public function store(Request $request , Product $product)
    {
        $Validator=Validator::make($request->all(),[
            'comment'=> ['required','min:1','max:512'],
        ]);

        if($Validator->fails()){
            return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
        }

        $data=$product->comments()->create([
            'user_id'=>Auth::id(),
            'comment'=>$request->input('comment'),
        ]);

        return response()->json([$data , Response::HTTP_OK]);
    }

    public function update(Request $request, Comment $comment)
    {
        if($comment->user_id == Auth::id()){
            $Validator=Validator::make($request->all(),[
                'comment'=> ['required'],
            ]);

            if($Validator->fails()){
                return response()->json($Validator->errors()->all() , Response:: HTTP_UNPROCESSABLE_ENTITY);
            }

            $data=$comment->update([
                'comment'=>$request->input('comment'),
            ]);

            return response()->json([$data, Response::HTTP_OK]);
        }

        return response()->json([["message"=>"you can't access"],401]);
    }

    public function destroy(Comment $comment)
    {
        $owner_product=$comment->product()->select('user_id')->first()->user_id;
        if($comment->user_id == Auth::id() ||  $owner_product == Auth::id() ){
            $comment->delete();
            return response()->json($comment,Response::HTTP_NO_CONTENT);
        }
        return response()->json($comment,401);
    }
}
