<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Forum participation anti-fraud rules
    |--------------------------------------------------------------------------
    | Must match discussion posting rules so students cannot spam short replies
    | to farm daily challenge points.
    */
    'forum' => [
        'min_reply_characters' => 40,
        'min_discussion_characters' => 40,
        'min_title_characters' => 10,
        'max_qualifying_replies_per_discussion' => 1,
    ],

    /** Days after challenge date when completion can still be claimed. */
    'completion_window_days' => 7,
];
