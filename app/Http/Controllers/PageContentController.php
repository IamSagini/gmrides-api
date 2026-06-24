<?php
namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    public function show($slug) {
        $page = PageContent::where('slug', $slug)->first();
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }
        return response()->json(['success' => true, 'page' => $page]);
    }

    public function index() {
        return response()->json(['success' => true, 'pages' => PageContent::orderBy('slug')->get()]);
    }

    public function update(Request $request, $slug) {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sections' => 'required|array',
            'sections.*.h' => 'required|string',
            'sections.*.p' => 'required|array',
            'sections.*.p.*' => 'string',
        ]);
        $page = PageContent::where('slug', $slug)->firstOrFail();
        $page->update(['title' => $data['title'], 'sections' => $data['sections']]);
        return response()->json(['success' => true, 'page' => $page]);
    }
}
