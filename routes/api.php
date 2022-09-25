<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\HTTP\Controllers\ProductController;
use App\HTTP\Controllers\CategoryController;
use App\HTTP\Controllers\UserController;
use App\Http\Controllers\ProductOperationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('sign-up', [UserController::class , 'createAccount']);
Route::post('login', [UserController::class , 'login']);

Route::middleware(['auth:api'])->group(function(){
    Route::prefix('categories')->group(function(){
        Route::get('/',[CategoryController::class,'index']);
        Route::post('/',[CategoryController::class,'store']);
        Route::get('/{category}',[CategoryController::class,'show']);
        Route::put('/{category}',[CategoryController::class,'update']);
        Route::delete('/{category}',[CategoryController::class,'destroy']);
    });

    Route::prefix('products')->group(function(){
        Route::get('/',[ProductController::class,'index']);
        Route::post('/',[ProductController::class,'store']);
        Route::get('/{product}',[ProductController::class,'show']);
        Route::post('update/{product}',[ProductController::class,'update']);
        Route::post('/search',[ProductController::class,'search']);
        Route::post('/sort',[ProductController::class,'sort']);
        Route::delete('/{product}',[ProductController::class,'destroy']);
    });

    Route::prefix('users')->group(function(){
        Route::post('/logout',[UserController::class,'logout']);
        Route::get('myProducts/{user}',[UserController::class,'myProduct']);
        Route::get('/',[UserController::class , 'showProfile']);
        Route::post('Editprofile', [UserController::class , 'Editprofile']);
        Route::delete('/{user}', [UserController::class , 'deleteAccount']);
    });

    Route::prefix('comments')->group(function(){
        Route::get('/{product}',[CommentController::class,'index']);
        Route::post('/{product}',[CommentController::class,'store']);
        Route::put('/{comment}',[CommentController::class,'update']);
        Route::delete('/{comment}',[CommentController::class,'destroy']);
    });

    Route::prefix('likes')->group(function(){
        Route::get('/',[LikeController::class,'favorite']);
        Route::post('/{product}',[LikeController::class,'store']);
    });
});




Route::get('try',function(Request $request){
    //if($request->hasFile('image')){
    /*    $image = $request-> file('image');
        $destination_path='public/images/products';
        $image_name =$image->getClientOriginalExtension();
        $path = $request->file('image')->storeAs($destination_path , $image_name);
        $image_url = $image_name;
        return "hi";*/
    //}
    $image=time().$request->image->extension();
    //return $request->image->extension();
   // return $request->image;

});
Route::get('test/{product}',[ProductController::class,'test']);
