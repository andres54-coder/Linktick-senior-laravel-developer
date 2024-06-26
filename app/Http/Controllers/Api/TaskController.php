<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {

        $query = Task::query();


        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->input('due_date'));
        }



        $tasks = $query->with('user')->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new HttpResponseException(response()->json(['errors' => $errors], 422));
        }
        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        $task = Task::create(
            $data
        );

        return response()->json($task, 201); // Use 201 for created resources

    }

    public function show($id)
    {
        $task = Task::findOrFail($id);

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new HttpResponseException(response()->json(['errors' => $errors], 422));
        }

        $task = Task::findOrFail($id);
        $task->update($validator->validated());

        return response()->json($task);
    }

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();

        return response()->noContent();
    }
}
