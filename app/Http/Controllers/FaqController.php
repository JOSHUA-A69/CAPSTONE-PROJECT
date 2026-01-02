<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    /**
     * Get all active FAQs for the chat interface.
     */
    public function index()
    {
        $faqs = Faq::active()
            ->ordered()
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'title' => $faq->title,
                    'question' => $faq->question,
                    'response' => $faq->response,
                ];
            });

        return response()->json([
            'success' => true,
            'faqs' => $faqs,
        ]);
    }

    /**
     * Store a new FAQ (admin only).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user is admin
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'question' => 'required|string|max:500',
            'response' => 'required|string|max:2000',
        ]);

        $faq = Faq::create([
            'title' => $validated['title'] ?? null,
            'question' => $validated['question'],
            'response' => $validated['response'],
            'admin_id' => $user->id,
            'is_active' => true,
            'sort_order' => Faq::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'faq' => [
                'id' => $faq->id,
                'title' => $faq->title,
                'question' => $faq->question,
                'response' => $faq->response,
            ],
        ]);
    }

    /**
     * Update an existing FAQ (admin only).
     */
    public function update(Request $request, Faq $faq)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'question' => 'required|string|max:500',
            'response' => 'required|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully.',
            'faq' => [
                'id' => $faq->id,
                'title' => $faq->title,
                'question' => $faq->question,
                'response' => $faq->response,
            ],
        ]);
    }

    /**
     * Delete an FAQ (admin only).
     */
    public function destroy(Faq $faq)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully.',
        ]);
    }

    /**
     * Reorder FAQs (admin only).
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:faqs,id',
        ]);

        foreach ($validated['order'] as $index => $faqId) {
            Faq::where('id', $faqId)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'FAQ order updated successfully.',
        ]);
    }
}
