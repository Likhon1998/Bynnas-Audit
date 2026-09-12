<?php

return [
    /*
    | Auditors pick which checklist formats apply to a visit (any subset of Format 1–5).
    | The report unlocks when every selected format is saved as evidence.
    | At least this many headings must be selected before the gate can pass.
    */
    'min_selected_formats' => 1,
];
