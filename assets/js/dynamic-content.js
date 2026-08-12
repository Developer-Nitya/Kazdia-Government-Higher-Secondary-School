// =========================================================
// Backend-powered dynamic content renderer.
// This file keeps the original HTML layout and replaces only editable text/images.
// =========================================================

(function () {
  "use strict";

  // START: Shared utility section
  const script = document.currentScript || document.querySelector('script[src$="dynamic-content.js"]');
  const siteRoot = script ? new URL("../../", script.src) : new URL("./", window.location.href);

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function toSiteUrl(path) {
    const value = String(path || "").trim();

    if (!value) return "";
    if (/^(https?:|data:|mailto:|tel:|#)/i.test(value)) return value;
    if (value.startsWith("/")) return value;
    if (/^index\.html?$/i.test(value)) return new URL(value, siteRoot).href;
    if (!value.includes("/") && /\.html?$/i.test(value)) return new URL(value, window.location.href).href;

    return new URL(value.replace(/^\.?\//, ""), siteRoot).href;
  }

  // START: Telephone link normalization section
  function toTelephoneHref(value) {
    const bengaliDigits = "০১২৩৪৫৬৭৮৯";
    const normalized = String(value || "")
      .replace(/[০-৯]/g, (digit) => String(bengaliDigits.indexOf(digit)))
      .replace(/(?!^)\+/g, "")
      .replace(/[^\d+]/g, "");

    return normalized.replace(/\D/g, "").length >= 3 ? `tel:${normalized}` : "#";
  }
  // END: Telephone link normalization section

  function setText(selector, value, root = document) {
    const element = root.querySelector(selector);
    if (element && value !== undefined && value !== null) {
      element.textContent = value;
    }
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

  function currentFileName() {
    const cleanPath = window.location.pathname.split("/").pop() || "index.html";
    return cleanPath.replace(/\.html?$/i, "");
  }

  function isHomePage() {
    const file = currentFileName();
    return file === "index" || file === "";
  }

  // START: Dynamic link list renderer section
  function renderLinkList(items, forceNewTab = false) {
    return (Array.isArray(items) ? items : []).map((item) => {
      const title = escapeHtml(item.title || "");
      const url = toSiteUrl(item.url || "#");
      const isExternal = /^(https?:|mailto:|tel:)/i.test(url);
      const attrs = (forceNewTab || isExternal) ? ' target="_blank" rel="noopener noreferrer"' : "";
      return `<li><a href="${escapeHtml(url)}"${attrs}>${title}</a></li>`;
    }).join("");
  }
  // END: Dynamic link list renderer section

  // START: Academic resource link renderer section
  function renderProgramResourceLinks(items, activeSlug = "") {
    const themes = ["emerald", "blue", "amber", "violet"];
    const source = Array.isArray(items) ? items : [];

    if (!source.length) {
      return '<li class="program-resource-empty">এখনো কোনো তথ্য লিংক যোগ করা হয়নি।</li>';
    }

    return source.map((item, index) => {
      const url = toSiteUrl(item.url || "#");
      const fileSlug = String(item.url || "")
        .split("/")
        .pop()
        .replace(/\.html?$/i, "");
      const activeClass = fileSlug === activeSlug ? " is-active" : "";
      const theme = themes[index % themes.length];

      return `
        <li class="program-resource-item theme-${theme}${activeClass}">
          <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer">
            <span class="program-resource-icon" aria-hidden="true">${escapeHtml(item.icon || "🔗")}</span>
            <span class="program-resource-label">${escapeHtml(item.title || "")}</span>
            <span class="program-resource-arrow" aria-hidden="true">↗</span>
          </a>
        </li>
      `;
    }).join("");
  }
  // END: Academic resource link renderer section

  function renderNoticeList(items, limit) {
    const source = Array.isArray(items) ? items.slice(0, limit || items.length) : [];

    if (!source.length) {
      return '<li><a href="#">এখনো কোনো নোটিশ যোগ করা হয়নি।</a><span>তথ্য</span></li>';
    }

    return source.map((item) => {
      const url = toSiteUrl(item.url || "#");
      const isExternal = /^https?:/i.test(url);
      const attrs = isExternal ? ' target="_blank" rel="noopener"' : "";
      return `<li><a href="${escapeHtml(url)}"${attrs}>${escapeHtml(item.title || "")}</a><span>${escapeHtml(item.category || "নোটিশ")}</span></li>`;
    }).join("");
  }

  // START: Dynamic SHED service card renderer section
  function renderServiceGrid(items, limit) {
    const source = Array.isArray(items) ? items.slice(0, limit || items.length) : [];

    return source.map((service, index) => {
      const links = renderLinkList(service.items || [], true);
      const image = toSiteUrl(service.image || "assets/img/service-about.svg");
      const summary = service.text ? `<p class="service-summary">${escapeHtml(service.text)}</p>` : "";
      const extraClass = index >= 6 ? " shed-service-extra is-hidden" : "";

      return `
        <article class="service-item shed-service-card${extraClass}" data-service-card="${index + 1}">
          <span class="service-icon-wrap" aria-hidden="true"><img src="${escapeHtml(image)}" alt=""></span>
          <div class="service-content">
            <h3>${escapeHtml(service.title || "")}</h3>
            ${summary}
            <ul class="shed-service-links">${links}</ul>
          </div>
        </article>
      `;
    }).join("");
  }
  // END: Dynamic SHED service card renderer section

  // START: SHED service expand/collapse controller section
  function initShedServiceToggle(root = document) {
    const grids = Array.from(root.querySelectorAll(".shed-service-grid"));

    grids.forEach((grid) => {
      const cards = Array.from(grid.querySelectorAll(".shed-service-card"));
      const section = grid.closest("section") || grid.parentElement || document;
      const controls = section.querySelector(".shed-service-toggle-wrap");
      const showButton = controls ? controls.querySelector("[data-service-show]") : null;
      const hideButton = controls ? controls.querySelector("[data-service-hide]") : null;

      if (!cards.length || cards.length <= 6) {
        if (controls) controls.hidden = true;
        return;
      }

      grid.setAttribute("data-service-collapsible", "true");
      if (!grid.hasAttribute("data-service-expanded")) {
        grid.setAttribute("data-service-expanded", "false");
      }

      cards.forEach((card, index) => {
        if (index >= 6) {
          card.classList.add("shed-service-extra");
        } else {
          card.classList.remove("shed-service-extra", "is-hidden");
        }
      });

      function applyServiceVisibility(expanded) {
        const currentCards = Array.from(grid.querySelectorAll(".shed-service-card"));

        grid.setAttribute("data-service-expanded", expanded ? "true" : "false");

        currentCards.forEach((card, index) => {
          if (index >= 6) {
            card.classList.add("shed-service-extra");
            card.classList.toggle("is-hidden", !expanded);
          } else {
            card.classList.remove("shed-service-extra", "is-hidden");
          }
        });

        if (showButton) {
          showButton.hidden = expanded;
          showButton.setAttribute("aria-expanded", expanded ? "true" : "false");
        }

        if (hideButton) {
          hideButton.hidden = !expanded;
          hideButton.setAttribute("aria-expanded", expanded ? "true" : "false");
        }
      }

      const initialExpanded = grid.getAttribute("data-service-expanded") === "true";
      applyServiceVisibility(initialExpanded);

      if (showButton) {
        showButton.onclick = () => {
          applyServiceVisibility(true);
        };
      }

      if (hideButton) {
        hideButton.onclick = () => {
          applyServiceVisibility(false);
          grid.scrollIntoView({ behavior: "smooth", block: "start" });
        };
      }
    });
  }
  // END: SHED service expand/collapse controller section

  function normalizeGalleryImages(item) {
    const albumImages = Array.isArray(item.images) ? item.images : [];
    const imageList = albumImages
      .map((image) => (typeof image === "string" ? image : (image && (image.image || image.url)) || ""))
      .filter(Boolean);

    if (item.image && !imageList.includes(item.image)) {
      imageList.unshift(item.image);
    }

    return imageList;
  }

  function renderGalleryGrid(items, limit) {
    const allItems = Array.isArray(items) ? items : [];
    const source = allItems.slice(0, limit || allItems.length);

    // START: Gallery album card renderer section
    return source.map((item, visibleIndex) => {
      const originalIndex = allItems.indexOf(item);
      const galleryIndex = originalIndex >= 0 ? originalIndex : visibleIndex;
      const title = escapeHtml(item.title || "গ্যালারি ছবি");
      const images = normalizeGalleryImages(item);
      const coverImage = escapeHtml(toSiteUrl(item.image || images[0] || "assets/img/logo.jpg"));
      const viewerUrl = escapeHtml(toSiteUrl(`pages/gallery-viewer.html?gallery=${encodeURIComponent(String(galleryIndex))}`));

      return `
        <figure class="gallery-item">
          <a class="gallery-box-link" href="${viewerUrl}" target="_blank" rel="noopener" title="${title} অ্যালবাম দেখুন">
            <img src="${coverImage}" alt="${title}">
            <figcaption>${title}</figcaption>
          </a>
        </figure>
      `;
    }).join("");
    // END: Gallery album card renderer section
  }
  // END: Shared utility section

  // START: Site identity rendering section
  function applySiteSettings(data) {
    const settings = data.siteSettings || {};

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

    if (window.KAZDIA_HEADER_MEDIA) {
      window.KAZDIA_HEADER_MEDIA.applySettings(settings);
    }

  }
  // END: Site identity rendering section

  // START: Professional footer utility section
  function toBengaliDigits(value) {
    const digits = ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"];
    return String(value).replace(/\d/g, (digit) => digits[Number(digit)]);
  }

  function footerIconSvg(iconName) {
    const icons = {
      facebook: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13.5 8.5H16V5h-2.5C10.7 5 9 6.7 9 9.4V12H6v3.5h3V23h4v-7.5h3l.5-3.5H13V9.8c0-.8.3-1.3.5-1.3Z"></path></svg>',
      youtube: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 12s0-3.1-.4-4.6c-.2-.8-.8-1.4-1.6-1.6-1.5-.4-8-.4-8-.4s-6.5 0-8 .4c-.8.2-1.4.8-1.6 1.6C2 8.9 2 12 2 12s0 3.1.4 4.6c.2.8.8 1.4 1.6 1.6 1.5.4 8 .4 8 .4s6.5 0 8-.4c.8-.2 1.4-.8 1.6-1.6.4-1.5.4-4.6.4-4.6ZM10 15.2V8.8l5.5 3.2-5.5 3.2Z"></path></svg>',
      website: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-3.1a15.7 15.7 0 0 0-1.5-3.6A8.1 8.1 0 0 1 18.9 8ZM12 4c.8 1 1.5 2.3 1.9 4h-3.8c.4-1.7 1.1-3 1.9-4ZM4.3 14a8 8 0 0 1 0-4h3.4a17 17 0 0 0 0 4H4.3Zm.8 2h3.1a15.7 15.7 0 0 0 1.5 3.6A8.1 8.1 0 0 1 5.1 16ZM8.2 8H5.1a8.1 8.1 0 0 1 4.6-3.6A15.7 15.7 0 0 0 8.2 8Zm3.8 12c-.8-1-1.5-2.3-1.9-4h3.8c-.4 1.7-1.1 3-1.9 4Zm2.3-6H9.7a15 15 0 0 1 0-4h4.6a15 15 0 0 1 0 4Zm0 5.6a15.7 15.7 0 0 0 1.5-3.6h3.1a8.1 8.1 0 0 1-4.6 3.6ZM16.3 14a17 17 0 0 0 0-4h3.4a8 8 0 0 1 0 4h-3.4Z"></path></svg>'
    };

    return icons[iconName] || icons.website;
  }

  function footerExternalAttributes(url) {
    return /^https?:/i.test(url)
      ? ' target="_blank" rel="noopener noreferrer"'
      : "";
  }

  function renderFooterLinks(items) {
    return (Array.isArray(items) ? items : [])
      .filter((item) => String(item.title || "").trim() && String(item.url || "").trim() && String(item.url || "").trim() !== "#")
      .map((item) => {
        const url = toSiteUrl(item.url);
        return `<li><a href="${escapeHtml(url)}"${footerExternalAttributes(url)}><span aria-hidden="true">›</span>${escapeHtml(item.title)}</a></li>`;
      })
      .join("");
  }

  function renderFooterOfficialLinks(items) {
    return (Array.isArray(items) ? items : [])
      .filter((item) => String(item.title || "").trim() && String(item.url || "").trim() && String(item.url || "").trim() !== "#")
      .map((item) => {
        const url = toSiteUrl(item.url);
        return `<li><a href="${escapeHtml(url)}"${footerExternalAttributes(url)}><span aria-hidden="true">↗</span>${escapeHtml(item.title)}</a></li>`;
      })
      .join("");
  }

  function renderFooterSocialLinks(items) {
    const links = (Array.isArray(items) ? items : [])
      .filter((item) => String(item.title || "").trim() && String(item.url || "").trim() && String(item.url || "").trim() !== "#")
      .map((item) => {
        const url = toSiteUrl(item.url);
        const icon = String(item.icon || "website").toLowerCase();
        return `<a class="footer-social-link" href="${escapeHtml(url)}"${footerExternalAttributes(url)} aria-label="${escapeHtml(item.title)}">${footerIconSvg(icon)}<span>${escapeHtml(item.title)}</span></a>`;
      })
      .join("");

    return links || '<p class="footer-empty-state">অফিসিয়াল Facebook ও YouTube লিংক এডমিন প্যানেল থেকে যুক্ত করুন।</p>';
  }

  function formatFooterLastUpdated(value) {
    const configured = String(value || "").trim();

    if (configured) return configured;

    const modified = new Date(document.lastModified);
    if (Number.isNaN(modified.getTime())) return "হালনাগাদের তথ্য পাওয়া যায়নি";

    try {
      return new Intl.DateTimeFormat("bn-BD", {
        day: "numeric",
        month: "long",
        year: "numeric"
      }).format(modified);
    } catch {
      return toBengaliDigits(modified.toLocaleDateString());
    }
  }

  function setOptionalFooterItem(footer, key, value) {
    const item = footer.querySelector(`[data-footer-item="${key}"]`);
    if (item) item.hidden = !String(value || "").trim();
  }
  // END: Professional footer utility section

  // START: Professional footer rendering section
  function renderProfessionalFooter(data) {
    const settings = data.siteSettings || {};
    const footerSettings = data.footer || {};

    document.querySelectorAll(".site-footer").forEach((footer) => {
      footer.querySelectorAll('[data-footer-field="institutionName"]').forEach((element) => {
        element.textContent = settings.institutionName || "";
      });

      footer.querySelectorAll('[data-footer-field="slogan"]').forEach((element) => {
        element.textContent = settings.slogan || "";
      });

      footer.querySelectorAll('[data-footer-field="established"]').forEach((element) => {
        element.textContent = settings.established || "";
      });

      footer.querySelectorAll('[data-footer-field="eiin"]').forEach((element) => {
        element.textContent = settings.eiin || "";
      });

      const description = footer.querySelector("[data-footer-description]");
      if (description) {
        description.textContent = footerSettings.description || settings.slogan || "";
      }

      const homeLink = footer.querySelector("[data-footer-home-link]");
      if (homeLink) {
        homeLink.setAttribute("href", toSiteUrl(footerSettings.homeUrl || "index.html"));
      }

      const address = footer.querySelector("[data-footer-address]");
      if (address) address.textContent = settings.address || "";

      const phone = footer.querySelector("[data-footer-phone]");
      if (phone) {
        phone.textContent = settings.phone || "";
        phone.setAttribute("href", `tel:${String(settings.phone || "").replace(/\s+/g, "")}`);
      }

      const email = footer.querySelector("[data-footer-email]");
      if (email) {
        email.textContent = settings.email || "";
        email.setAttribute("href", `mailto:${settings.email || ""}`);
      }

      const officeHours = footer.querySelector("[data-footer-office-hours]");
      if (officeHours) officeHours.textContent = settings.officeHours || "";

      setOptionalFooterItem(footer, "address", settings.address);
      setOptionalFooterItem(footer, "phone", settings.phone);
      setOptionalFooterItem(footer, "email", settings.email);
      setOptionalFooterItem(footer, "officeHours", settings.officeHours);
      setOptionalFooterItem(footer, "established", settings.established);
      setOptionalFooterItem(footer, "eiin", settings.eiin);

      const mapLink = footer.querySelector("[data-footer-map-link]");
      if (mapLink) {
        const mapUrl = String(footerSettings.mapUrl || "").trim();
        mapLink.hidden = !mapUrl;
        if (mapUrl) mapLink.setAttribute("href", toSiteUrl(mapUrl));
      }

      const quickLinks = footer.querySelector("[data-footer-quick-links]");
      if (quickLinks) {
        const renderedLinks = renderFooterLinks(footerSettings.quickLinks || []);
        quickLinks.innerHTML = renderedLinks;
        const navigationColumn = quickLinks.closest(".footer-navigation");
        if (navigationColumn) navigationColumn.hidden = !renderedLinks;
      }

      const officialLinks = footer.querySelector("[data-footer-official-links]");
      if (officialLinks) {
        const renderedLinks = renderFooterOfficialLinks(footerSettings.officialLinks || []);
        officialLinks.innerHTML = renderedLinks;
        const officialHeading = officialLinks.previousElementSibling;
        if (officialHeading) officialHeading.hidden = !renderedLinks;
      }

      const socialLinks = footer.querySelector("[data-footer-social-links]");
      if (socialLinks) {
        socialLinks.innerHTML = renderFooterSocialLinks(footerSettings.socialLinks || []);
      }

      const privacyLink = footer.querySelector("[data-footer-privacy-link]");
      if (privacyLink) {
        const privacyUrl = String(footerSettings.privacyUrl || "").trim();
        privacyLink.hidden = !privacyUrl;
        if (privacyUrl) privacyLink.setAttribute("href", toSiteUrl(privacyUrl));
      }

      const termsLink = footer.querySelector("[data-footer-terms-link]");
      if (termsLink) {
        const termsUrl = String(footerSettings.termsUrl || "").trim();
        termsLink.hidden = !termsUrl;
        if (termsUrl) termsLink.setAttribute("href", toSiteUrl(termsUrl));
      }

      const year = footer.querySelector("[data-footer-year]");
      if (year) year.textContent = toBengaliDigits(new Date().getFullYear());

      const lastUpdated = footer.querySelector("[data-footer-last-updated]");
      if (lastUpdated) {
        lastUpdated.textContent = formatFooterLastUpdated(footerSettings.lastUpdated);
        lastUpdated.setAttribute("datetime", new Date().toISOString().slice(0, 10));
      }

      const developerLabel = footer.querySelector("[data-footer-developer-label]");
      if (developerLabel) {
        developerLabel.textContent = footerSettings.developerLabel || "কারিগরি ব্যবস্থাপনা:";
      }

      const developerName = footer.querySelector("[data-footer-developer-name]");
      if (developerName) {
        developerName.textContent = footerSettings.developerName || "ওয়েবসাইট প্রশাসন";
      }
    });
  }
  // END: Professional footer rendering section


  // START: Homepage rendering section
  function renderHomePage(data) {
    const home = data.home || {};
    const settings = data.siteSettings || {};

    if (!isHomePage()) return;

    const slides = Array.from(document.querySelectorAll("#heroSlider .slide"));
    (home.slides || []).slice(0, slides.length).forEach((slide, index) => {
      const node = slides[index];
      const image = node.querySelector("img");

      if (image && slide.image) image.setAttribute("src", toSiteUrl(slide.image));
      if (image && slide.alt) image.setAttribute("alt", slide.alt);
      setText(".slide-caption h2", slide.title, node);
      setText(".slide-caption p", slide.text, node);
    });

    const noticeList = document.querySelector("#notice-board .notice-list");
    if (noticeList) noticeList.innerHTML = renderNoticeList(data.notices || [], 4);

    const typeGrid = document.querySelector("#education-types .type-grid");
    if (typeGrid && Array.isArray(home.educationCards)) {
      typeGrid.innerHTML = home.educationCards.map((card) => `
        <a class="type-card" href="${escapeHtml(toSiteUrl(card.url || "#"))}" target="_blank" rel="noopener noreferrer">
          <span class="type-icon">${escapeHtml(card.icon || "📘")}</span>
          <h3>${escapeHtml(card.title || "")}</h3>
          <p>${escapeHtml(card.text || "")}</p>
          <strong>বিস্তারিত দেখুন →</strong>
        </a>
      `).join("");
    }

    const serviceGrid = document.querySelector("#services .service-grid");
    if (serviceGrid) {
      serviceGrid.innerHTML = renderServiceGrid(data.services || []);
      serviceGrid.setAttribute("data-service-expanded", "false");
      initShedServiceToggle(document);
    }

    const galleryGrid = document.querySelector("#gallery .gallery-grid");
    if (galleryGrid) galleryGrid.innerHTML = renderGalleryGrid(data.gallery || [], 6);

    const profileCards = Array.from(document.querySelectorAll(".sidebar .profile-card")).slice(0, 3);
    (home.profiles || []).slice(0, profileCards.length).forEach((profile, index) => {
      const card = profileCards[index];
      const image = card.querySelector("img");
      const link = card.querySelector("a");

      setText("h2", profile.role, card);
      setText("h3", profile.name, card);
      setText("p", profile.text, card);

      if (image && profile.image) image.setAttribute("src", toSiteUrl(profile.image));
      if (image) image.setAttribute("alt", `${profile.role || profile.name || "প্রোফাইল"} ছবি`);
      if (link && profile.url) link.setAttribute("href", toSiteUrl(profile.url));
    });

    const linkCards = document.querySelectorAll(".sidebar .link-card");
    if (linkCards[0] && home.importantLinks) {
      const list = linkCards[0].querySelector("ul");
      if (list) list.innerHTML = renderLinkList(home.importantLinks);
    }

    if (linkCards[1] && home.officialLinks) {
      const list = linkCards[1].querySelector("ul");
      if (list) list.innerHTML = renderLinkList(home.officialLinks);
    }

    // START: Focal point and hotline dynamic rendering section
    const focalPoint = home.focalPoint || {};
    const focalPointCard = document.querySelector("[data-focal-point]");

    if (focalPointCard) {
      setText("[data-focal-title]", focalPoint.title || "ফোকাল পয়েন্ট", focalPointCard);
      setText("[data-focal-name]", focalPoint.name || "ফোকাল পয়েন্ট কর্মকর্তার নাম", focalPointCard);
      setText("[data-focal-designation]", focalPoint.designation || "পদবী লিখুন", focalPointCard);
      setText("[data-focal-phone]", focalPoint.phone || "০১XXXXXXXXX", focalPointCard);

      const focalImage = focalPointCard.querySelector("[data-focal-image]");
      if (focalImage && focalPoint.image) {
        focalImage.setAttribute("src", toSiteUrl(focalPoint.image));
      }
      if (focalImage) {
        focalImage.setAttribute("alt", `${focalPoint.name || "ফোকাল পয়েন্ট কর্মকর্তা"}-এর ছবি`);
      }

      const focalPhoneLink = focalPointCard.querySelector("[data-focal-phone-link]");
      if (focalPhoneLink) {
        focalPhoneLink.setAttribute("href", toTelephoneHref(focalPoint.phone));
        focalPhoneLink.setAttribute("aria-label", `${focalPoint.name || "ফোকাল পয়েন্ট কর্মকর্তা"}-এর মোবাইল নম্বর`);
      }
    }

    const hotline = home.hotline || {};
    const hotlineCard = document.querySelector("[data-hotline]");

    if (hotlineCard) {
      setText("[data-hotline-title]", hotline.title || "হটলাইন", hotlineCard);
      setText("[data-hotline-label]", hotline.label || "জরুরি যোগাযোগ নম্বর", hotlineCard);
      setText("[data-hotline-phone]", hotline.phone || "০১XXXXXXXXX", hotlineCard);

      const hotlinePhoneLink = hotlineCard.querySelector("[data-hotline-phone-link]");
      if (hotlinePhoneLink) {
        hotlinePhoneLink.setAttribute("href", toTelephoneHref(hotline.phone));
        hotlinePhoneLink.setAttribute("aria-label", `${hotline.title || "হটলাইন"} নম্বরে কল করুন`);
      }
    }
    // END: Focal point and hotline dynamic rendering section

    document.title = settings.institutionName || document.title;
  }
  // END: Homepage rendering section

  // START: Static content pages rendering section
  function renderBriefHistoryPage(data) {
    if (currentFileName() !== "brief-history") return;

    const page = (data.pages || {}).briefHistory || {};
    setText(".content-page-card .section-title h2", page.title);
    setText(".content-page-card .section-title p", page.subtitle);

    const display = document.getElementById("briefDisplay");
    if (display) display.innerHTML = textToParagraphs(page.text);

    const input = document.getElementById("briefInput");
    if (input) input.value = page.text || "";
  }

  function renderContactPage(data) {
    if (currentFileName() !== "contact") return;

    const settings = data.siteSettings || {};
    const page = (data.pages || {}).contact || {};
    const cards = document.querySelectorAll(".contact-info-card");

    setText(".content-page-card .section-title h2", page.title || "যোগাযোগ");
    setText(".content-page-card .section-title p", page.subtitle || "");

    if (cards[0]) {
      cards[0].innerHTML = `
        <h3>${escapeHtml(settings.institutionName || "")}</h3>
        <p><strong>ঠিকানা:</strong> ${escapeHtml(settings.address || "")}</p>
        <p><strong>ইমেইল:</strong> <a href="mailto:${escapeHtml(settings.email || "")}" data-site-field="email" data-site-mail>${escapeHtml(settings.email || "")}</a></p>
        <p><strong>ফোন:</strong> ${escapeHtml(settings.phone || "")}</p>
        <p><strong>অফিস সময়:</strong> ${escapeHtml(settings.officeHours || "")}</p>
      `;
    }

    if (cards[1]) {
      cards[1].innerHTML = `
        <h3>${escapeHtml(page.quickTitle || "দ্রুত বার্তা")}</h3>
        <p>${escapeHtml(page.quickText || "")}</p>
        <a class="contact-mail-button" href="mailto:${escapeHtml(settings.email || "")}" data-site-field="email" data-site-mail>${escapeHtml(page.buttonText || "ইমেইল পাঠান")}</a>
      `;
    }
  }

  function renderNoticePage(data) {
    if (currentFileName() !== "notice") return;

    const list = document.querySelector(".notice-page-list");
    if (list) list.innerHTML = renderNoticeList(data.notices || []);
  }

  function renderServicesPage(data) {
    if (currentFileName() !== "services") return;

    const grid = document.querySelector(".service-page-grid");
    if (grid) {
      grid.innerHTML = renderServiceGrid(data.services || []);
      grid.setAttribute("data-service-expanded", "false");
      initShedServiceToggle(document);
    }
  }

  function renderGalleryPage(data) {
    if (currentFileName() !== "gallery") return;

    const grid = document.querySelector(".gallery-page-grid");
    if (grid) grid.innerHTML = renderGalleryGrid(data.gallery || []);
  }
  // END: Static content pages rendering section

  // START: Academic program pages rendering section
  function renderProgramPage(data) {
    const slug = currentFileName();
    const program = ((data.programs || {})[slug]);

    if (!program) return;

    setText(".detail-hero h1", program.title);
    setText(".detail-hero p", program.subtitle);

    const tableBody = document.querySelector(".info-table tbody");
    if (tableBody && Array.isArray(program.infoRows)) {
      tableBody.innerHTML = program.infoRows.map((row) => `
        <tr>
          <th>${escapeHtml(row[0] || "")}</th>
          <td>${escapeHtml(row[1] || "")}</td>
        </tr>
      `).join("");
    }

    const noticeList = document.querySelector(".content-column .notice-list");
    if (noticeList) noticeList.innerHTML = renderNoticeList(program.notices || []);

    const card = document.querySelector(".sidebar .profile-card");
    const officer = program.contactOfficer || {};
    if (card) {
      const image = card.querySelector("img");
      const link = card.querySelector("a");

      setText("h2", officer.role || "যোগাযোগ কর্মকর্তা", card);
      setText("h3", officer.name || "", card);
      setText("p", officer.text || "", card);

      if (image && officer.image) image.setAttribute("src", toSiteUrl(officer.image));
      if (image) image.setAttribute("alt", `${officer.role || "যোগাযোগ কর্মকর্তা"} ছবি`);
      if (link && officer.url) link.setAttribute("href", toSiteUrl(officer.url));
    }

    const resourceTitle = document.querySelector("[data-program-resource-title]");
    if (resourceTitle) {
      resourceTitle.textContent = `${program.title || "শিক্ষাস্তর"} সম্পর্কিত তথ্য`;
    }

    const resourceList = document.querySelector("[data-program-resource-links]");
    if (resourceList) {
      resourceList.innerHTML = renderProgramResourceLinks(program.resourceLinks || []);
    }
  }
  // END: Academic program pages rendering section

  // START: Academic resource detail pages rendering section
  function renderProgramResourcePage(data) {
    const slug = currentFileName();
    const resource = ((data.programResources || {})[slug]);

    if (!resource) return;

    const program = ((data.programs || {})[resource.program]) || {};
    const settings = data.siteSettings || {};

    setText("[data-resource-page-title]", resource.title);
    setText("[data-resource-page-subtitle]", resource.subtitle);
    setText("[data-resource-page-description]", resource.description);
    setText("[data-resource-page-note]", resource.note);
    setText("[data-resource-page-icon]", resource.icon || "📄");

    document.querySelectorAll("[data-resource-parent-name]").forEach((element) => {
      element.textContent = program.title || "শিক্ষাস্তরের বিস্তারিত";
    });

    const parentLinks = document.querySelectorAll("[data-resource-parent-link]");
    parentLinks.forEach((link) => {
      link.setAttribute("href", toSiteUrl(program.page || "index.html"));
    });

    const homeLinks = document.querySelectorAll("[data-resource-home-link]");
    homeLinks.forEach((link) => {
      link.setAttribute("href", new URL("index.html", siteRoot).href);
    });

    const table = document.querySelector("[data-resource-table]");
    const tableHead = table ? table.querySelector("thead") : null;
    const tableBody = table ? table.querySelector("tbody") : null;
    const columns = Array.isArray(resource.columns) ? resource.columns : [];
    const rows = Array.isArray(resource.rows) ? resource.rows : [];

    if (tableHead) {
      tableHead.innerHTML = `<tr>${columns.map((column) => `<th scope="col">${escapeHtml(column)}</th>`).join("")}</tr>`;
    }

    if (tableBody) {
      if (rows.length) {
        tableBody.innerHTML = rows.map((row) => `
          <tr>${columns.map((column, index) => `<td data-label="${escapeHtml(column)}">${escapeHtml((row || [])[index] || "")}</td>`).join("")}</tr>
        `).join("");
      } else {
        tableBody.innerHTML = `<tr><td colspan="${Math.max(columns.length, 1)}">এখনো কোনো তথ্য যোগ করা হয়নি।</td></tr>`;
      }
    }

    const resourceTitle = document.querySelector("[data-program-resource-title]");
    if (resourceTitle) {
      resourceTitle.textContent = `${program.title || "শিক্ষাস্তর"} সম্পর্কিত তথ্য`;
    }

    const resourceList = document.querySelector("[data-program-resource-links]");
    if (resourceList) {
      resourceList.innerHTML = renderProgramResourceLinks(program.resourceLinks || [], slug);
    }

    if (resource.title) {
      document.title = `${resource.title} | ${settings.institutionName || "শিক্ষা প্রতিষ্ঠান"}`;
    }
  }
  // END: Academic resource detail pages rendering section

  // START: Static fallback service toggle initialization section
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => initShedServiceToggle(document));
  } else {
    initShedServiceToggle(document);
  }
  // END: Static fallback service toggle initialization section

  // START: Backend data loading section
  function applyAll(data) {
    applySiteSettings(data);
    renderProfessionalFooter(data);
    renderHomePage(data);
    renderBriefHistoryPage(data);
    renderContactPage(data);
    renderNoticePage(data);
    renderServicesPage(data);
    renderGalleryPage(data);
    renderProgramPage(data);
    renderProgramResourcePage(data);
  }

  fetch(new URL("api/site-content.php", siteRoot), { cache: "no-store" })
    .then((response) => {
      if (!response.ok) throw new Error("Site content API failed");
      return response.json();
    })
    .then((payload) => applyAll(payload.data || payload))
    .catch(() => {
      if (window.SITE_SETTINGS) {
        const fallbackData = {
          siteSettings: window.SITE_SETTINGS,
          footer: window.SITE_FOOTER || {}
        };
        applySiteSettings(fallbackData);
        renderProfessionalFooter(fallbackData);
      }
    });
  // END: Backend data loading section
})();
