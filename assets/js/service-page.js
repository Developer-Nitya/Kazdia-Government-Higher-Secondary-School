// =========================================================
// Dynamic renderer for the individual service information pages.
// Content and uploaded documents are loaded from the PHP JSON API.
// =========================================================

(function () {
  "use strict";

  /* START: Service page utility section */
  const pageRoot = document.querySelector("[data-service-page-key]");
  const script = document.currentScript || document.querySelector('script[src*="service-page.js"]');
  const siteRoot = script ? new URL("../../", script.src) : new URL("../", window.location.href);

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function textToParagraphs(value) {
    const paragraphs = String(value || "")
      .split(/\n{2,}/)
      .map((paragraph) => paragraph.trim())
      .filter(Boolean);

    if (!paragraphs.length) {
      return "<p>এখনো কোনো বিস্তারিত তথ্য প্রকাশ করা হয়নি।</p>";
    }

    return paragraphs
      .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, "<br>")}</p>`)
      .join("");
  }

  function toSiteUrl(value) {
    const path = String(value || "").trim();

    if (!path) return "#";
    if (/^(https?:|mailto:|tel:|data:|#)/i.test(path)) return path;
    if (path.startsWith("/")) return path;

    return new URL(path.replace(/^\.?\//, ""), siteRoot).href;
  }

  function fileTypeLabel(fileName) {
    const cleanName = String(fileName || "").split("?")[0];
    const extension = cleanName.includes(".")
      ? cleanName.split(".").pop().toUpperCase()
      : "FILE";

    return extension || "FILE";
  }

  function formatDate(value) {
    const raw = String(value || "").trim();

    if (!raw) return "";

    const date = new Date(raw);

    if (Number.isNaN(date.getTime())) return raw;

    try {
      return new Intl.DateTimeFormat("bn-BD", {
        year: "numeric",
        month: "long",
        day: "numeric"
      }).format(date);
    } catch (error) {
      return raw;
    }
  }
  /* END: Service page utility section */

  /* START: Service document renderer section */
  function renderDocuments(documents) {
    const container = document.querySelector("[data-service-page-documents]");

    if (!container) return;

    const items = Array.isArray(documents) ? documents : [];

    if (!items.length) {
      container.innerHTML = '<p class="service-document-empty">এখনো কোনো ফাইল সংযুক্ত করা হয়নি।</p>';
      return;
    }

    container.innerHTML = items.map((documentItem) => {
      const fileUrl = toSiteUrl(documentItem.file || documentItem.url || "#");
      const fileName = documentItem.originalName || documentItem.fileName || documentItem.title || "ফাইল";
      const description = String(documentItem.description || "").trim();
      const uploadedAt = formatDate(documentItem.uploadedAt);
      const meta = [fileTypeLabel(fileName), uploadedAt].filter(Boolean).join(" • ");

      return `
        <article class="service-document-item">
          <span class="service-document-icon" aria-hidden="true">⇩</span>
          <div class="service-document-copy">
            <h3>${escapeHtml(documentItem.title || fileName)}</h3>
            ${description ? `<p>${escapeHtml(description)}</p>` : ""}
            ${meta ? `<span>${escapeHtml(meta)}</span>` : ""}
          </div>
          <a href="${escapeHtml(fileUrl)}" target="_blank" rel="noopener noreferrer">
            ফাইল দেখুন
            <span aria-hidden="true">↗</span>
          </a>
        </article>
      `;
    }).join("");
  }
  /* END: Service document renderer section */

  /* START: Service page content renderer section */
  function renderServicePage(page) {
    if (!page || typeof page !== "object") return;

    const title = String(page.title || "").trim();
    const sectionTitle = String(page.sectionTitle || "সেবাসমূহ").trim();
    const intro = String(page.intro || "").trim();
    const content = String(page.content || "").trim();

    const titleNode = document.querySelector("[data-service-page-title]");
    const sectionNode = document.querySelector("[data-service-page-section]");
    const introNode = document.querySelector("[data-service-page-intro]");
    const contentNode = document.querySelector("[data-service-page-content]");

    if (titleNode && title) titleNode.textContent = title;
    if (sectionNode && sectionTitle) sectionNode.textContent = sectionTitle;
    if (introNode && intro) introNode.textContent = intro;
    if (contentNode) contentNode.innerHTML = textToParagraphs(content);

    if (title) {
      document.title = `${title} | কাজদিয়া সরকারি উচ্চ মাধ্যমিক বিদ্যালয়`;
    }

    renderDocuments(page.documents);
  }
  /* END: Service page content renderer section */

  /* START: Service page API loader section */
  async function loadServicePage() {
    if (!pageRoot) return;

    const slug = String(pageRoot.getAttribute("data-service-page-key") || "").trim();

    if (!/^[a-z0-9-]+$/i.test(slug)) return;

    const apiUrl = new URL("api/service-pages.php", siteRoot);
    apiUrl.searchParams.set("slug", slug);
    apiUrl.searchParams.set("_", String(Date.now()));

    try {
      const response = await fetch(apiUrl.href, {
        credentials: "same-origin",
        cache: "no-store",
        headers: {
          Accept: "application/json"
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const payload = await response.json();

      if (payload && payload.success && payload.data) {
        renderServicePage(payload.data);
      }
    } catch (error) {
      /* Static hosting keeps the complete fallback content already present in the HTML. */
    }
  }

  loadServicePage();
  /* END: Service page API loader section */
})();
