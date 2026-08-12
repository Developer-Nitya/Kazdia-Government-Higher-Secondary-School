# Logo and Banner Image Update Notes

এই আপডেটে যা করা হয়েছে:

- ব্যবহারকারীর দেয়া নতুন লোগো `assets/img/logo.jpg` ফাইলে যোগ করা হয়েছে।
- হোমপেজের পরিচিতি ব্যানারের ৩টি ব্যাকগ্রাউন্ড ছবি আপডেট করা হয়েছে:
  - `assets/img/bg-1.jpg`
  - `assets/img/bg-2.jpg`
  - `assets/img/bg-3.jpg`
- `storage/site-content.json`-এ dynamic site settings আপডেট করা হয়েছে।
- `assets/css/styles.css`-এ static fallback banner background paths আপডেট করা হয়েছে।
- static fallback logo references-ও আপডেট করা হয়েছে যাতে JS/API লোড না হলেও লোগো দেখা যায়।
- বিদ্যমান structure অপরিবর্তিত রাখা হয়েছে।

- ব্যানারটি ১৮ সেকেন্ডের infinite cross-fade cycle ব্যবহার করে; প্রতি ৬ সেকেন্ডে ছবি বদলায় এবং ১ সেকেন্ড overlap fade থাকে।
- প্রথম ব্যানার ও লোগো homepage থেকে preload করা হয়েছে যাতে প্রথম paint স্থিতিশীল থাকে।
- Desktop, tablet ও mobile-এর জন্য banner height responsive করা হয়েছে।
