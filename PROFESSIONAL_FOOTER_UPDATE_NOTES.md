# প্রফেশনাল ফুটার আপডেট নোট

## আপডেটের সারসংক্ষেপ

বিদ্যালয়ের বিদ্যমান সবুজ–সাদা থিম অক্ষুণ্ণ রেখে সব public page-এ একই professional ও responsive footer যুক্ত করা হয়েছে। Header, navigation, slider, main content, sidebar এবং বিদ্যমান page layout পরিবর্তন করা হয়নি।

## ফুটারের অংশসমূহ

- বিদ্যালয়ের logo, নাম, slogan, পরিচিতি, প্রতিষ্ঠাকাল ও EIIN
- গুরুত্বপূর্ণ navigation link
- ঠিকানা, ফোন, email, office time ও Google Maps link
- Facebook, YouTube এবং অন্যান্য social link-এর editable section
- সরকারি শিক্ষা-সংক্রান্ত দাপ্তরিক link
- স্বয়ংক্রিয় বর্তমান বছর, সর্বশেষ হালনাগাদ, privacy policy, terms এবং technical credit
- keyboard focus state, readable contrast এবং reduced-motion support

## এডমিন প্যানেল থেকে সম্পাদনা

1. PHP-enabled hosting-এ project upload করুন।
2. `admin/content.php` খুলুন।
3. এডমিন password দিয়ে login করুন।
4. **ফুটার** section থেকে description, map URL, important links, official links, social links, legal links ও credit edit করুন।
5. প্রতিষ্ঠানের নাম, EIIN, phone, email, address ও office time **মূল সেটিংস** section থেকে edit করুন।

## Static hosting fallback

PHP API unavailable হলে `assets/js/site-settings.js`-এর `SITE_SETTINGS` এবং `SITE_FOOTER` object edit করুন।

## Data source

- Primary hosting data: `storage/site-content.json`
- Server fallback/default: `backend/helpers.php`
- Static fallback: `assets/js/site-settings.js`

## নতুন policy page

- `pages/privacy-policy.html`
- `pages/terms-and-conditions.html`

## পরিবর্তিত প্রধান file

- `index.html`
- `pages/*.html`
- `assets/css/styles.css`
- `assets/js/dynamic-content.js`
- `assets/js/site-settings.js`
- `storage/site-content.json`
- `backend/helpers.php`
- `admin/content.php`

## Responsive behavior

- Large desktop: চারটি column
- Medium desktop/tablet: তিনটি বা দুইটি column
- Mobile: এক column
- Email, address ও long URL-এ safe wrapping
- কোনো horizontal overflow তৈরি না হওয়ার জন্য প্রতিটি grid item-এ `min-width: 0` এবং text wrapping প্রয়োগ করা হয়েছে

## Cache version

Footer-related CSS ও JavaScript cache version:

`20260727-footer-1`

## যাচাই

- JavaScript syntax: সফল
- PHP syntax: সফল
- JSON parsing: সফল
- CSS parsing: সফল
- ১৪টি public HTML page-এ footer: সফল
- Duplicate HTML ID check: সফল
- Internal policy link: সফল
- ZIP integrity: ZIP তৈরির পরে যাচাই করতে হবে
