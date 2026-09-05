<?php

namespace App\Support;

/**
 * Catalog of auditor checklist formats (Format 1…N).
 * Searchable by heading; each format maps to an editable template.
 */
class AuditChecklistCatalog
{
    public const LAYOUT_SOCIETY_LIFECYCLE = 'society_lifecycle';

    public const LAYOUT_MEMBER_ADMISSION = 'member_admission';

    public const LAYOUT_SOCIETY_MANAGEMENT = 'society_management';

    public const LAYOUT_SAVINGS_LOAN_COLLECTION = 'savings_loan_collection';

    public const LAYOUT_SAVINGS_REFUND = 'savings_refund';

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::format1(),
            self::format2(),
            self::format3(),
            self::format4(),
            self::format5(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByCode(string $code): ?array
    {
        foreach (self::all() as $format) {
            if (($format['code'] ?? '') === $code) {
                return $format;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByNumber(int $number): ?array
    {
        foreach (self::all() as $format) {
            if ((int) ($format['number'] ?? 0) === $number) {
                return $format;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function format1(): array
    {
        return [
            'number' => 1,
            'code' => 'format-1',
            'layout' => self::LAYOUT_SOCIETY_LIFECYCLE,
            'heading' => 'সমিতি গঠন, বন্ধ /একত্রিকরণ ও স্থানান্তরের চেক লিস্ট।',
            'org_name' => 'ডিএসকে',
            'dept_name' => 'অভ্যন্তরীণ নিরীক্ষা ও পরিদর্শন বিভাগ',
            'sections' => [
                'formation' => [
                    'key' => 'formation',
                    'label' => 'সমিতি গঠন',
                    'check_count' => 5,
                    'default_rows' => 2,
                    'questions' => [
                        'সমিতি গঠন / খোলার জন্য সার্ভে ডকুমেন্টস আছে কি?',
                        'নতুন সমিতি গঠন / খোলার জন্য আঞ্চলিক ব্যবস্থাপকের অনুমোদন আছে কি?',
                        'কম্পিউটার সফটওয়্যারে সঠিকভাবে সমিতি সেটআপ করা আছে কি?',
                        'নতুন সমিতি ন্যূনতম ১০ জন সদস্য নিয়ে পরিচালিত হচ্ছে কি?',
                        'পুরাতন সমিতিতে ন্যূনতম ১০ এবং সর্বোচ্চ ৪০ জন সদস্য / ঋণগ্রহীতা আছে কি?',
                    ],
                ],
                'closure' => [
                    'key' => 'closure',
                    'label' => 'সমিতি বন্ধ /একত্রিকরণ',
                    'check_count' => 3,
                    'default_rows' => 2,
                    'questions' => [
                        'সমিতি বন্ধ / একত্রিকরণের জন্য যথাযথ কর্তৃপক্ষের লিখিত অনুমোদন আছে কি?',
                        'সদস্য, ঋণগ্রহীতা, ঋণ, সঞ্চয় ও অন্যান্য প্রয়োজনীয় তথ্যাদি সফটওয়্যারে সঠিকভাবে স্থানান্তর / গ্রহণ করা হয়েছে কি?',
                    ],
                ],
                'transfer' => [
                    'key' => 'transfer',
                    'label' => 'সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)',
                    'check_count' => 4,
                    'default_rows' => 2,
                    'questions' => [
                        'সমিতি স্থানান্তরের জন্য যথাযথ কর্তৃপক্ষের লিখিত অনুমোদন আছে কি?',
                        'সদস্য, ঋণগ্রহীতা, ঋণ, সঞ্চয় ও অন্যান্য প্রয়োজনীয় তথ্যাদি সফটওয়্যারে সঠিকভাবে স্থানান্তর / গ্রহণ করা হয়েছে কি?',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function format2(): array
    {
        return [
            'number' => 2,
            'code' => 'format-2',
            'layout' => self::LAYOUT_MEMBER_ADMISSION,
            'heading' => 'সদস্য নির্বাচন ও ভর্তি সংক্রান্ত চেকলিস্ট',
            'org_name' => 'ডিএসকে',
            'dept_name' => 'অভ্যন্তরীণ নিরীক্ষা ও পরিবীক্ষণ বিভাগ',
            'check_count' => 11,
            'default_rows' => 8,
            'questions' => [
                'ভর্তি ফরম NID / স্মার্ট কার্ড অনুযায়ী সঠিকভাবে পূরণ করা আছে কি?',
                'আবেদনকারী, অভিভাবক ও সমিতি প্রধানের স্বাক্ষর আছে কি?',
                'ভর্তি ফরমে ম্যানেজারের অনুমোদন নেওয়া আছে কি?',
                'কর্মকর্তা কর্তৃক সত্যায়িত যৌথ ছবি সংযুক্ত আছে কি?',
                'NID এর ফটোকপি সংগ্রহ করা আছে কি?',
                'সফটওয়্যারে NID নম্বর সঠিকভাবে এন্ট্রি করা আছে কি?',
                'পূর্বে খেলাপী ছিল কি না ম্যানেজার কর্তৃক যাচাই করা হয়েছে কি?',
                'ফরম অনুযায়ী সদস্যের নাম ও নম্বর সফটওয়্যারে এন্ট্রি করা আছে কি?',
                'আবেদন ফরমের সকল তথ্য সফটওয়্যারে পোস্ট / এন্ট্রি করা আছে কি?',
                'সদস্যের বয়স ১৮–৬৪ বছরের মধ্যে আছে কি?',
                'কর্ম এলাকায় ভাড়াটিয়া হলে কমপক্ষে ৫ বছর বসবাস করছে কি?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function format3(): array
    {
        return [
            'number' => 3,
            'code' => 'format-3',
            'layout' => self::LAYOUT_SOCIETY_MANAGEMENT,
            'heading' => 'সমিতি পরিচালনা সংক্রান্ত চেকলিস্ট',
            'org_name' => 'ডিএসকে',
            'dept_name' => 'অভ্যন্তরীণ নিরীক্ষা ও পরিবীক্ষণ বিভাগ',
            'default_stats_rows' => 5,
            'questions' => [
                'সমিতিটি বসার স্পট/স্থান আছে কিনা?',
                'সমিতির মিটিং স্থান উপযুক্ত ও নিরাপদ কিনা?',
                'সমিতি মিটিংয়ের জন্য পর্যাপ্ত জায়গা আছে কিনা?',
                'মাঠকর্মী সঠিক সময়ে সমিতিতে পৌঁছায় কিনা?',
                'সমিতি মিটিং নির্ধারিত সময়কাল অনুযায়ী অনুষ্ঠিত হয় কিনা?',
                'নিয়মিতভাবে সমিতি মিটিং হয় কিনা?',
                'মিটিংয়ে এজেন্ডা অনুসরণ করা হয় কিনা?',
                'সদস্য সংখ্যা ন্যূনতম নিয়ম অনুযায়ী আছে কিনা?',
                'মিটিংয়ে প্রয়োজনীয় সংখ্যক সদস্য উপস্থিত থাকে কিনা?',
                'সমিতি থেকে সদস্য ঝরে পড়া / বাদ পড়ার বিষয় পর্যবেক্ষণ করা হয় কিনা?',
                'নিয়মানুযায়ী মাঠে ২০০০ টাকা পর্যন্ত সঞ্চয় ফেরত দেওয়া হয় কিনা?',
                'মাঠকর্মী একই সমিতিতে নির্ধারিত মেয়াদ ধরে কাজ করে কিনা?',
                'সমিতিতে নির্বাচিত নেতৃত্ব / প্রধান আছে কিনা?',
                'সমিতি প্রধান মিটিংয়ে উপস্থিত থাকেন কিনা?',
                'একই ব্যক্তি একাধিক সমিতির প্রধান কিনা?',
                'সমিতি পরিচালনায় নেতৃত্ব সক্রিয় কিনা?',
                'রেজুলেশন বইয়ে প্রয়োজনীয় স্বাক্ষর আছে কিনা?',
                'সদস্যদের পাসবুক নিয়মিত হালনাগাদ করা হয় কিনা?',
                'ঋণ প্রস্তাব মিটিংয়ে আলোচনা / অনুমোদন হয় কিনা?',
                'সমিতিতে কোন বকেয়া সদস্য আছে কিনা, থাকলে মাঠকর্মকর্তা সকল বকেয়া সদস্য চিনেন কিনা?',
                'দীর্ঘমেয়াদী / স্থায়ী সঞ্চয় সংক্রান্ত তথ্য হালনাগাদ আছে কিনা?',
                'সঞ্চয় আদায় নিয়মিত হয় কিনা?',
                'নিষ্ক্রিয় সদস্যদের সঞ্চয় সমন্বয় করা হয় কিনা?',
                'সদস্যদের মধ্যে অননুমোদিত ঋণ হস্তান্তর / সমন্বয় আছে কিনা?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function format4(): array
    {
        return [
            'number' => 4,
            'code' => 'format-4',
            'layout' => self::LAYOUT_SAVINGS_LOAN_COLLECTION,
            'heading' => 'সঞ্চয় ও ঋণ আদায় চেকলিস্ট',
            'org_name' => 'ডিএসকে',
            'dept_name' => 'অভ্যন্তরীণ নিরীক্ষা ও পরিবীক্ষণ বিভাগ',
            'check_count' => 12,
            'default_rows' => 3,
            'questions' => [
                'সদস্যের পাসবুক ও সফটওয়্যার লেজারে সঞ্চয় ও ঋণ স্থিতি সামঞ্জস্যপূর্ণ কিনা?',
                'আদায়কৃত সঞ্চয় ও কিস্তি পাসবুকে সঠিকভাবে পোস্ট করা হয়েছে কিনা?',
                'মাঠকর্মী সঞ্চয় ও ঋণ কিস্তি সঠিকভাবে আদায় করে সময়মতো অফিসে জমা দেন কিনা?',
                'সঞ্চয়ের সুদ সঠিক সময়ে ও সঠিক পরিমাণে পাসবুকে পোস্ট করা হয়েছে কিনা?',
                'পাসবুকের এক পৃষ্ঠা থেকে অন্য পৃষ্ঠায় সঞ্চয় ও ঋণ স্থিতি সঠিকভাবে স্থানান্তর করা হয়েছে কিনা?',
                'পাসবুক পরিবর্তনের সময় পুরাতন থেকে নতুন পাসবুকে স্থিতি সঠিকভাবে স্থানান্তর ও শাখা ব্যবস্থাপক / সহকারী ব্যবস্থাপকের স্বাক্ষর নিশ্চিত করা হয়েছে কিনা?',
                'পাসবুকে কোনো ওভাররাইটিং / কাটাকাটি থাকলে পাশে ম্যানেজারের স্বাক্ষর আছে কিনা?',
                'সদস্যের পাসবুকে নির্ধারিত মাঠকর্মীর স্বাক্ষর আছে কিনা?',
                'প্রতি তিন মাস অন্তর শাখা ব্যবস্থাপনা কর্তৃক প্রতিটি সমিতির সকল সদস্যের পাসবুক পরীক্ষা করা হয়েছে কিনা?',
                'সঞ্চয় ফেরত ও সমন্বয় সদস্যের পাসবুকে সঠিকভাবে পোস্ট করা হয়েছে কিনা?',
                'নীতিমালা অনুযায়ী নিয়মিত দীর্ঘমেয়াদী সঞ্চয় আদায় হয় এবং অনিয়মিত পরিশোধে জরিমানা আদায় হয় কিনা?',
                'দীর্ঘমেয়াদী সঞ্চয় ৩ মাসের বেশি বকেয়া থাকলে একাউন্ট স্বয়ংক্রিয়ভাবে বন্ধ হয়ে দাবীযোগ্য সঞ্চয়ে স্থানান্তর হয় কিনা?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function format5(): array
    {
        return [
            'number' => 5,
            'code' => 'format-5',
            'layout' => self::LAYOUT_SAVINGS_REFUND,
            'heading' => 'সঞ্চয় ফেরত চেকলিস্ট',
            'org_name' => 'ডিএসকে',
            'dept_name' => 'অভ্যন্তরীণ নিরীক্ষা ও পরিবীক্ষণ বিভাগ',
            'check_count' => 17,
            'default_rows' => 2,
            'questions' => [
                'সঞ্চয় ফেরতের পূর্বে রসিদ ব্যবহার করা হয় কিনা?',
                'সঞ্চয় ফেরতের পূর্বে যথাযথ কর্তৃপক্ষের অনুমোদন নেওয়া হয় কিনা?',
                'সঞ্চয় ফেরত সদস্যের পাসবুকে সঠিকভাবে পোস্ট করা হয় কিনা?',
                'সঞ্চয় ফেরত রেজিস্টারে সঠিকভাবে লিপিবদ্ধ হয় এবং রেজিস্টার অনুযায়ী রিপোর্ট তৈরি হয় কিনা?',
                'সঞ্চয় ফেরত দিয়ে নগদে কিস্তি আদায় করা হয় কিনা?',
                'ফেরতকৃত সঞ্চয় সদস্যভিত্তিক সফটওয়্যারে সঠিকভাবে পোস্ট করা হয় কিনা?',
                'প্রতিষ্ঠানের নিয়ম অনুযায়ী মাঠ থেকে ২,০০০ টাকা পর্যন্ত স্বেচ্ছায় সঞ্চয় ফেরত দেওয়া হয় কিনা?',
                'মাঠে স্বেচ্ছায় সঞ্চয় ফেরতের জন্য ২ জন সদস্যের সাক্ষী স্বাক্ষর ও সদস্যের মোবাইল নম্বর সংগ্রহ করা হয় কিনা?',
                'সঞ্চয় ফেরত রেজিস্টারের স্বাক্ষর কলামে সদস্যের স্বাক্ষর নেওয়া হয় কিনা?',
                'সঞ্চয় ফেরত রেজিস্টারে সদস্যের স্বাক্ষর নিশ্চিত করা হয় কিনা?',
                'সঞ্চয় ফেরত রেজিস্টারে স্বাক্ষর ব্যবস্থাপনার উপস্থিতিতে করা হয় কিনা?',
                'ঋণ সম্পূর্ণ পরিশোধের পর (অনুরোধ থাকলে) কোনো সদস্যকে পূর্ণ সঞ্চয় ফেরত দেওয়া হয় কিনা?',
                'বকেয়া সদস্যদের সঞ্চয় ফেরত দেওয়া হয় কিনা?',
                'ঋণ চলাকালীন সদস্যের প্রয়োজনে সঞ্চয় ফেরত দেওয়া হয় কিনা?',
                'সঞ্চয় ফেরত সমিতির রেজুলেশনে লিপিবদ্ধ করা হয় কিনা?',
                'ম্যানেজার / সহকারী ম্যানেজার দিনশেষে নিয়মিত সঞ্চয় ফেরত রেজিস্টারে স্বাক্ষর করেন কিনা?',
                'মাসিক রিপোর্টের পণ্যভিত্তিক মোট সঞ্চয় ফেরত ও সঞ্চয় ফেরত রেজিস্টারের মধ্যে সমন্বয় আছে কিনা?',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    public static function blankPayload(array $definition): array
    {
        $layout = $definition['layout'] ?? self::LAYOUT_SOCIETY_LIFECYCLE;

        if ($layout === self::LAYOUT_MEMBER_ADMISSION) {
            $checks = (int) ($definition['check_count'] ?? 11);
            $count = (int) ($definition['default_rows'] ?? 8);
            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = self::blankMemberRow($checks);
            }

            return ['rows' => $rows];
        }

        if ($layout === self::LAYOUT_SAVINGS_LOAN_COLLECTION) {
            $checks = (int) ($definition['check_count'] ?? 12);
            $count = (int) ($definition['default_rows'] ?? 3);
            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = self::blankSavingsLoanRow($checks);
            }

            return ['rows' => $rows];
        }

        if ($layout === self::LAYOUT_SAVINGS_REFUND) {
            $checks = (int) ($definition['check_count'] ?? 17);
            $count = (int) ($definition['default_rows'] ?? 2);
            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = self::blankSavingsRefundRow($checks);
            }

            return ['rows' => $rows];
        }

        if ($layout === self::LAYOUT_SOCIETY_MANAGEMENT) {
            $statsCount = (int) ($definition['default_stats_rows'] ?? 5);
            $stats = [];
            for ($i = 0; $i < $statsCount; $i++) {
                $stats[] = self::blankManagementStatsRow();
            }

            $items = [];
            foreach ($definition['questions'] ?? [] as $_q) {
                $items[] = self::blankManagementItem();
            }

            return [
                'stats_rows' => $stats,
                'items' => $items,
            ];
        }

        $sections = [];
        foreach ($definition['sections'] ?? [] as $key => $section) {
            $rows = [];
            $count = (int) ($section['default_rows'] ?? 2);
            $checks = (int) ($section['check_count'] ?? 0);
            for ($i = 0; $i < $count; $i++) {
                $rows[] = self::blankSocietyRow($checks);
            }
            $sections[$key] = $rows;
        }

        return ['sections' => $sections];
    }

    /**
     * @return array{society_name: string, member_name: string, refund_date: string, voucher_no: string, amount: string, checks: list<string>, wp_ref: string}
     */
    public static function blankSavingsRefundRow(int $checkCount): array
    {
        return [
            'society_name' => '',
            'member_name' => '',
            'refund_date' => '',
            'voucher_no' => '',
            'amount' => '',
            'checks' => array_fill(0, max(0, $checkCount), ''),
            'wp_ref' => '',
        ];
    }

    /**
     * @return array{society_name: string, member_name: string, savings_amount: string, loan_amount: string, checks: list<string>, wp_ref: string}
     */
    public static function blankSavingsLoanRow(int $checkCount): array
    {
        return [
            'society_name' => '',
            'member_name' => '',
            'savings_amount' => '',
            'loan_amount' => '',
            'checks' => array_fill(0, max(0, $checkCount), ''),
            'wp_ref' => '',
        ];
    }

    /**
     * @return array{fo_name: string, society_no: string, formed_date: string, accepted_date: string, member_count: string, borrower_count: string, savings_balance: string, loan_balance: string, arrear_count: string, arrear_amount: string}
     */
    public static function blankManagementStatsRow(): array
    {
        return [
            'fo_name' => '',
            'society_no' => '',
            'formed_date' => '',
            'accepted_date' => '',
            'member_count' => '',
            'borrower_count' => '',
            'savings_balance' => '',
            'loan_balance' => '',
            'arrear_count' => '',
            'arrear_amount' => '',
        ];
    }

    /**
     * @return array{compliance: string, incident_count: string, wp_ref: string}
     */
    public static function blankManagementItem(): array
    {
        return [
            'compliance' => '', // yes | no | ''
            'incident_count' => '',
            'wp_ref' => '',
        ];
    }

    /**
     * @return array{society_name: string, start_date: string, field_worker: string, checks: list<string>, wp_ref: string}
     */
    public static function blankSocietyRow(int $checkCount): array
    {
        return [
            'society_name' => '',
            'start_date' => '',
            'field_worker' => '',
            'checks' => array_fill(0, max(0, $checkCount), ''),
            'wp_ref' => '',
        ];
    }

    /**
     * @return array{society_name: string, fo_name: string, member_name: string, checks: list<string>, wp_ref: string}
     */
    public static function blankMemberRow(int $checkCount): array
    {
        return [
            'society_name' => '',
            'fo_name' => '',
            'member_name' => '',
            'checks' => array_fill(0, max(0, $checkCount), ''),
            'wp_ref' => '',
        ];
    }

    /** @deprecated Use blankSocietyRow */
    public static function blankRow(int $checkCount): array
    {
        return self::blankSocietyRow($checkCount);
    }
}
