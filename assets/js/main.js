// =========================================================
// Editable homepage JavaScript
// Keep this file simple so non-technical editors can update it.
// =========================================================

// START: Central editable content apply section
const mainScriptElement = document.currentScript
  || Array.from(document.scripts).find((script) => {
    try {
      return new URL(script.src, window.location.href).pathname.endsWith("/assets/js/main.js");
    } catch {
      return false;
    }
  });

const siteRootUrl = mainScriptElement
  ? new URL("../../", mainScriptElement.src)
  : new URL("./", window.location.href);

function getEditableSettings() {
  return window.SITE_SETTINGS || {};
}

function resolveHeaderImageUrl(path) {
  const value = String(path || "").trim();

  if (!value) return "";
  if (/^(https?:|data:|blob:)/i.test(value)) return value;
  if (value.startsWith("/")) return value;

  if (!value.includes("/")) {
    return new URL(`assets/img/${value}`, siteRootUrl).href;
  }

  return new URL(value.replace(/^\.?\//, ""), siteRootUrl).href;
}

function canLoadImage(url) {
  return new Promise((resolve) => {
    if (!url) {
      resolve(false);
      return;
    }

    const probe = new Image();
    let settled = false;

    const finish = (loaded) => {
      if (settled) return;
      settled = true;
      resolve(loaded);
    };

    probe.onload = () => finish(true);
    probe.onerror = () => finish(false);
    probe.src = url;

    if (probe.complete) {
      finish(probe.naturalWidth > 0);
    }
  });
}

async function applyHeaderLogo(settings) {
  const images = Array.from(document.querySelectorAll("[data-site-logo]"));
  const logoUrl = resolveHeaderImageUrl(settings.logo);
  const logoIsAvailable = logoUrl ? await canLoadImage(logoUrl) : false;

  images.forEach((image) => {
    const fallbackUrl = image.dataset.fallbackSrc || image.src;
    image.dataset.fallbackSrc = fallbackUrl;

    if (settings.institutionName) {
      image.setAttribute("alt", `${settings.institutionName} লোগো`);
    }

    if (logoIsAvailable) {
      image.src = logoUrl;
    }

    image.onerror = () => {
      if (fallbackUrl && image.src !== fallbackUrl) {
        image.src = fallbackUrl;
      }
    };
  });
}

async function applyHeaderBackgrounds(settings) {
  const bannerImages = Array.isArray(settings.bannerImages)
    ? settings.bannerImages
    : [];
  const backgrounds = Array.from(document.querySelectorAll("[data-banner-bg]"));
  const candidates = backgrounds.map((background) => {
    const index = Number(background.getAttribute("data-banner-bg"));
    return resolveHeaderImageUrl(bannerImages[index]);
  });
  const availability = await Promise.all(
    candidates.map((imageUrl) => imageUrl ? canLoadImage(imageUrl) : false)
  );

  backgrounds.forEach((background, index) => {
    if (availability[index]) {
      background.style.backgroundImage = `url("${candidates[index]}")`;
    }
  });
}

function applyHeaderMediaSettings(settings) {
  applyHeaderLogo(settings);
  applyHeaderBackgrounds(settings);
}

function applyEditableSiteSettings() {
  const settings = getEditableSettings();

  const fields = {
    institutionName: settings.institutionName,
    slogan: settings.slogan,
    eiin: settings.eiin,
    established: settings.established,
    email: settings.email
  };

  document.querySelectorAll("[data-site-field]").forEach((element) => {
    const key = element.getAttribute("data-site-field");

    if (!fields[key]) return;

    element.textContent = fields[key];

    if (key === "email" && element.hasAttribute("data-site-mail")) {
      element.setAttribute("href", `mailto:${fields[key]}`);
    }
  });

  applyHeaderMediaSettings(settings);
}

function setupIdentityBackgroundSlider() {
  document.querySelectorAll(".school-identity-banner").forEach((banner) => {
    const backgrounds = Array.from(banner.querySelectorAll("[data-banner-bg]"));

    if (!backgrounds.length || banner.dataset.sliderInitialized === "true") {
      return;
    }

    banner.dataset.sliderInitialized = "true";
    banner.classList.add("is-slider-ready");

    let activeIndex = 0;

    const showBackground = (index) => {
      activeIndex = (index + backgrounds.length) % backgrounds.length;

      backgrounds.forEach((background, backgroundIndex) => {
        background.classList.toggle("is-active", backgroundIndex === activeIndex);
      });
    };

    showBackground(0);

    if (backgrounds.length > 1) {
      window.setInterval(() => {
        showBackground(activeIndex + 1);
      }, 6000);
    }
  });
}

window.KAZDIA_HEADER_MEDIA = {
  applySettings: applyHeaderMediaSettings
};

applyEditableSiteSettings();
setupIdentityBackgroundSlider();
// END: Central editable content apply section

// START: Mobile menu section
const menuToggle = document.getElementById("menuToggle");
const mainMenu = document.getElementById("mainMenu");

if (menuToggle && mainMenu) {
  menuToggle.addEventListener("click", () => {
    const isOpen = mainMenu.classList.toggle("open");
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });
}
// END: Mobile menu section


// START: Navigation dropdown section
document.querySelectorAll(".has-dropdown").forEach((dropdown) => {
  const toggle = dropdown.querySelector(".dropdown-toggle");
  if (!toggle) return;

  toggle.addEventListener("click", (event) => {
    event.preventDefault();

    document.querySelectorAll(".has-dropdown.dropdown-open").forEach((openDropdown) => {
      if (openDropdown !== dropdown) {
        openDropdown.classList.remove("dropdown-open");
        const openToggle = openDropdown.querySelector(".dropdown-toggle");
        if (openToggle) openToggle.setAttribute("aria-expanded", "false");
      }
    });

    const isOpen = dropdown.classList.toggle("dropdown-open");
    toggle.setAttribute("aria-expanded", String(isOpen));
  });
});

document.addEventListener("click", (event) => {
  if (event.target.closest(".has-dropdown")) return;

  document.querySelectorAll(".has-dropdown.dropdown-open").forEach((dropdown) => {
    dropdown.classList.remove("dropdown-open");
    const toggle = dropdown.querySelector(".dropdown-toggle");
    if (toggle) toggle.setAttribute("aria-expanded", "false");
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") return;

  document.querySelectorAll(".has-dropdown.dropdown-open").forEach((dropdown) => {
    dropdown.classList.remove("dropdown-open");
    const toggle = dropdown.querySelector(".dropdown-toggle");
    if (toggle) toggle.setAttribute("aria-expanded", "false");
  });
});
// END: Navigation dropdown section


// START: Hero slider section
const slides = Array.from(document.querySelectorAll("#heroSlider .slide"));
const prevSlideButton = document.getElementById("prevSlide");
const nextSlideButton = document.getElementById("nextSlide");
const HERO_SLIDE_INTERVAL_MS = 5000;
let activeSlideIndex = 0;

function showSlide(index) {
  if (!slides.length) return;

  slides[activeSlideIndex].classList.remove("active");
  activeSlideIndex = (index + slides.length) % slides.length;
  slides[activeSlideIndex].classList.add("active");
}

if (prevSlideButton && nextSlideButton) {
  prevSlideButton.addEventListener("click", () => showSlide(activeSlideIndex - 1));
  nextSlideButton.addEventListener("click", () => showSlide(activeSlideIndex + 1));

  window.setInterval(() => {
    showSlide(activeSlideIndex + 1);
  }, HERO_SLIDE_INTERVAL_MS);
}
// END: Hero slider section

// START: Font size controls section
const fontIncreaseButton = document.getElementById("fontIncrease");
const fontDecreaseButton = document.getElementById("fontDecrease");
let currentFontSize = 16;

function updateFontSize(size) {
  currentFontSize = Math.min(22, Math.max(14, size));
  document.body.style.fontSize = `${currentFontSize}px`;
}

if (fontIncreaseButton && fontDecreaseButton) {
  fontIncreaseButton.addEventListener("click", () => updateFontSize(currentFontSize + 1));
  fontDecreaseButton.addEventListener("click", () => updateFontSize(currentFontSize - 1));
}
// END: Font size controls section


// START: National anthem media box section
function getCurrentScriptBase() {
  const script = document.querySelector('script[src$="assets/js/main.js"], script[src$="../assets/js/main.js"], script[src*="/assets/js/main.js"]');
  if (!script) return window.location.href;
  return new URL("../../", script.src).href;
}

function resolveSiteUrl(path) {
  if (!path) return "";
  if (/^(https?:|data:|\/)/.test(path)) return path;
  return new URL(path, getCurrentScriptBase()).href;
}

function getMediaTypeFromSource(source, configuredType) {
  const type = (configuredType || "").toLowerCase();
  if (type === "video" || type === "audio") return type;

  const cleanSource = String(source || "").split("?")[0].toLowerCase();
  if (/\.(mp4|webm|ogv|ogg)$/.test(cleanSource)) return "video";
  return "audio";
}

function getMimeType(source, mediaType) {
  const cleanSource = String(source || "").split("?")[0].toLowerCase();
  if (cleanSource.endsWith(".mp4")) return "video/mp4";
  if (cleanSource.endsWith(".webm")) return "video/webm";
  if (cleanSource.endsWith(".ogv") || (mediaType === "video" && cleanSource.endsWith(".ogg"))) return "video/ogg";
  if (cleanSource.endsWith(".oga") || cleanSource.endsWith(".ogg")) return "audio/ogg";
  if (cleanSource.endsWith(".wav")) return "audio/wav";
  return "audio/mpeg";
}

function createAnthemMediaElement(settings) {
  const source = settings.source || "";
  const mediaType = getMediaTypeFromSource(source, settings.type);
  const media = document.createElement(mediaType === "video" ? "video" : "audio");
  const sourceElement = document.createElement("source");

  media.id = "nationalAnthemPlayer";
  media.setAttribute("controls", "");
  media.setAttribute("preload", "metadata");
  media.setAttribute("data-anthem-player", "");

  if (mediaType === "video") {
    media.setAttribute("playsinline", "");
    if (settings.poster) {
      media.setAttribute("poster", resolveSiteUrl(settings.poster));
    }
  }

  sourceElement.src = resolveSiteUrl(source);
  sourceElement.type = getMimeType(source, mediaType);
  media.appendChild(sourceElement);
  media.appendChild(document.createTextNode("আপনার ব্রাউজার মিডিয়া প্লেয়ার সমর্থন করে না।"));

  return media;
}

function setupNationalAnthemBox() {
  const anthemCard = document.querySelector("[data-national-anthem]");
  if (!anthemCard) return;

  const playerWrap = anthemCard.querySelector("[data-anthem-player-wrap]");
  const playButton = anthemCard.querySelector("[data-anthem-play]");
  const titleElement = anthemCard.querySelector("[data-anthem-title]");
  const posterImage = anthemCard.querySelector("[data-anthem-poster] img");

  const defaultSettings = {
    title: "আমার সোনার বাংলা",
    type: "audio",
    source: "https://upload.wikimedia.org/wikipedia/commons/transcoded/b/bc/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg/Amar_Sonar_Bangla_-_official_vocal_music_of_the_National_anthem_of_Bangladesh.ogg.mp3",
    poster: "assets/img/national-anthem-poster.svg"
  };

  function applySettings(settings) {
    const nextSettings = { ...defaultSettings, ...settings };

    if (titleElement && nextSettings.subtitle) {
      titleElement.textContent = nextSettings.subtitle;
    } else if (titleElement && nextSettings.title) {
      titleElement.textContent = nextSettings.title;
    }

    if (posterImage && nextSettings.poster) {
      posterImage.src = resolveSiteUrl(nextSettings.poster);
    }

    if (playerWrap && nextSettings.source) {
      playerWrap.replaceChildren(createAnthemMediaElement(nextSettings));
    }

    const player = anthemCard.querySelector("[data-anthem-player]");
    if (!player) return;

    player.addEventListener("play", () => anthemCard.classList.add("is-playing"));
    player.addEventListener("pause", () => anthemCard.classList.remove("is-playing"));
    player.addEventListener("ended", () => anthemCard.classList.remove("is-playing"));
  }

  fetch(resolveSiteUrl("storage/media-settings.json"), { cache: "no-store" })
    .then((response) => (response.ok ? response.json() : defaultSettings))
    .then((settings) => applySettings(settings))
    .catch(() => applySettings(defaultSettings));

  if (playButton) {
    playButton.addEventListener("click", () => {
      const player = anthemCard.querySelector("[data-anthem-player]");
      if (!player) return;

      if (player.paused) {
        player.play().catch(() => {});
      } else {
        player.pause();
      }
    });
  }
}

setupNationalAnthemBox();
// END: National anthem media box section
