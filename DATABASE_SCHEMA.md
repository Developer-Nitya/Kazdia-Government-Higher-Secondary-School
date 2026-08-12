# Content Database Schema

এই প্রজেক্টের মূল editable database হলো `storage/site-content.json`।

## Root Keys

```json
{
  "siteSettings": {},
  "footer": {},
  "home": {},
  "pages": {},
  "notices": [],
  "services": [],
  "gallery": [],
  "programs": {},
  "programResources": {}
}
```

## siteSettings

- `institutionName`
- `slogan`
- `eiin`
- `established`
- `email`
- `phone`
- `address`
- `officeHours`
- `logo`
- `bannerImages`


## footer

Professional footer-এর সব editable data:

```json
{
  "homeUrl": "index.html",
  "description": "বিদ্যালয়ের সংক্ষিপ্ত পরিচিতি",
  "mapUrl": "https://www.google.com/maps/search/?api=1&query=...",
  "privacyUrl": "pages/privacy-policy.html",
  "termsUrl": "pages/terms-and-conditions.html",
  "lastUpdated": "",
  "developerLabel": "কারিগরি ব্যবস্থাপনা:",
  "developerName": "ওয়েবসাইট প্রশাসন",
  "quickLinks": [
    { "title": "হোমপেজ", "url": "index.html" }
  ],
  "socialLinks": [
    { "title": "Facebook", "url": "", "icon": "facebook" }
  ],
  "officialLinks": [
    { "title": "শিক্ষা মন্ত্রণালয়", "url": "https://moedu.gov.bd/" }
  ]
}
```

- খালি social বা optional URL visitor-facing footer-এ link হিসেবে দেখানো হবে না।
- `lastUpdated` খালি থাকলে browser/server file modification date থেকে তারিখ দেখানো হবে।
- Footer-এর নাম, EIIN, প্রতিষ্ঠাকাল, ঠিকানা, ফোন, email ও office time `siteSettings` থেকে নেওয়া হয়।

## home

- `slides`: homepage slider
- `educationCards`: শিক্ষার ধরন কার্ড
- `profiles`: sidebar profile cards
- `importantLinks`: গুরুত্বপূর্ণ লিংক
- `officialLinks`: দাপ্তরিক লিংক

## pages

- `briefHistory`: সংক্ষিপ্ত বিবরণ
- `contact`: যোগাযোগ পেজের অতিরিক্ত টেক্সট

## notices

প্রতিটি item:

```json
{
  "title": "নোটিশের শিরোনাম",
  "category": "নতুন",
  "url": "#"
}
```

## services

প্রতিটি service:

```json
{
  "title": "সেবা শিরোনাম",
  "image": "assets/img/service-about.svg",
  "items": [
    { "title": "লিংক", "url": "#" }
  ]
}
```

## gallery

প্রতিটি item:

```json
{
  "title": "ছবির নাম",
  "image": "assets/img/gallery-1.svg"
}
```

## programs

Key হিসেবে page slug ব্যবহৃত:

- `secondary`
- `secondary-vocational`
- `higher-secondary`
- `higher-secondary-bm`

প্রতিটি program:

```json
{
  "page": "pages/secondary.html",
  "title": "মাধ্যমিক শিক্ষা",
  "subtitle": "পেজের উপরের বিবরণ",
  "infoRows": [["লেবেল", "মান"]],
  "notices": [{ "title": "নোটিশ", "category": "PDF", "url": "#" }],
  "contactOfficer": {
    "role": "যোগাযোগ কর্মকর্তা",
    "name": "কর্মকর্তার নাম",
    "text": "শাখা",
    "image": "assets/img/principal.svg",
    "url": "contact.html"
  }
}
```


## Gallery Album Schema

`storage/site-content.json` ফাইলের `gallery` array-র প্রতিটি object এখন album হিসেবে কাজ করে।

```json
{
  "title": "পাঠাগার কার্যক্রম",
  "image": "assets/img/gallery-album-library-activities.svg",
  "images": [
    "assets/img/gallery-album-library-activities.svg",
    "uploads/media/cms-uploaded-image-1.jpg",
    "uploads/media/cms-uploaded-image-2.jpg"
  ]
}
```

- `title`: গ্যালারি বক্সের শিরোনাম।
- `image`: বক্সে দেখানো Cover image।
- `images`: slideshow-তে দেখানো image path/URL list। এখানে একাধিক ছবি রাখা যাবে।


## programs.resourceLinks

প্রতিটি `programs` item-এ ডান পাশের রঙিন তথ্য লিংক:

```json
{
  "resourceLinks": [
    {
      "icon": "👥",
      "title": "মাধ্যমিক স্তরের শিক্ষক ও কর্মচারীর তালিকা",
      "url": "pages/secondary-teachers-staff.html"
    }
  ]
}
```

- `icon`: লিংকের আইকন বা emoji
- `title`: দৃশ্যমান শিরোনাম
- `url`: `pages/` ফোল্ডারের সংশ্লিষ্ট পেজ
- visitor page-এ প্রতিটি লিংক নতুন ট্যাবে খোলে

## programResources

চারটি শিক্ষাস্তরের জন্য চার ধরনের তথ্যপেজ—মোট ১৬টি item:

```json
{
  "secondary-teachers-staff": {
    "page": "pages/secondary-teachers-staff.html",
    "program": "secondary",
    "type": "teachers-staff",
    "icon": "👥",
    "title": "মাধ্যমিক স্তরের শিক্ষক ও কর্মচারীর তালিকা",
    "subtitle": "পেজের সংক্ষিপ্ত পরিচিতি",
    "description": "টেবিলের আগে দেখানো বিস্তারিত বর্ণনা",
    "columns": ["ক্রমিক", "নাম", "পদবি"],
    "rows": [
      ["১", "শিক্ষকের নাম", "সহকারী শিক্ষক"]
    ],
    "note": "টেবিলের নিচের নোট"
  }
}
```

Admin panel-এর `একাডেমিক তথ্যপেজ` অংশে `columns` ও প্রতিটি `rows`-এর cell `|` চিহ্ন দিয়ে আলাদা করে সম্পাদনা করা যায়।
