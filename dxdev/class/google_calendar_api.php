<?php

class GoogleCalendarApi
{

    public static function getPublicHolidays($start_date, $end_date)
    {

        //APIキー
        $api_key = 'AIzaSyC97E6R63D5WNzuLqvHRFlbs8JVyL-rzHA';

        //取得するカレンダー
        $calendar_id = 'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com';  // mozilla.org版

        $holidays_url = sprintf(
            'https://www.googleapis.com/calendar/v3/calendars/%s/events?' .
            'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
            $calendar_id, $api_key, "{$start_date}T00:00:00Z", // 取得開始日
            "{$end_date}T00:00:00Z", // 取得終了日
            31            // 最大取得数
        );

        $holidays = null;

        if($results = file_get_contents($holidays_url)){
            $results = json_decode($results);
            if(isset($results->items) && count($results->items) > 0){
                $holidays = array();
                foreach($results->items as $key => $item){
                    $date            = date('Y-m-d', strtotime((string)$item->start->date));
                    $holidays[$date] = (string)$item->summary;
                }
                ksort($holidays);
            }
        }

        return $holidays;

    }
}