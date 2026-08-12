// =========================================================
// Content page helper.
// Brief information still supports local browser editing.
// Former institution heads are loaded from the backend API.
// =========================================================

(function () {
  const contentPage = document.body.getAttribute("data-content-page");
  const defaults = window.SCHOOL_CONTENT || {};

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function textToParagraphs(value) {
    const lines = String(value || "")
      .split(/\n+/)
      .map((line) => line.trim())
      .filter(Boolean);

    if (!lines.length) {
      return "<p>এখানে তথ্য ইনপুট করুন।</p>";
    }

    return lines.map((line) => `<p>${escapeHtml(line)}</p>`).join("");
  }

  function readStorage(key, fallback) {
    try {
      const saved = localStorage.getItem(key);
      return saved ? JSON.parse(saved) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function writeStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function readPhotoAsDataUrl(file) {
    return new Promise((resolve, reject) => {
      if (!file || file.size === 0) {
        resolve("");
        return;
      }

      if (!file.type.startsWith("image/")) {
        reject(new Error("শুধু ছবি ফাইল আপলোড করুন।"));
        return;
      }

      if (file.size > 1024 * 1024) {
        reject(new Error("ছবির সাইজ ১ এমবি-এর কম রাখুন।"));
        return;
      }

      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = () => reject(new Error("ছবি পড়তে সমস্যা হয়েছে।"));
      reader.readAsDataURL(file);
    });
  }

  function initBriefInfoPage() {
    const storageKey = "kazdia_static_brief_info";
    const fallback = defaults.briefInfo || { title: "সংক্ষিপ্ত বিবরণ", text: "" };
    let data = readStorage(storageKey, fallback);

    const display = document.getElementById("briefDisplay");
    const input = document.getElementById("briefInput");
    const saveButton = document.getElementById("saveBriefInfo");
    const resetButton = document.getElementById("resetBriefInfo");

    function render() {
      if (display) display.innerHTML = textToParagraphs(data.text);
      if (input) input.value = data.text || "";
    }

    if (saveButton && input) {
      saveButton.addEventListener("click", () => {
        data = { title: "সংক্ষিপ্ত বিবরণ", text: input.value.trim() };
        writeStorage(storageKey, data);
        render();
        alert("সংক্ষিপ্ত বিবরণ সংরক্ষণ করা হয়েছে।");
      });
    }

    if (resetButton) {
      resetButton.addEventListener("click", () => {
        if (!confirm("সংরক্ষিত তথ্য মুছে ডিফল্ট তথ্য দেখাবেন?")) return;
        localStorage.removeItem(storageKey);
        data = fallback;
        render();
      });
    }

    render();
  }

  function initFormerHeadsPage() {
    const fallback = Array.isArray(defaults.formerHeads) ? defaults.formerHeads : [];
    let heads = fallback;
    const tableBody = document.getElementById("formerHeadsBody");
    const emptyPhoto = "../assets/img/logo.jpg";
    const apiEndpoint = "../api/former-heads.php";

    function normalizePhoto(photo) {
      if (!photo) return emptyPhoto;
      if (/^(https?:|data:|\/|\.\.\/)/.test(photo)) return photo;
      return `../${photo.replace(/^\/+/, "")}`;
    }

    function render() {
      if (!tableBody) return;

      if (!heads.length) {
        tableBody.innerHTML = '<tr><td colspan="6">এখনো কোনো তথ্য যোগ করা হয়নি। ব্যাকেন্ড থেকে ডাটা এন্ট্রি করলে এখানে তালিকা দেখা যাবে।</td></tr>';
        return;
      }

      tableBody.innerHTML = heads.map((head, index) => {
        const photo = normalizePhoto(head.photo);
        return `
          <tr>
            <td>${escapeHtml(head.serial || String(index + 1))}</td>
            <td><img class="head-photo" src="${photo}" alt="${escapeHtml(head.name || "প্রতিষ্ঠান প্রধান")}"></td>
            <td>${escapeHtml(head.name)}</td>
            <td>${escapeHtml(head.designation)}</td>
            <td>${escapeHtml(head.period)}</td>
            <td>${escapeHtml(head.bio)}</td>
          </tr>
        `;
      }).join("");
    }

    async function loadFromBackend() {
      try {
        const response = await fetch(apiEndpoint, { cache: "no-store" });
        if (!response.ok) throw new Error("API response failed");
        const payload = await response.json();
        heads = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : fallback);
      } catch (error) {
        heads = fallback;
      }

      render();
    }

    loadFromBackend();
  }

  if (contentPage === "brief-info") {
    initBriefInfoPage();
  }

  if (contentPage === "former-heads") {
    initFormerHeadsPage();
  }
})();
