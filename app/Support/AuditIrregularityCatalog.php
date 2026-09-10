<?php

namespace App\Support;

/**
 * Canonical Findings Matrix catalog:
 * নিরীক্ষা খাত → সাব-খাত → অনিয়ম কোড → আপত্তি শিরোনাম
 */
class AuditIrregularityCatalog
{
    /**
     * @return list<array{
     *     category: string,
     *     sub_category: string|null,
     *     indicator_code: string,
     *     title: string,
     *     risk_rating: string|null
     * }>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '২০০০-১',
                'title' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে প্রদানকৃত চাহিদা অপেক্ষায় প্রকৃত খরচ কম হওয়া',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '২০০০-২',
                'title' => 'ঋণ অনুমোদন ব্যতীত দৈনিক আর্থিক চাহিদা রেজিস্টারে ঋণ বিতরণের চাহিদা রাখা যা ঋণ নীতিমালা বহি:র্ভুত',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '২০০০-৩',
                'title' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে খরচের চাহিদা না রেখে ব্যাংক থেকে টাকা উত্তোলন করে ঋণ বিতরন ও খরচ করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '২০০০-৪',
                'title' => 'স্টক/মজুদ রেজিষ্টারে স্টেশনারী/অন্যান্য ঋণ কার্যক্রমের প্রিন্টিং সামগ্রী এন্ট্রি না দেওয়া এবং স্টক রেজিস্টার আপডেট না থাকা।',
                'risk_rating' => null,
            ],
            [
                'category' => 'কর্মসূচী সংক্রান্ত',
                'sub_category' => 'ঋণ সংক্রান্ত',
                'indicator_code' => '১০০০-১',
                'title' => 'পাসবইতে এন্ট্রি না দিয়ে সদস্যর কাছ থেকে কিস্তি আদায় করা (সদস্যর নাম,আইডি, সমিতি নং, আদায় তারিখ)',
                'risk_rating' => null,
            ],
            [
                'category' => 'কর্মসূচী সংক্রান্ত',
                'sub_category' => 'ঋণ সংক্রান্ত',
                'indicator_code' => '১০০০-২',
                'title' => 'কর্মী কর্তৃক পাশ বইয়ের ব্যালেন্স কাটাকাটি করে আত্নসাৎ করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'কর্মসূচী সংক্রান্ত',
                'sub_category' => 'ঋণ সংক্রান্ত',
                'indicator_code' => '১০০০-৩',
                'title' => 'যাতায়াতে মোটরসাইকেল ব্যবহার করে বিল নেওয়া হচ্ছে সিএনজি+রিক্সা',
                'risk_rating' => null,
            ],
            [
                'category' => 'কর্মসূচী সংক্রান্ত',
                'sub_category' => 'ঋণ সংক্রান্ত',
                'indicator_code' => '১০০০-৪',
                'title' => 'সহকারী ব্যবস্থাপক কর্তৃক বাস্তবে পাস বই ক্রসচেক না করেই রির্পোট করা হয়েছে',
                'risk_rating' => null,
            ],
            [
                'category' => 'স্থায়ী সম্পদ সংক্রান্ত',
                'sub_category' => 'স্থায়ী সম্পদ',
                'indicator_code' => '৩০০০-১',
                'title' => 'স্থায়ী সম্পদ ক্রয়ের কোটেশন সংগ্রহ না করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'স্থায়ী সম্পদ সংক্রান্ত',
                'sub_category' => 'স্থায়ী সম্পদ',
                'indicator_code' => '৩০০০-২',
                'title' => 'স্থায়ী সম্পদ ক্রয়ের ক্রয় কমিটি ব্যতীত ক্রয় করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'স্থায়ী সম্পদ সংক্রান্ত',
                'sub_category' => 'স্থায়ী সম্পদ',
                'indicator_code' => '৩০০০-৩',
                'title' => 'স্থায়ী সম্পদ ক্রয়ের মূল্য ব্যাংক চেকে না দিয়ে নগদে পরিশোধ করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'স্থায়ী সম্পদ সংক্রান্ত',
                'sub_category' => 'স্থায়ী সম্পদ',
                'indicator_code' => '৩০০০-৪',
                'title' => 'স্থায়ী সম্পদ এর অবচয় ধার্য্য কম করা/না করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ ও হিসাব সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৪০০০-১',
                'title' => 'প্রোডাক্ট অনুযায়ী/মোট MIS ও AIS প্রতিবেদনের সাথে সঞ্চয়স্থিতির পার্থক্য',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ ও হিসাব সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৪০০০-২',
                'title' => 'প্রোডাক্ট অনুযায়ী/মোট MIS ও AIS প্রতিবেদনের সাথে ঋণস্থিতির পার্থক্য',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ ও হিসাব সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৪০০০-৩',
                'title' => 'AIS প্রতিবেদনে সুফলন প্রোডাক্টে ঋণাত্বক সঞ্চয়স্থিতি পার্থক্য',
                'risk_rating' => null,
            ],
            [
                'category' => 'অর্থ ও হিসাব সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৪০০০-৪',
                'title' => 'AIS and MIS এর মধ্যে সঞ্চয় প্রোডাক্ট অনুযায়ী স্থিতি পার্থক্য থাকা এবং AIS -এ DSK Double Scheme এর তথ্য রয়েছে কিন্তু MIS-এ FDR-Personal থাকা',
                'risk_rating' => null,
            ],
            [
                'category' => 'মানব সম্পদ সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৫০০০-১',
                'title' => 'অনুমোদন ব্যতীত কুক নিয়োগ',
                'risk_rating' => null,
            ],
            [
                'category' => 'মানব সম্পদ সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৫০০০-২',
                'title' => 'একই স্টাফ দীর্ঘদিন একই শাখায় থাকা',
                'risk_rating' => null,
            ],
            [
                'category' => 'মানব সম্পদ সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৫০০০-৩',
                'title' => 'অভিযোগ বক্স স্থাপন না করা',
                'risk_rating' => null,
            ],
            [
                'category' => 'মানব সম্পদ সংক্রান্ত',
                'sub_category' => null,
                'indicator_code' => '৫০০০-৪',
                'title' => 'নারী বান্ধব টয়লেট এর বক্সে প্রয়োজনীয় কীট না থাকা',
                'risk_rating' => null,
            ],
        ];
    }
}
