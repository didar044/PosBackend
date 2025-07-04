<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase\Purchase;
use App\Models\Sale\Sale;
use App\Models\Customer\Customer;
use App\Models\Supplier\Supplier;

class DashboardController extends Controller
{
    
    public function index()
    {    
        $purchases=Purchase::all();
           $totalpurchase=0;
           foreach($purchases as $purchase){
            $totalpurchase+=$purchase->total_amount;
           }

           $totalpurchasedue=0;
           foreach($purchases as $purchase){
            $totalpurchasedue+=$purchase->total_amount-$purchase->paid_amount;
           }


        $sales=Sale::all();
        $totalsale=0;
        foreach($sales as $sale){
            $totalsale+=$sale->total_amount;
        }
        $totalsaledue=0;
        foreach($sales as $sale){
            $totalsaledue+=$sale->total_amount-$sale->paid_amount;
        }

        



        $data=[
                   "totalpurchase"=>$totalpurchase,
                   "totalpurchasedue"=>$totalpurchasedue,
                   "totalsale"=>$totalsale,
                   "totalsaledue"=>$totalsaledue,
                   "customers"=>Customer::count(),
                   "suppliers"=>Supplier::count(),
                   "purchaseinvoice"=>Purchase::where('status','completed')->count(),
                   "saleinvoice"=>Sale::where('status','completed')->count(),

        ];
        return response()->json($data);
    }

   
}
