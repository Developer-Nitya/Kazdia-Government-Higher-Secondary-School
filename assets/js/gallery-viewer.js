// =========================================================
// Gallery album slideshow renderer.
// It loads gallery albums from the backend JSON API and shows images one by one.
// =========================================================

(function () {
  "use strict";

  // START: Gallery slideshow utility section
  const script = document.currentScript || document.querySelector('script[src$="gallery-viewer.js"]');
  const siteRoot = script ? new URL("../../", script.src) : new URL("../", window.location.href);
  const apiUrl = new URL("api/site-content.php", siteRoot);
  let currentIndex = 0;
  let images = [];
  let isPlaying = true;
  let timer = null;

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function toBanglaNumber(value) {
    return String(value).replace(/[0-9]/g, (digit) => "০১২৩৪৫৬৭৮৯"[Number(digit)]);
  }

  function toSiteUrl(path) {
    const value = String(path || "").trim();

    if (!value) return "";
    if (/^(https?:|data:|mailto:|tel:|#)/i.test(value)) return value;
    if (value.startsWith("/")) return value;

    return new URL(value.replace(/^\.?\//, ""), siteRoot).href;
  }

  function normalizeImageList(item) {
    const albumImages = Array.isArray(item && item.images) ? item.images : [];
    const result = [];

    albumImages.forEach((image) => {
      const value = typeof image === "string" ? image : (image && (image.image || image.url)) || "";
      if (value && !result.includes(value)) result.push(value);
    });

    if (item && item.image && !result.includes(item.image)) {
      result.unshift(item.image);
    }

    return result;
  }

  function getRequestedGalleryIndex(max) {
    const params = new URLSearchParams(window.location.search);
    const rawValue = params.get("gallery") || "0";
    const index = Number.parseInt(rawValue, 10);

    if (Number.isNaN(index) || index < 0 || index >= max) {
      return 0;
    }

    return index;
  }
  // END: Gallery slideshow utility section

  // START: Gallery slideshow render section
  function renderCurrentImage() {
    const image = document.getElementById("galleryViewerImage");
    const counter = document.getElementById("galleryCounter");
    const thumbnails = document.getElementById("galleryThumbnails");

    if (!image || !images.length) return;

    const currentImage = images[currentIndex] || images[0];
    image.setAttribute("src", toSiteUrl(currentImage));
    image.setAttribute("alt", `গ্যালারি ছবি ${toBanglaNumber(currentIndex + 1)}`);

    if (counter) {
      counter.textContent = `${toBanglaNumber(currentIndex + 1)} / ${toBanglaNumber(images.length)}`;
    }

    if (thumbnails) {
      Array.from(thumbnails.querySelectorAll("button")).forEach((button, index) => {
        button.classList.toggle("active", index === currentIndex);
      });
    }
  }

  function goToImage(index) {
    if (!images.length) return;

    currentIndex = (index + images.length) % images.length;
    renderCurrentImage();
  }

  function stopTimer() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  function startTimer() {
    stopTimer();

    if (!isPlaying || images.length <= 1) return;

    timer = window.setInterval(() => {
      goToImage(currentIndex + 1);
    }, 3500);
  }

  function setPlaying(nextState) {
    isPlaying = nextState;
    const playPause = document.getElementById("galleryPlayPause");
    if (playPause) {
      playPause.textContent = isPlaying ? "Pause" : "Play";
    }

    startTimer();
  }

  function renderThumbnails() {
    const thumbnails = document.getElementById("galleryThumbnails");
    if (!thumbnails) return;

    thumbnails.innerHTML = images.map((image, index) => `
      <button type="button" aria-label="ছবি ${toBanglaNumber(index + 1)} দেখুন">
        <img src="${escapeHtml(toSiteUrl(image))}" alt="">
      </button>
    `).join("");

    Array.from(thumbnails.querySelectorAll("button")).forEach((button, index) => {
      button.addEventListener("click", () => {
        goToImage(index);
        startTimer();
      });
    });
  }

  function bindControls() {
    const previous = document.getElementById("galleryPrev");
    const next = document.getElementById("galleryNext");
    const playPause = document.getElementById("galleryPlayPause");
    const stageImage = document.getElementById("galleryViewerImage");

    if (previous) {
      previous.addEventListener("click", () => {
        goToImage(currentIndex - 1);
        startTimer();
      });
    }

    if (next) {
      next.addEventListener("click", () => {
        goToImage(currentIndex + 1);
        startTimer();
      });
    }

    if (playPause) {
      playPause.addEventListener("click", () => {
        setPlaying(!isPlaying);
      });
    }

    if (stageImage) {
      stageImage.addEventListener("click", () => {
        goToImage(currentIndex + 1);
        startTimer();
      });
    }
  }

  function renderAlbum(album) {
    const title = document.getElementById("galleryViewerTitle");
    const subtitle = document.getElementById("galleryViewerSubtitle");

    images = normalizeImageList(album);
    currentIndex = 0;

    if (!images.length) {
      images = ["assets/img/logo.jpg"];
    }

    if (title) title.textContent = album.title || "গ্যালারি অ্যালবাম";
    if (subtitle) subtitle.textContent = `${toBanglaNumber(images.length)}টি ছবি পর্যায়ক্রমে দেখানো হচ্ছে।`;

    document.title = `${album.title || "গ্যালারি অ্যালবাম"} | কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়`;
    renderThumbnails();
    renderCurrentImage();
    setPlaying(images.length > 1);
  }
  // END: Gallery slideshow render section

  // START: Gallery slideshow bootstrap section
  async function initGalleryViewer() {
    const title = document.getElementById("galleryViewerTitle");

    try {
      const response = await fetch(apiUrl.href, { cache: "no-store" });
      const payload = await response.json();
      const gallery = Array.isArray(payload && payload.data && payload.data.gallery) ? payload.data.gallery : [];
      const selectedIndex = getRequestedGalleryIndex(gallery.length);
      const album = gallery[selectedIndex] || gallery[0] || {
        title: "গ্যালারি অ্যালবাম",
        image: "assets/img/logo.jpg",
        images: ["assets/img/logo.jpg"]
      };

      bindControls();
      renderAlbum(album);
    } catch (error) {
      if (title) title.textContent = "গ্যালারি লোড করা যায়নি";
      bindControls();
      renderAlbum({
        title: "গ্যালারি অ্যালবাম",
        image: "assets/img/logo.jpg",
        images: ["assets/img/logo.jpg"]
      });
    }
  }

  document.addEventListener("DOMContentLoaded", initGalleryViewer);
  // END: Gallery slideshow bootstrap section
})();
