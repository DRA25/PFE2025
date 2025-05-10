<?php

namespace App\Http\Controllers;

use App\Models\AboutPageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AboutPageController extends Controller
{
    public function show()
    {
        $sections = AboutPageContent::orderBy('order')->get();
        return Inertia::render('About/Index', [
            'sections' => $sections
        ]);
    }

    public function edit()
    {
        $sections = AboutPageContent::orderBy('order')->get();
        return Inertia::render('About/Edit', [
            'sections' => $sections
        ]);
    }

    public function create()
    {
        return Inertia::render('About/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*.section_title' => 'required|string|max:255',
            'sections.*.section_content' => 'required|string',
            'sections.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sections.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['sections'] as $sectionData) {
                $section = new AboutPageContent();
                $section->section_title = $sectionData['section_title'];
                $section->section_content = $sectionData['section_content'];
                $section->order = $sectionData['order'];

                if (isset($sectionData['image'])) {
                    $path = $sectionData['image']->store('about-page', 'public');
                    $section->image_path = $path;
                }

                $section->save();
            }
        });

        return redirect()->route('about')->with('success', 'New sections created successfully');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:about_page_contents,id',
            'sections.*.section_title' => 'required|string',
            'sections.*.section_content' => 'required|string',
            'sections.*.image' => 'nullable', // <-- changed
        ]);

        foreach ($request->sections as $sectionData) {
            $section = AboutPageContent::find($sectionData['id']);

            $updateData = [
                'section_title' => $sectionData['section_title'],
                'section_content' => $sectionData['section_content'],
            ];

            if (isset($sectionData['image']) && $sectionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $sectionData['image']->store('about-page', 'public');
                $updateData['image_path'] = $path;
            }

            $section->update($updateData);
        }

        return redirect()->route('about')->with('success', 'About page updated successfully');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:about_page_contents,id'
        ]);

        $section = AboutPageContent::find($request->id);
        $section->delete();

        return redirect()->route('about.edit')->with('success', 'Section deleted successfully');
    }
}
