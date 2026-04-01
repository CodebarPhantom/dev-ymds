<?php

namespace App\Services;

use App\Models\EntryHeader;
use Illuminate\Support\Facades\Auth;

class EntryHeaderService
{
    public function createEntryHeader(array $data)
    {
        return EntryHeader::create($data);
    }

    public function getLastestEntry()
    {
        return EntryHeader::with(['surah', 'approver', 'details'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(10) // Gunakan take() bukan limit() di Eloquent
            ->get();
    }

    public function getEntryAndDetailWithAnswer(int $entryId)
    {
        $entryHeader = EntryHeader::where('id', $entryId)
            ->with(['surah', 'approver', 'form.categories.questions', 'details', 'user'])
            ->first();

        $sections = $entryHeader->form->categories->map(function ($category) use ($entryHeader) {
            return [
                'category_id' => $category->id,
                'title'       => $category->name,
                'questions'   => $category->questions->map(function ($question) use ($entryHeader) {
                    $detail = $entryHeader->details->firstWhere('question_id', $question->id);
                    return [
                        'question_id' => $question->id,
                        'text'        => $question->name,
                        'order'       => $question->order,
                        'answer'      => $detail ? $detail->string_value : null
                    ];
                })->toArray()
            ];
        })->toArray();

        return [
            'header' => [
                'id' => $entryHeader->id,
                'user_id' => $entryHeader->user_id,
                'page' => $entryHeader->page,
                'verse_start'=> $entryHeader->verse_start,
                'verse_end'=> $entryHeader->verse_end,
                'notes'=>$entryHeader->notes,
                'metadata'=>$entryHeader->metadata,
                'user'=> [
                    'id' => $entryHeader->user->id,
                    'name' => $entryHeader->user->name
                ],
                'surah' => [
                    'id' => $entryHeader->surah->id,
                    'name' => $entryHeader->surah->name,
                    'name_latin' => $entryHeader->surah->name_latin
                ],
                'approver' => $entryHeader->approver ? [
                    'id' => $entryHeader->approver->id,
                    'name' => $entryHeader->approver->name
                ] : null,
                'form' => [
                    'id' => $entryHeader->form->id,
                    'form_code' => $entryHeader->form->form_code,
                    'name' => $entryHeader->form->name
                ],
                'formatted_entry_date' => $entryHeader->formatted_entry_date,
                'created_at' => $entryHeader->created_at,
                'updated_at' => $entryHeader->updated_at
            ],
            'sections' => $sections
        ];
    }
}
