<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleTypeRequest;
use App\Http\Requests\UpdateSaleTypeRequest;
use App\Models\SaleType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $saleTypes = SaleType::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SaleType $saleType) => [
                'id' => $saleType->id,
                'name' => $saleType->name,
                'egg_quantity' => $saleType->egg_quantity,
                'selling_price' => $saleType->selling_price,
                'is_active' => $saleType->is_active,
            ]);

        return Inertia::render('saleTypes/Index', [
            'saleTypes' => $saleTypes,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('saleTypes/Create');
    }

    public function store(StoreSaleTypeRequest $request): RedirectResponse
    {
        SaleType::query()->create($this->payload($request));

        return to_route('sale-types.index')->with('success', 'Tipe penjualan berhasil ditambahkan.');
    }

    public function edit(SaleType $saleType): Response
    {
        return Inertia::render('saleTypes/Edit', [
            'saleType' => [
                'id' => $saleType->id,
                'name' => $saleType->name,
                'egg_quantity' => $saleType->egg_quantity,
                'selling_price' => $saleType->selling_price,
                'is_active' => $saleType->is_active,
            ],
        ]);
    }

    public function update(UpdateSaleTypeRequest $request, SaleType $saleType): RedirectResponse
    {
        $saleType->update($this->payload($request));

        return to_route('sale-types.index')->with('success', 'Tipe penjualan berhasil diperbarui.');
    }

    public function toggle(SaleType $saleType): RedirectResponse
    {
        $saleType->update(['is_active' => ! $saleType->is_active]);

        return back()->with('success', $saleType->is_active ? 'Tipe penjualan diaktifkan.' : 'Tipe penjualan dinonaktifkan.');
    }

    public function destroy(SaleType $saleType): RedirectResponse
    {
        if ($saleType->saleItems()->exists()) {
            return back()->with('error', 'Tipe penjualan sudah dipakai pada transaksi. Nonaktifkan saja agar histori tetap aman.');
        }

        $saleType->delete();

        return to_route('sale-types.index')->with('success', 'Tipe penjualan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        return [
            'name' => $request->string('name')->toString(),
            'egg_quantity' => $request->integer('egg_quantity'),
            'selling_price' => $request->integer('selling_price'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
