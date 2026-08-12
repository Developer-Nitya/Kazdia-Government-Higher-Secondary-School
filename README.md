# কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয় - এডিটেবল স্ট্যাটিক হোমপেজ

এই প্রজেক্টের মূল ওয়েবসাইট HTML/CSS/JS ভিত্তিক। `সাবেক প্রতিষ্ঠান প্রধানগণ` অংশের ডাটা এন্ট্রি করার জন্য PHP ব্যাকেন্ড এডমিন প্যানেল যুক্ত করা হয়েছে। MySQL ডাটাবেজ লাগবে না; তথ্য `storage/former-heads.json` ফাইলে সংরক্ষণ হবে।

## নতুনভাবে যুক্ত/আপডেট করা অংশ

- প্রথম সেকশনে EIIN, স্থাপিত সাল এবং ইমেইল সবুজ ব্যাকগ্রাউন্ডে সাদা টেক্সটে বেশি দূরত্বসহ দেখানো হয়েছে।
- পরের সেকশনে 100% width এবং 350px height-এর ডাইনামিক ব্যানার রাখা হয়েছে।
- ব্যানারে ৩টি ব্যাকগ্রাউন্ড ছবি পর্যায়ক্রমে অটো পরিবর্তন হয়।
- ব্যানারের ওপর হালকা ডার্ক শ্যাডো/ওভারলে দেওয়া হয়েছে, যাতে টেক্সট স্পষ্ট থাকে।
- বাম পাশে রাউন্ড লোগো এরিয়া এবং ডান পাশে প্রতিষ্ঠানের নাম ও স্লোগান রাখা হয়েছে।
- মেন্যুবারে “হোম” শব্দের পরিবর্তে হোম আইকন যুক্ত করা হয়েছে।
- “প্রতিষ্ঠান সম্পর্কিত তথ্য” ড্রপডাউন মেন্যু যুক্ত করা হয়েছে।
- ড্রপডাউনের অধীনে `সংক্ষিপ্ত বিবরণ` এবং `সাবেক প্রতিষ্ঠান প্রধানগণ` পেজ যুক্ত করা হয়েছে।
- মোবাইল, ট্যাব ও ডেস্কটপে রেসপন্সসিভ CSS যুক্ত করা হয়েছে।
- গ্যালারি বক্সগুলো এখন clickable album; প্রতিটি বক্সে একাধিক ছবি আপলোড করা যায় এবং নতুন ট্যাবে slideshow হিসেবে দেখা যায়।


## শিক্ষার ধরন ও একাডেমিক তথ্যপেজ

- হোমপেজের চারটি শিক্ষাস্তরের “বিস্তারিত দেখুন” লিংক নতুন ট্যাবে খোলে।
- প্রতিটি শিক্ষাস্তরের বিস্তারিত পেজের ডান পাশে চারটি রঙিন তথ্য লিংক আছে।
- প্রতি শিক্ষাস্তরে শিক্ষক-কর্মচারী, পাবলিক পরীক্ষার ফলাফল, প্রাতিষ্ঠানিক ফলাফল এবং শ্রেণি শিক্ষক—চারটি করে মোট ১৬টি আলাদা পেজ `pages/` ফোল্ডারে রাখা হয়েছে।
- প্রতিটি তথ্যপেজে হোমপেজ ও মূল শিক্ষাস্তরের পেজে ফেরার সরাসরি লিংক আছে।
- `admin/content.php#programs` থেকে ডান পাশের লিংকের আইকন, শিরোনাম ও URL পরিবর্তন করা যায়।
- `admin/content.php#program-resources` থেকে ১৬টি পেজের টাইটেল, বর্ণনা, টেবিল কলাম, টেবিল সারি এবং নোট সম্পাদনা করা যায়।
- মূল ডাটা `storage/site-content.json`-এর `programs[*].resourceLinks` ও `programResources` অংশে সংরক্ষিত হয়।
- বিস্তারিত ফাইল তালিকা ও সম্পাদনা নিয়ম: `ACADEMIC_RESOURCE_PAGES_UPDATE_NOTES.md`।

## ফাইল স্ট্রাকচার

```text
Kazdia_homepage_design/
├── index.html
├── admin/
│   ├── index.php
│   └── former-heads.php
├── api/
│   └── former-heads.php
├── backend/
│   ├── config.php
│   └── helpers.php
├── storage/
│   └── former-heads.json
├── uploads/
│   └── former-heads/
├── pages/
│   ├── brief-history.html
│   ├── former-heads.html
│   ├── secondary.html
│   ├── secondary-vocational.html
│   ├── higher-secondary.html
│   └── higher-secondary-bm.html
└── assets/
    ├── css/styles.css
    ├── js/site-settings.js
    ├── js/content-data.js
    ├── js/content-editor.js
    ├── js/main.js
    └── img/*.svg
```

## কীভাবে সহজে এডিট করবেন

প্রতিষ্ঠানের নাম, স্লোগান, EIIN, স্থাপিত সাল, ইমেইল, লোগো এবং ব্যানার ছবি পরিবর্তনের জন্য `assets/js/site-settings.js` ফাইলটি খুলুন।

```js
window.SITE_SETTINGS = {
  institutionName: "কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়",
  slogan: "জ্ঞান,শৃঙ্খলা ও দক্ষতায় গড়ি আলোকিত ভবিষ্যৎ",
  eiin: "117396",
  established: "১৯৫৭",
  email: "kazdiahighersecondaryschool57@gmail.com",
  logo: "logo.jpg",
  bannerImages: [
    "bg-1.jpg",
    "bg-2.jpg",
    "bg-3.jpg"
  ]
};
```

`সংক্ষিপ্ত বিবরণ` এবং `সাবেক প্রতিষ্ঠান প্রধানগণ` পেজের ডিফল্ট/স্থায়ী তথ্য পরিবর্তনের জন্য `assets/js/content-data.js` ফাইলটি এডিট করুন। পেজের ফর্ম দিয়ে তথ্য ইনপুট করলে সেটি সংশ্লিষ্ট ব্রাউজারের localStorage-এ সংরক্ষণ হবে।

### ব্যানারের ছবি পরিবর্তন

১. নতুন ছবি `assets/img/` ফোল্ডারে রাখুন।  
২. `site-settings.js` ফাইলে `bannerImages`-এর filename পরিবর্তন করুন।  
৩. একইভাবে লোগো বদলাতে চাইলে `logo`-এর filename পরিবর্তন করুন।

## লোকালভাবে চালানো

হোমপেজ দেখতে ডাবল ক্লিক করে `index.html` খুলতে পারেন। ডাইনামিক কনটেন্ট ও এডমিন প্যানেল চালাতে PHP সার্ভার লাগবে। প্রজেক্ট ফোল্ডারের ভেতর থেকে চালান:

```bash
php -S localhost:8000
```

তারপর ব্রাউজারে হোমপেজের জন্য `http://localhost:8000` এবং এডমিন প্যানেলের জন্য `http://localhost:8000/admin/` খুলুন।

## হোস্টিংয়ে আপলোড

পুরো `Kazdia_homepage_design` ফোল্ডারের সব ফাইল একইভাবে PHP-enabled hosting-এ আপলোড করুন। MySQL ডাটাবেজ ইমপোর্ট করার দরকার নেই।



## এডমিন প্যানেল কোথায় পাবেন

হোস্টিংয়ের public_html বা মূল ওয়েব ফোল্ডারে `Kazdia_homepage_design`-এর ভিতরের সব ফাইল আপলোড করলে এডমিন প্যানেল পাবেন:

```text
https://your-domain.com/admin/
```

যদি পুরো `Kazdia_homepage_design` ফোল্ডারসহ আপলোড করেন, তাহলে এডমিন প্যানেল হবে:

```text
https://your-domain.com/Kazdia_homepage_design/admin/
```

সরাসরি প্রতিষ্ঠান প্রধানদের ডাটা এন্ট্রি পেজ:

```text
https://your-domain.com/admin/former-heads.php
```

ডিফল্ট পাসওয়ার্ড `backend/config.php` ফাইলে আছে। লাইভ হোস্টিংয়ে আপলোডের আগে অবশ্যই `SCHOOL_ADMIN_PASSWORD` পরিবর্তন করুন।

## ব্যাকেন্ডে সাবেক প্রতিষ্ঠান প্রধানদের ডাটা এন্ট্রি

এই ভার্সনে `সাবেক প্রতিষ্ঠান প্রধানগণ` পেজটি শুধু ফ্রন্টেন্ড তালিকা দেখাবে। ডাটা এন্ট্রি, এডিট, ডিলিট এবং ছবি আপলোডের ফর্ম ব্যাকেন্ডে রাখা হয়েছে।

- মূল এডমিন প্যানেল: `admin/index.php`
- ব্যাকেন্ড ডাটা এন্ট্রি পেজ: `admin/former-heads.php`
- ফ্রন্টেন্ড তালিকা: `pages/former-heads.html`
- ফ্রন্টেন্ড API: `api/former-heads.php`
- ডাটা স্টোরেজ: `storage/former-heads.json`
- আপলোডেড ছবি: `uploads/former-heads/`

### হোস্টিংয়ে ব্যবহারের নিয়ম

১. পুরো `Kazdia_homepage_design` ফোল্ডার PHP-enabled hosting-এ আপলোড করুন।  
২. `backend/config.php` ফাইল খুলে `SCHOOL_ADMIN_PASSWORD` অবশ্যই পরিবর্তন করুন।  
৩. `storage/` এবং `uploads/former-heads/` ফোল্ডারে write permission দিন, সাধারণত 755 বা প্রয়োজনে 775।  
৪. ব্রাউজার থেকে `admin/` খুলে লগইন করুন, এরপর ড্যাশবোর্ড থেকে `সাবেক প্রতিষ্ঠান প্রধানগণ` ম্যানেজ করুন।  
৫. মেন্যুর `সাবেক প্রতিষ্ঠান প্রধানগণ` লিংকে ক্লিক করলে নিউ ট্যাবে শুধু তালিকা দেখা যাবে।

ডিফল্ট পাসওয়ার্ড: `ChangeThisPassword123!` — আপলোডের আগে এটি পরিবর্তন করা জরুরি।


---

## নতুন ডাইনামিক CMS আপডেট

এই ভার্সনে static HTML structure অক্ষত রেখে সম্পূর্ণ editable backend যুক্ত করা হয়েছে।

প্রধান নতুন ফাইল:

- `admin/content.php` — সম্পূর্ণ ওয়েবসাইট কনটেন্ট এডিট প্যানেল
- `api/site-content.php` — frontend dynamic content API
- `assets/js/dynamic-content.js` — HTML layout না বদলে কনটেন্ট বসানোর renderer
- `storage/site-content.json` — মূল file-based JSON database
- `DYNAMIC_WEBSITE_GUIDE.md` — hosting/upload/admin guide
- `DATABASE_SCHEMA.md` — database schema

Admin panel:

```text
/admin/
```

Default password:

```text
ChangeThisPassword123!
```

Upload করার আগে `backend/config.php` থেকে password পরিবর্তন করুন।

## Professional Footer

- সব public page-এ একই responsive professional footer যুক্ত আছে।
- Footer data `storage/site-content.json`-এর `footer` object থেকে আসে।
- `admin/content.php#footer` থেকে footer content edit করা যায়।
- Static hosting fallback: `assets/js/site-settings.js`-এর `SITE_FOOTER`।
- Privacy policy: `pages/privacy-policy.html`
- Terms: `pages/terms-and-conditions.html`
- বিস্তারিত: `PROFESSIONAL_FOOTER_UPDATE_NOTES.md`

## সেবাসমূহের ৫৯টি পৃথক পেজ

- হোমপেজের ১৫টি সেবা সাব-সেকশনের অধীন ৫৯টি লিংক এখন `pages/service-XX-YY.html` পেজে খোলে।
- প্রতিটি পেজে সাইটের বর্তমান hero/header, navigation এবং professional footer রয়েছে।
- তথ্য ও ফাইল ব্যবস্থাপনা: `admin/service-pages.php`
- JSON database: `storage/service-pages.json`
- Upload directory: `uploads/service-pages/`
- Public API: `api/service-pages.php`
- বিস্তারিত নির্দেশনা: `SERVICE_PAGES_UPDATE_NOTES.md`

Hosting-এ `storage/`, `uploads/` এবং `uploads/service-pages/` writable রাখুন।
