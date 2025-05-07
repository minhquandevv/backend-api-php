<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return response()->json([
            'code' => 200,
            'message' => 'Project list retrieved successfully',
            'data' => $projects,
        ], 200);
    }

    public function store(Request $request)
    {
        $project = new Project();
        $project->name = $request->name;
        $project->description = $request->description;
        $project->save();

        return response()->json([
            'code' => 201,
            'message' => 'Project created successfully',
            'data' => $project,
        ], 201);
    }

    public function show(string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'code' => 404,
                'message' => 'Project not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Project retrieved successfully',
            'data' => $project,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'code' => 404,
                'message' => 'Project not found',
                'data' => null,
            ], 404);
        }

        $project->name = $request->name;
        $project->description = $request->description;
        $project->save();

        return response()->json([
            'code' => 200,
            'message' => 'Project updated successfully',
            'data' => $project,
        ], 200);
    }

    public function destroy(string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'code' => 404,
                'message' => 'Project not found',
            ], 404);
        }

        $project->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Project deleted successfully',
        ], 200);
    }
}