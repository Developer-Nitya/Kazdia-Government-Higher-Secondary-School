# ডাইনামিক ওয়েবসাইট ব্যবহারের গাইড

## কী করা হয়েছে

এই প্যাকেজে আপনার আগের static HTML/CSS layout অক্ষত রেখে PHP backend যুক্ত করা হয়েছে। Visitor-facing পেজগুলো এখনও `index.html` এবং `pages/*.html` হিসেবেই থাকবে, কিন্তু কনটেন্ট লোড হবে backend JSON database থেকে।

## Admin Panel

হোস্টিংয়ে আপলোড করার পর ব্রাউজারে খুলুন:

```text
https://your-domain.com/admin/
```

ডিফল্ট পাসওয়ার্ড:

```text
ChangeThisPassword123!
```

নিরাপত্তার জন্য আপলোডের আগে/পরে `backend/config.php` ফাইলে `SCHOOL_ADMIN_PASSWORD` পরিবর্তন করুন।

## কোন কোন অংশ এডিট করা যাবে

`admin/content.php` থেকে:

- প্রতিষ্ঠানের নাম, স্লোগান, EIIN, স্থাপিত সাল, ইমেইল, ফোন, ঠিকানা, অফিস সময়
- লোগো ও ব্যানার ছবি
- হোমপেজ স্লাইডার
- শিক্ষার ধরন কার্ড
- প্রতিষ্ঠাতা/অধ্যক্ষ/শিক্ষক সংগঠন প্রোফাইল
- গুরুত্বপূর্ণ লিংক ও দাপ্তরিক লিংক
- সংক্ষিপ্ত বিবরণ পেজ
- যোগাযোগ পেজ
- নোটিশ বোর্ড
- সেবা সমূহ
- গ্যালারি: প্রতিটি বক্সের Cover image, একাধিক album image upload/path, এবং নতুন ট্যাবে slideshow
- মাধ্যমিক, ভোকেশনাল, উচ্চ মাধ্যমিক ও বিএম পেজের টেবিল/নোটিশ/যোগাযোগ কর্মকর্তা
- Advanced JSON editor দিয়ে অতিরিক্ত আইটেম যোগ/মুছা

আগের মতো আলাদা পেজ থেকেও এডিট করা যাবে:

- `admin/former-heads.php` — সাবেক প্রতিষ্ঠান প্রধানগণ
- `admin/national-anthem.php` — জাতীয় সংগীত অডিও/ভিডিও

## Database/Storage

এই সাইট MySQL ছাড়া কাজ করার জন্য file-based JSON database ব্যবহার করে:

```text
storage/site-content.json
storage/former-heads.json
storage/media-settings.json
```

Uploaded images/media থাকবে:

```text
uploads/media/
uploads/former-heads/
```

হোস্টিংয়ে এই ফোল্ডারগুলো writable থাকতে হবে। সাধারণ cPanel hosting-এ Permission `755` বা প্রয়োজন হলে `775` দিলেই যথেষ্ট হয়।

## Upload করার নিয়ম

1. ZIP extract করুন।
2. পুরো `shed_homepage_design` ফোল্ডারের ভেতরের সব ফাইল hosting public folder-এ upload করুন।
3. `backend/config.php` ফাইলের admin password পরিবর্তন করুন।
4. নিশ্চিত করুন `storage/` এবং `uploads/` ফোল্ডার writable।
5. ব্রাউজারে `your-domain.com/admin/` খুলে লগইন করুন।
6. `সম্পূর্ণ ওয়েবসাইট কনটেন্ট` থেকে প্রয়োজনীয় সব তথ্য আপডেট করুন।

## গুরুত্বপূর্ণ

- HTML structure/layout পরিবর্তন করা হয়নি; শুধু dynamic script `assets/js/dynamic-content.js` যোগ করা হয়েছে।
- Visitor পেজে data API থেকে কনটেন্ট আসে: `api/site-content.php`
- কোনো external PHP library লাগবে না।
- Server-এ PHP 8+ থাকলেই চলবে।


## Hosting Compatibility Check

Upload করার পর একবার খুলে দেখতে পারেন:

```text
https://your-domain.com/hosting-check.php
```

সবগুলো `PASS` হলে backend/database/upload permission ঠিক আছে। চেক শেষ হলে নিরাপত্তার জন্য `hosting-check.php` delete করতে পারেন।


## গ্যালারি অ্যালবাম ব্যবহার

Admin panel → `ওয়েবসাইট কনটেন্ট` → `গ্যালারি` অংশে প্রতিটি বক্সে:

- `Cover image` দিলে সেটি গ্যালারি বক্সে দেখাবে।
- `অ্যালবামের একাধিক ছবি আপলোড` দিয়ে একসাথে অনেক ছবি আপলোড করা যাবে।
- `অ্যালবাম image path/URL` textarea-তে প্রতিটি লাইনে একটি image path বা image URL দেওয়া যাবে।
- Visitor গ্যালারি বক্সে ক্লিক করলে `pages/gallery-viewer.html` নতুন ট্যাবে খুলবে এবং ছবিগুলো ৩.৫ সেকেন্ড পরপর পর্যায়ক্রমে দেখাবে।



============================================================
শিক্ষার ধরন ও ১৬টি একাডেমিক তথ্যপেজ
============================================================

১. Admin panel → সম্পূর্ণ ওয়েবসাইট কনটেন্ট → একাডেমিক পেজ খুলুন।
২. প্রতিটি শিক্ষাস্তরের “ডান পাশের রঙিন তথ্য লিংক” ঘরে এই ফরম্যাট ব্যবহার করুন:
   আইকন | শিরোনাম | pages/ফাইলের-নাম.html
৩. Admin panel-এর “একাডেমিক তথ্যপেজ” অংশে ১৬টি পেজ আলাদাভাবে পাওয়া যাবে।
৪. টেবিলের কলাম একই লাইনে | দিয়ে আলাদা করুন।
৫. টেবিলের প্রতিটি নতুন সারি নতুন লাইনে লিখুন এবং সারির cell-গুলো | দিয়ে আলাদা করুন।
৬. Save করার পর সংশ্লিষ্ট visitor page reload করুন।
৭. সব পেজের হোম লিংক ../index.html এবং মূল শিক্ষাস্তরের লিংক pages ফোল্ডারের ভেতরে relative path ব্যবহার করে।
৮. মূল ডাটা storage/site-content.json-এ থাকে; storage/ writable না হলে পরিবর্তন সংরক্ষিত হবে না।
