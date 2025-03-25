<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Field;

class FieldList extends Component
{
    public $fields, $size, $location;

    public function render()
    {
        $this->fields = Field::all();
        return view('livewire.field-list')->extends('layouts.app');
    }

    public function store()
    {
        $this->validate([
            'size' => 'required|numeric',
            'location' => 'required',
        ]);
        Field::create([
            'Size' => $this->size,
            'Location' => $this->location,
        ]);
        session()->flash('message', 'Field added successfully.');
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->size = '';
        $this->location = '';
    }
}

