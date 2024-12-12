<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function task_kanban_board()
    {
        return view('pages.task.task-kanban-board');
    }

    public function task_listview()
    {
        return view('pages.task.task-listview');
    }

    public function task_details()
    {
        return view('pages.task.task-details');
    }
    
}