<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        if ($buyer->id == $product->seller_id) {
            return $this->errorResponse('The buyer must be different from seller', 409);
        }

        if (! $buyer->isVerified()) {
            return $this->errorResponse('The buyer must be a verified user', 409);
        }
        
        if (! $product->seller->isVerified()) {
            return $this->errorResponse('The seller must be a verified user', 409);
        }

        if (! $product->isAvaliable()) {
            return $this->errorResponse('The seller is not available', 409);
        }

        if ($product->quantity < $request->quantity) {
            return $this->errorResponse('The product does not have enough units for this transaction', 409);
        }

        return DB::transaction(function () use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction);
        });
    }
}
