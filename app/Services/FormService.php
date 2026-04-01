<?php

namespace App\Services;

use App\Models\Form;

class FormService
{

    public function getAllForms()
    {
        return Form::with(['lastEntryHeader','lastEntryHeader.approver'])->get();
    }

    public function getForm(string $formCode)
    {
       return Form::where('form_code', $formCode)->first();
    }

    public function getFormData(string $formCode)
    {
        $form = Form::with(['categories.questions'])->where('form_code', $formCode)->first();

        $sections = $form->categories->map(function ($cat) {
            return [
                'category_id' => $cat->id,
                'title'       => $cat->name,
                'questions'   => $cat->questions->map(fn($q) => [
                    'question_id' => $q->id,
                    'text'        => $q->name,
                    'order'       => $q->order,
                ])->toArray(),
            ];
        })->toArray();

        return [
            'id' => $form->id,
            'form_code' => $form->form_code,
            'name'      => $form->name,
            'sections'  => $sections,
        ];
    }


}
