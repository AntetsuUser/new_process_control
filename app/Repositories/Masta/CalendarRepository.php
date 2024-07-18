<?php

namespace App\Repositories\Masta;

use App\Models\Calendar;

class CalendarRepository
{
    public function calendar_get()
    {
        //calendarDBからすべてのデータを取得する
        return Calendar::all()->pluck('day');
    }
    public function insertOrdelete($data)
    {
        foreach ($data as $value) 
        {
           // データベースから日付を取得するクエリを実行
            $existingRecord = Calendar::where('day', $value)->first();
            if ($existingRecord) {
                // レコードが見つかった場合は削除
                $existingRecord->delete();
            } else {
                // レコードが見つからなかった場合は登録
                Calendar::create([
                    'day' => $value
                ]);
            }
        }
    }
}