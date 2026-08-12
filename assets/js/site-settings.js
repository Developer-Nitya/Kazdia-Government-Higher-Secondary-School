// =========================================================
// Central editable settings for the static website.
// Change the values here to update shared information on all pages.
// PHP hosting uses storage/site-content.json and automatically overrides these fallbacks.
// =========================================================

// START: Static institution settings fallback section
window.SITE_SETTINGS = {
  institutionName: "কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়",
  slogan: "জ্ঞান,শৃঙ্খলা ও দক্ষতায় গড়ি আলোকিত ভবিষ্যৎ",
  eiin: "117396",
  established: "১৯৫৭",
  email: "kazdiahighersecondaryschool57@gmail.com",
  phone: "০১XXXXXXXXX",
  address: "আপনার প্রতিষ্ঠানের ঠিকানা লিখুন",
  officeHours: "সকাল ১০টা থেকে বিকাল ৪টা",

  // Keep image files inside assets/img/. You may replace these filenames.
  logo: "logo.jpg?v=20260727-1",
  bannerImages: [
    "bg-1.jpg?v=20260725-4",
    "bg-2.jpg?v=20260725-4",
    "bg-3.jpg?v=20260725-4"
  ]
};
// END: Static institution settings fallback section

// START: Static professional footer settings fallback section
window.SITE_FOOTER = {
  homeUrl: "index.html",
  description: "ঐতিহ্য, শৃঙ্খলা, আধুনিক শিক্ষা ও দক্ষতা উন্নয়নের সমন্বয়ে আলোকিত নাগরিক গড়ে তোলাই আমাদের অঙ্গীকার।",
  mapUrl: "https://www.google.com/maps/search/?api=1&query=কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়",
  privacyUrl: "pages/privacy-policy.html",
  termsUrl: "pages/terms-and-conditions.html",
  lastUpdated: "",
  developerLabel: "কারিগরি ব্যবস্থাপনা:",
  developerName: "ওয়েবসাইট প্রশাসন",
  quickLinks: [
    { title: "হোমপেজ", url: "index.html" },
    { title: "আমাদের সম্পর্কে", url: "pages/brief-history.html" },
    { title: "নোটিশ", url: "pages/notice.html" },
    { title: "সেবা সমূহ", url: "pages/services.html" },
    { title: "গ্যালারি", url: "pages/gallery.html" },
    { title: "যোগাযোগ", url: "pages/contact.html" }
  ],
  socialLinks: [
    { title: "Facebook", url: "", icon: "facebook" },
    { title: "YouTube", url: "", icon: "youtube" }
  ],
  officialLinks: [
    { title: "শিক্ষা মন্ত্রণালয়", url: "https://moedu.gov.bd/" },
    { title: "মাধ্যমিক ও উচ্চ শিক্ষা অধিদপ্তর", url: "https://dshe.gov.bd/" },
    { title: "যশোর শিক্ষা বোর্ড", url: "https://www.jessoreboard.gov.bd/" }
  ]
};
// END: Static professional footer settings fallback section
