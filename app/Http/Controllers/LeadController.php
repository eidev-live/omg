<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Support\Csv;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    private const WHATSAPP_NUMBER = '6285770722652';

    private const WHATSAPP_MESSAGE = 'saya tertarik untuk membeli telur di oh my egg';

    /**
     * Simpan data calon pembeli lalu arahkan ke WhatsApp.
     */
    public function store(StoreLeadRequest $request)
    {
        Lead::query()->create($request->safe()->only(['name', 'email', 'phone']));

        $url = 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode(self::WHATSAPP_MESSAGE);

        return Inertia::location($url);
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $leads = Lead::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Lead $lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'created_at' => $lead->created_at?->toIso8601String(),
            ]);

        return Inertia::render('leads/Index', [
            'leads' => $leads,
            'filters' => ['search' => $search],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $search = $request->string('search')->toString();

        $rows = Lead::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (Lead $lead) => [
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->created_at?->format('Y-m-d H:i'),
            ]);

        return Csv::stream('calon-pembeli.csv', ['Nama', 'Email', 'No. WhatsApp', 'Tanggal'], $rows);
    }
}
