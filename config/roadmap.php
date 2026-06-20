<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Item Kinds Registry
    |--------------------------------------------------------------------------
    | Add a new kind here to make it valid everywhere (import validation,
    | roadmap viewer sections, checkin type mapping, schema help text).
    |
    | color      — Tailwind color token name from your design system
    | checkin_type — 'study' | 'project' | 'community' | 'task'
    | section    — heading shown in the block-card accordion
    |--------------------------------------------------------------------------
    */

    'item_kinds' => [
        'project'   => ['label' => 'Project',   'color' => 'accent',    'checkin_type' => 'project',   'section' => 'Projects & Builds'],
        'problem'   => ['label' => 'Problem',   'color' => 'warn',      'checkin_type' => 'study',     'section' => 'Practice Problems'],
        'community' => ['label' => 'Community', 'color' => 'community', 'checkin_type' => 'community', 'section' => 'Community & Presence'],
        'drill'     => ['label' => 'Drill',     'color' => 'ok',        'checkin_type' => 'study',     'section' => 'Drills & Exercises'],
        'habit'     => ['label' => 'Habit',     'color' => 'accent',    'checkin_type' => 'study',     'section' => 'Daily Habits'],
        'practice'  => ['label' => 'Practice',  'color' => 'ok',        'checkin_type' => 'study',     'section' => 'Practice Sessions'],
        'milestone' => ['label' => 'Milestone', 'color' => 'warn',      'checkin_type' => 'community', 'section' => 'Milestones'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Color → Tailwind class map
    |--------------------------------------------------------------------------
    | Maps color token names above to actual Tailwind classes used in views.
    |--------------------------------------------------------------------------
    */

    'kind_colors' => [
        'accent'    => ['bg' => 'bg-accent/20',    'text' => 'text-accent'],
        'warn'      => ['bg' => 'bg-warn/20',      'text' => 'text-warn'],
        'ok'        => ['bg' => 'bg-ok/20',        'text' => 'text-ok'],
        'community' => ['bg' => 'bg-community/20', 'text' => 'text-community'],
        'danger'    => ['bg' => 'bg-danger/20',    'text' => 'text-danger'],
    ],

];
