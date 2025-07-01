<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;

class CustomerController extends Controller
{
    
    public function index()
    {
        $customers=Customer::all();
        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
        {
            $customer = new Customer();

            $customer->name        = $request->name;
            $customer->email       = $request->email;
            $customer->phone       = $request->phone;
            $customer->address     = $request->address;
            $customer->description = $request->description;

            $customer->save();

            return response()->json([
                'message' => 'Customer created successfully.',
                'data' => $customer,
            ], 201);
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         $customer = Customer::findOrFail($id);
             return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer= Customer::findOrFail($id);
        $customer->name        = $request->name;
        $customer->email       = $request->email;
        $customer->phone       = $request->phone;
        $customer->address     = $request->address;
        $customer->description = $request->description;

        $customer->save();

        return response()->json([$customer ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer=Customer::find($id);
        $customer->delete();
        return response()->json($customer);
    }
}
