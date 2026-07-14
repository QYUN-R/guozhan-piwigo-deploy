(function () {
  "use strict";

  function setText(element, value) {
    if (element && value) element.textContent = value;
  }

  function renderQr(box, url) {
    if (!box || !url) return;
    box.textContent = "";
    var image = document.createElement("img");
    image.src = url;
    image.alt = "客服二维码";
    image.loading = "lazy";
    image.style.width = "100%";
    image.style.height = "100%";
    image.style.objectFit = "contain";
    box.appendChild(image);
  }

  function renderLogo(box, url, brandName) {
    if (!box) return;
    if (!url) {
      if (brandName) box.setAttribute("aria-label", brandName);
      return;
    }
    box.textContent = "";
    var image = document.createElement("img");
    image.src = url;
    image.alt = brandName || "网站 Logo";
    box.appendChild(image);
  }

  function cssUrl(value) {
    return String(value || "").replace(/["\\\n\r]/g, "");
  }

  function applyHeroSlides() {
    var slides = (window.GZCA_HERO_SLIDES || []).filter(function (slide) {
      return slide && slide.url;
    });
    if (!slides.length) return;

    var canvas = document.querySelector(".hero-canvas");
    if (!canvas) return;

    var existing = Array.prototype.slice.call(canvas.querySelectorAll(".hero-slide"));
    while (existing.length < slides.length) {
      var next = document.createElement("div");
      next.className = "hero-slide";
      canvas.insertBefore(next, canvas.firstChild);
      existing.push(next);
    }
    existing.forEach(function (node, index) {
      if (index >= slides.length) {
        node.remove();
        return;
      }
      var slide = slides[index];
      node.style.backgroundImage = 'url("' + cssUrl(slide.url) + '")';
      node.style.backgroundPosition = slide.position || "center";
      if (slides.length === 1) {
        node.style.animation = "none";
        node.style.opacity = "1";
        node.style.transform = "scale(1.02)";
      } else {
        node.style.animationDuration = (slides.length * 6) + "s";
        node.style.animationDelay = (index * 6) + "s";
      }
    });
    if (slides[0].title) canvas.setAttribute("aria-label", slides[0].title);
  }

  function applyContact() {
    var contact = window.GZCA_CONTACT || {};
    if (!contact.brandName && !contact.logoUrl && !contact.wechat && !contact.qrUrl && !contact.note) return;

    document.querySelectorAll(".brand-text strong, [data-brand-name]").forEach(function (node) {
      setText(node, contact.brandName);
    });
    document.querySelectorAll(".brand-text > span, [data-brand-en]").forEach(function (node) {
      setText(node, contact.brandEn);
    });
    document.querySelectorAll(".brand-mark").forEach(function (box) {
      renderLogo(box, contact.logoUrl, contact.brandName);
    });
    document.querySelectorAll("[data-footer-brand]").forEach(function (node) {
      setText(node, contact.brandName ? "© " + contact.brandName : "");
    });

    document.querySelectorAll("[data-copy-wechat]").forEach(function (button) {
      if (contact.wechat) button.setAttribute("data-copy-wechat", contact.wechat);
    });

    document.querySelectorAll("[data-contact-modal]").forEach(function (modal) {
      setText(modal.querySelector("[data-contact-wechat], .contact-band h3"), contact.wechat);
      var note = modal.querySelector("[data-contact-note], .contact-band .muted");
      setText(note, contact.note);
      renderQr(modal.querySelector(".qr-box"), contact.qrUrl);
    });

    document.querySelectorAll(".qr-box").forEach(function (box) {
      renderQr(box, contact.qrUrl);
    });

    document.querySelectorAll(".service-tip").forEach(function (tip) {
      setText(tip, contact.note);
    });

    document.querySelectorAll("[data-contact-send-content]").forEach(function (node) {
      setText(node, contact.sendContent);
    });
    document.querySelectorAll("[data-contact-course-learning]").forEach(function (node) {
      setText(node, contact.courseLearning);
    });
    document.querySelectorAll("[data-contact-consultation-tip]").forEach(function (node) {
      setText(node, contact.consultationTip);
    });

    document.querySelectorAll(".detail-row").forEach(function (row) {
      var label = row.querySelector(".muted");
      if (!label) return;
      if (label.textContent.indexOf("微信号") >= 0) setText(row.querySelector("strong, span:last-child"), contact.wechat);
      if (label.textContent.indexOf("联系电话") >= 0) {
        if (contact.phone) {
          row.hidden = false;
          setText(row.querySelector("strong, span:last-child"), contact.phone);
        } else {
          row.hidden = true;
        }
      }
      if (label.textContent.indexOf("发送内容") >= 0) setText(row.querySelector("strong, span:last-child"), contact.sendContent);
      if (label.textContent.indexOf("课程学习") >= 0) setText(row.querySelector("strong, span:last-child"), contact.courseLearning);
      if (label.textContent.indexOf("咨询提示") >= 0) setText(row.querySelector("strong, span:last-child"), contact.consultationTip || contact.note);
    });
  }

  function applyFrontendState() {
    applyHeroSlides();
    applyContact();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", applyFrontendState);
  } else {
    applyFrontendState();
  }
  document.addEventListener("guozhan:rendered", applyFrontendState);
})();
