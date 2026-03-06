<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::all());
    }

    public function create()
    {
        $code = 'S' . str_pad(Supplier::count() + 1, 3, '0', STR_PAD_LEFT);
        return response()->json(['supplierCode' => $code]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'contact_email'  => 'required|email|unique:suppliers,contact_email',
            'contact_number' => 'required|string|max:11',
        ]);

        $code = 'S' . str_pad(Supplier::count() + 1, 3, '0', STR_PAD_LEFT);

        $supplier = Supplier::create([
            'supplier_code'  => $code,
            'supplier_name'  => $request->supplier_name,
            'contact_email'  => $request->contact_email,
            'contact_number' => $request->contact_number,
        ]);

        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with(['products' => function ($q) {
            $q->withPivot('quantity', 'delivery_reference');
        }])->findOrFail($id);

        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplier_name'  => 'sometimes|string|max:255',
            'contact_email'  => 'sometimes|email|unique:suppliers,contact_email,' . $id,
            'contact_number' => 'sometimes|string|max:11',
        ]);

        $supplier->update($request->only('supplier_name', 'contact_email', 'contact_number'));
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }
}