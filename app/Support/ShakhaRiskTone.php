<?php

namespace App\Support;

/**
 * Canonical shakha risk colours for badges, chips, map markers, and text.
 */
class ShakhaRiskTone
{
    public const SIGNIFICANT = 'Significant Risk';

    public const HIGH = 'High Risk';

    public const MEDIUM = 'Medium Risk';

    public const LOW = 'Low Risk';

    public const UNASSESSED = 'Not assessed';

    /**
     * @return 'significant'|'high'|'medium'|'low'|'unassessed'
     */
    public static function key(?string $category): string
    {
        $normalized = self::normalize($category);

        return match ($normalized) {
            self::SIGNIFICANT => 'significant',
            self::HIGH => 'high',
            self::MEDIUM => 'medium',
            self::LOW => 'low',
            default => 'unassessed',
        };
    }

    public static function label(?string $category): string
    {
        $normalized = self::normalize($category);

        return $normalized ?: self::UNASSESSED;
    }

    public static function shortLabel(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => 'Significant',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => 'N/A',
        };
    }

    /** Light UI pill (tables, lists, pickers). */
    public static function badgeClasses(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => 'bg-rose-50 text-rose-800 ring-1 ring-rose-200',
            'high' => 'bg-orange-50 text-orange-800 ring-1 ring-orange-200',
            'medium' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
            'low' => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
            default => 'bg-slate-50 text-slate-500 ring-1 ring-slate-200',
        };
    }

    /** Dark / drawer chips. */
    public static function chipClasses(?string $category): string
    {
        return match (self::key($category)) {
            'significant', 'high' => 'bg-rose-500/15 text-rose-200 ring-1 ring-rose-400/30',
            'medium' => 'bg-amber-500/15 text-amber-200 ring-1 ring-amber-400/30',
            'low' => 'bg-emerald-500/15 text-emerald-200 ring-1 ring-emerald-400/30',
            default => 'bg-white/5 text-slate-400 ring-1 ring-white/10',
        };
    }

    /** Soft row / card background tint. */
    public static function softBgClasses(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => 'bg-rose-50/70',
            'high' => 'bg-orange-50/70',
            'medium' => 'bg-amber-50/60',
            'low' => 'bg-emerald-50/50',
            default => '',
        };
    }

    /** Name text colour. */
    public static function textClasses(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => 'text-rose-800',
            'high' => 'text-orange-800',
            'medium' => 'text-amber-900',
            'low' => 'text-emerald-800',
            default => 'text-navy-900',
        };
    }

    public static function hex(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => '#e11d48',
            'high' => '#ea580c',
            'medium' => '#d97706',
            'low' => '#059669',
            default => '#64748b',
        };
    }

    public static function dashboardTone(?string $category): string
    {
        return match (self::key($category)) {
            'significant' => 'rose',
            'high' => 'orange',
            'medium' => 'amber',
            'low' => 'emerald',
            default => 'slate',
        };
    }

    /**
     * JS-friendly map for Alpine UIs.
     *
     * @return array<string, array{key:string,label:string,short:string,badge:string,text:string,soft:string,hex:string}>
     */
    public static function jsMap(): array
    {
        $out = [];
        foreach ([self::SIGNIFICANT, self::HIGH, self::MEDIUM, self::LOW, self::UNASSESSED, ''] as $cat) {
            $label = self::label($cat);
            $out[$label] = [
                'key' => self::key($cat),
                'label' => $label,
                'short' => self::shortLabel($cat),
                'badge' => self::badgeClasses($cat),
                'text' => self::textClasses($cat),
                'soft' => self::softBgClasses($cat),
                'hex' => self::hex($cat),
            ];
        }

        return $out;
    }

    public static function normalize(?string $category): string
    {
        $raw = trim((string) $category);
        if ($raw === '' || strcasecmp($raw, 'Not assessed') === 0 || strcasecmp($raw, 'Unassessed') === 0) {
            return '';
        }

        $lower = mb_strtolower($raw);

        return match (true) {
            str_contains($lower, 'significant') => self::SIGNIFICANT,
            str_contains($lower, 'high') => self::HIGH,
            str_contains($lower, 'medium') => self::MEDIUM,
            str_contains($lower, 'low') => self::LOW,
            default => $raw,
        };
    }
}
