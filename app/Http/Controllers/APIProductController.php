<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class APIProductController extends Controller
{
    public function listProduct()
    {
        
        $listProduct = Product::whereHas('product_detail', function ($query) {

            $query->where('quantity', '>', 0);
        })
        ->with([
            'brand',
            'img_product',
            'product_series',
            'rate',
            'product_detail.color',
            'product_detail.capacity',
            'product_detail.discount_detail'=>function($query){
                $query->isActive();
            }
        ])
        ->get();
        
        
        return response()->json([
            'success' =>true,
            'data'=>$listProduct
        ]);
    }
    public function getProductDetail($id)
    {
        try{
            $proDuct = Product::whereHas('product_detail', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->with([
                'product_description.front_camera',
                'product_description.rear_camera',
                'product_description.screen',
                'rate',
                'comment.customer' => function($query) {
                    $query->select('id', 'name');
                },
                'comment.comment_detail.admin' => function($query) {
                    $query->select('id', 'name');
                },
                'brand',
                'product_series',
                'img_product', 
                'product_detail.color', 
                'product_detail.capacity',
                'product_detail.discount_detail' => function($query) {
                    $query->isActive()->with(['discount' => function($query) {
                        $query->select('id', 'date_start', 'date_end'); // Chọn cột 'date_start' và 'date_end' từ bảng 'discount'
                    }]);
                }
            ])
            ->findOrFail($id);
        if(empty($proDuct))
        {
            return response()->json([
                'success' =>false,
                'message'=>"Sản phẩm ID {$id} không tồn tại"
            ]);
        }
        return response()->json([
            'success' =>true,
            'data'=>$proDuct
        ]);
        }catch(ModelNotFoundException $e){
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm ID {$id} không tồn tại"
            ]);
        }
    }
    
    public function timKiem(Request $request )
    {
        $proDuct=Product::with(['brand','product_series'])->where('name',$request->name)->first();
        if(empty($proDuct))
        {
            return response()->json([
                'success'=>false,
                'message'=>"Sản phẩm tên {$request->name} không tồn tại"
            ]); 
        }
        return response()->json([
            'success'=>true,
            'data'=>$proDuct
        ]);
    } 
    public function timKiemTen($searchTerm)
    {
        $name=$searchTerm;
        $proDuct = Product::with([
            'brand',
            'product_series',
            'img',
            'product_detail' => function ($query) {
                $query->with('color', 'capacity');
            }
        ])->where('name', 'like', '%'. $name . '%')->get();
        if($proDuct->isEmpty())
        {
            return response()->json([
                'success'=>false,
                'message'=>"Sản phẩm không tồn tại"
            ]); 
        }
        return response()->json([
            'success'=>true,
            'data'=>$proDuct
        ]);
    }
}
