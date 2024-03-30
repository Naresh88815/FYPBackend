<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            // Retrieve all categories
            $categories = Category::all();

            return view('admin.category.index', compact('categories'));
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to fetch categories'
            ], 500);
        }
    }
    public function activeCat()
    {
        try {
            // Retrieve all categories
            $categories = Category::where('status', 1)->get();

            return response()->json([
                'status' => 200,
                'categories' => $categories
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to fetch categories'
            ], 500);
        }
    }
    public function create()
    {
        try {
            return view('admin.category.create');
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to fetch categories'
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'image' => 'nullable',
                'status' => 'boolean',
            ]);

            $imagePath = null;
            //image uplaoad 
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/category/'), $image_name);
                $image_path = 'images/category/' . $image_name;
            } else {
                $image_path = 'images/category/default.png';
            }

            // Create the category

            $category = new Category();
            $category->name = $request->input('name');
            $category->description = $request->input('description');
            $category->image = $image_path;
            $category->status = $request->input('status', 1);
            $category->save();

            return response()->json([
                'status' => 200,
                'category' => $category
            ], 201);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to create category' . $e
            ], 500);
        }
    }
    public function edit($id)
    {
        try {
            // Find the category by ID
            $category = Category::findOrFail($id);

            return response()->json([
                'status' => 200,
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 404,
                'error' => 'Category not found'
            ], 404);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Find the category by ID
            $category = Category::findOrFail($id);

            // Validate the request data
            $request->validate([
                'name' => 'string',
                'description' => 'nullable|string',
                'image' => 'nullable|image',
                'status' => 'boolean',
            ]);

            // Upload and store the category image if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/category/'), $image_name);
                $image_path = 'images/category/' . $image_name;
                $category->image = $image_path;
            }
            // Update the category fields
            $category->name = $request->name;
            $category->description = $request->description;
            $category->status = $request->status;
            $category->update();

            return response()->json([
                'status' => 200,
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to update category'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Find the category by ID
            $category = Category::findOrFail($id);

            // Delete the category
            $category->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Category deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'error' => 'Failed to delete category'
            ], 500);
        }
    }
}