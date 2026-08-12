# সেবাসমূহের ৫৯টি পৃথক পেজ আপডেট

## কী যোগ করা হয়েছে

- হোমপেজের “সেবাসমূহ” সেকশনের ১৫টি সাব-সেকশনের অধীন ৫৯টি লিংক এখন `pages/` ফোল্ডারের পৃথক পেজে খোলে।
- প্রতিটি পেজে বর্তমান সাইটের প্রতিষ্ঠান পরিচিতি hero/header, navigation এবং professional footer রাখা হয়েছে।
- প্রতিটি পেজে editable বিস্তারিত তথ্য এবং সংযুক্ত ফাইল/ডকুমেন্ট তালিকা রয়েছে।
- `admin/service-pages.php` থেকে পেজের শিরোনাম, সাব-সেকশন, hero লেখা, বিস্তারিত তথ্য এবং ফাইল পরিচালনা করা যাবে।
- তথ্য `storage/service-pages.json`-এ এবং ফাইল `uploads/service-pages/`-এ সংরক্ষিত হয়।
- মূল দাপ্তরিক URL শুধু admin reference হিসেবে সংরক্ষিত আছে।

## ব্যবহার

1. ব্রাউজারে `your-domain.com/admin/` খুলে লগইন করুন।
2. “সেবাসমূহের পৃথক পেজ” কার্ডে ক্লিক করুন।
3. dropdown থেকে প্রয়োজনীয় পেজ নির্বাচন করুন।
4. তথ্য লিখে “তথ্য সংরক্ষণ করুন” চাপুন।
5. প্রয়োজনীয় PDF/Office/image/ZIP file নির্বাচন করে “ফাইল আপলোড করুন” চাপুন।
6. “পাবলিক পেজ দেখুন” লিংক দিয়ে ফলাফল যাচাই করুন।

## Hosting permission

- `storage/` writable
- `uploads/` writable
- `uploads/service-pages/` writable
- PHP 8.0 বা নতুন সংস্করণ
- JSON এবং file upload enabled

`hosting-check.php` চালিয়ে সব check `PASS` নিশ্চিত করুন।
