# SHED Service Box Update Notes

## কী পরিবর্তন করা হয়েছে

- `index.html` এবং `pages/services.html`-এর সেবা বক্স অংশে shed.gov.bd হোমপেজের সেবা ক্যাটাগরি ও লিংক যুক্ত করা হয়েছে।
- প্রতিটি সেবা লিংকে `target="_blank"` এবং `rel="noopener noreferrer"` দেওয়া হয়েছে, তাই সব লিংক নতুন ট্যাবে খুলবে।
- নতুন আলাদা CSS ফাইল `assets/css/shed-services.css` যোগ করা হয়েছে।
- সেবা বক্সের link text বাম পাশে আরও compact করা হয়েছে এবং line-height/gap কমানো হয়েছে, যাতে বক্সের height কম থাকে।
- link/title/summary text-এর জন্য Bengali-friendly unique font stack ব্যবহার করা হয়েছে: `Hind Siliguri`, `Noto Sans Bengali`, `SolaimanLipi`, `Kalpurush` fallback সহ।
- প্রথমে ৬টি সেবা বক্স দেখা যাবে। বাকি বক্সগুলো দেখাতে “সকল সেবাসমূহ দেখুন” button এবং আবার hide করতে “সংক্ষিপ্ত” button যোগ করা হয়েছে।
- static HTML fallback এবং dynamic JSON/API rendering—দুই জায়গাতেই একই expand/collapse behavior কাজ করবে।
- ডাইনামিক রেন্ডারারের জন্য `assets/js/dynamic-content.js` আপডেট করা হয়েছে, যাতে JSON/API থেকে আসা সেবা বক্সগুলোও একই ডিজাইন ও আচরণ বজায় রাখে।
- `storage/site-content.json`-এর `services` ডেটা আপডেট করা হয়েছে, যাতে হোস্টিংয়ে PHP API চালু থাকলে একই সেবা বক্স ডাইনামিকভাবে লোড হয়।

## এডিট করার জায়গা

- ডিজাইন, spacing, font বা button style পরিবর্তন করতে: `assets/css/shed-services.css`
- সেবা বক্সের ডেটা পরিবর্তন করতে: `storage/site-content.json` এর `services` অ্যারে
- expand/collapse বা dynamic rendering logic পরিবর্তন করতে: `assets/js/dynamic-content.js`
- static fallback content পরিবর্তন করতে: `index.html` এবং `pages/services.html`

## হোস্টিং নোট

সাইটটি বিদ্যমান PHP/JSON স্টোরেজ স্ট্রাকচার ব্যবহার করে। আলাদা SQL ডাটাবেজ প্রয়োজন নেই। হোস্টিংয়ে আপলোডের পর PHP চালু থাকলে `api/site-content.php` থেকে সেবা ডেটা লোড হবে; PHP না থাকলেও HTML fallback সেবা বক্স দেখাবে।
