# Academic Resource Pages Update

## কী যুক্ত হয়েছে

হোমপেজের চারটি শিক্ষাস্তরের “বিস্তারিত দেখুন” লিংক এখন নতুন ট্যাবে খোলে। প্রতিটি শিক্ষাস্তরের বিস্তারিত পেজের ডান পাশে চারটি eye-catching responsive link card যুক্ত হয়েছে:

1. শিক্ষক ও কর্মচারীর তালিকা
2. বিগত বছরের পাবলিক পরীক্ষার ফলাফল
3. প্রাতিষ্ঠানিক পরীক্ষার ফলাফল
4. শ্রেণি শিক্ষকগণ

চারটি শিক্ষাস্তরে মোট ১৬টি আলাদা HTML পেজ `pages/` ফোল্ডারে তৈরি হয়েছে।

## তৈরি হওয়া পেজের naming pattern

```text
pages/<program>-teachers-staff.html
pages/<program>-public-results.html
pages/<program>-institutional-results.html
pages/<program>-class-teachers.html
```

`<program>` মান:

```text
secondary
secondary-vocational
higher-secondary
higher-secondary-bm
```

## Admin panel থেকে সম্পাদনা

```text
/admin/content.php#programs
```

এখানে প্রতিটি শিক্ষাস্তরের ডান পাশের রঙিন লিংক এডিট করুন:

```text
আইকন | শিরোনাম | URL
```

```text
/admin/content.php#program-resources
```

এখানে ১৬টি পেজের:

- page path
- মূল শিক্ষাস্তর
- icon
- title/subtitle
- description
- table columns
- table rows
- note

সম্পাদনা করা যায়।

টেবিল কলামের উদাহরণ:

```text
ক্রমিক | নাম | পদবি | বিষয়/দায়িত্ব | যোগাযোগ
```

টেবিল সারির উদাহরণ:

```text
১ | মোঃ উদাহরণ | সহকারী শিক্ষক | গণিত | ০১XXXXXXXXX
২ | মোছাঃ উদাহরণ | সহকারী শিক্ষক | বাংলা | ০১XXXXXXXXX
```

## Database

```text
storage/site-content.json
```

নতুন key:

```text
programs.<program>.resourceLinks
programResources
```

PHP default/fallback data `backend/helpers.php`-তেও রাখা হয়েছে। ফলে JSON ফাইল নতুন করে তৈরি হলেও ১৬টি পেজের প্রয়োজনীয় default data পাওয়া যাবে।

## Hosting checklist

1. PHP 8 বা পরবর্তী সংস্করণ ব্যবহার করুন।
2. `storage/` ও `uploads/` writable রাখুন; সাধারণত `755`, প্রয়োজনে `775`।
3. `backend/config.php`-এর default admin password পরিবর্তন করুন।
4. upload শেষে `/hosting-check.php` একবার চালান।
5. সব PASS হলে নিরাপত্তার জন্য `hosting-check.php` মুছে দিন।
6. `.htaccess` সমর্থিত Apache hosting হলে `storage/` ও `uploads/`-এর সুরক্ষা সক্রিয় থাকবে।

## পরিবর্তিত মূল ফাইল

```text
index.html
assets/css/styles.css
assets/js/dynamic-content.js
backend/helpers.php
admin/content.php
storage/site-content.json
hosting-check.php
README.md
DATABASE_SCHEMA.md
DYNAMIC_WEBSITE_GUIDE.md
ADMIN_PANEL_INSTRUCTIONS.txt
```
