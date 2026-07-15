(function () {
  const body = document.body;
  const params = new URLSearchParams(window.location.search);
  const BRAND_NAME = "图物计划国展素材馆";
  const BRAND_EN = "AI Graphics Learning Plan";
  const WECHAT = "aiguozhanhuihua";
  const HOME_PAGE_SIZE = 16;

  const categories = {
    "ink-landscape": cat("国画", "山水画", "国画山水画", "GH-SS", ["山水", "云壑", "溪桥", "松石"]),
    "ink-figure": cat("国画", "人物画", "国画人物画", "GH-RW", ["人物", "仕女", "群像", "场景"]),
    "ink-flower-bird": cat("国画", "花鸟画", "国画花鸟画", "GH-HN", ["花鸟", "花卉", "禽鸟", "折枝"]),
    "oil-figure": cat("油画", "人物画", "油画人物画", "YH-RW", ["人物", "肖像", "群像", "场景"]),
    "oil-landscape": cat("油画", "风景画", "油画风景画", "YH-FJ", ["风景", "自然", "山野", "建筑"]),
    "oil-still": cat("油画", "静物画", "油画静物画", "YH-JW", ["静物", "器物", "花卉", "构图"]),
    "print-black-woodcut": cat("版画", "黑白木刻", "版画黑白木刻", "BH-HB", ["黑白木刻", "人物", "风景", "构成"]),
    "print-watermark": cat("版画", "木刻水印", "版画木刻水印", "BH-SY", ["木刻水印", "套色", "水印", "肌理"]),
    "print-screen": cat("版画", "丝网版画", "版画丝网版画", "BH-SW", ["丝网版画", "色彩", "图形", "构成"]),
    "sculpture-round": cat("雕塑", "圆雕", "雕塑圆雕", "DS-YD", ["圆雕", "人物", "动物", "空间"]),
    "sculpture-relief": cat("雕塑", "浮雕", "雕塑浮雕", "DS-FD", ["浮雕", "墙面", "叙事", "装饰"]),
    "lacquer-figure": cat("漆画", "人物", "漆画人物", "QH-RW", ["人物", "群像", "场景", "色漆"]),
    "lacquer-landscape": cat("漆画", "风景", "漆画风景", "QH-FJ", ["风景", "山水", "城市", "空间"]),
    "lacquer-still": cat("漆画", "静物", "漆画静物", "QH-JW", ["静物", "器物", "花卉", "肌理"]),
    "watercolor-figure": cat("水彩", "人物", "水彩人物", "SC-RW", ["人物", "肖像", "场景", "色调"]),
    "watercolor-landscape": cat("水彩", "风景", "水彩风景", "SC-FJ", ["风景", "自然", "建筑", "光影"]),
    "watercolor-still": cat("水彩", "静物", "水彩静物", "SC-JW", ["静物", "器物", "花卉", "构图"]),
    "folk-art": cat("农民画", "不分板块", "农民画", "NM-ZH", ["农民画", "乡土", "节日", "生活"], true),
    "comic": cat("漫画", "不分板块", "漫画", "MH-ZH", ["漫画", "叙事", "人物", "场景"], true),
    "illustration-painted": cat("插画", "绘画风格插画", "绘画风格插画", "CH-HH", ["插画", "绘画风格", "人物", "场景"]),
    "illustration-digital": cat("插画", "数字插画", "数字插画", "CH-SZ", ["数字插画", "视觉", "人物", "概念"]),
    "caa-plum-blossom": cat("中美协展览专项画稿", "梅花之韵——2026·中国画花鸟作品展", "梅花之韵——2026·中国画花鸟作品展", "ZX-MH", ["花鸟", "梅花", "国画", "展览"]),
    "caa-landscape-oil": cat("中美协展览专项画稿", "山水滋美——2026风景油画展", "山水滋美——2026风景油画展", "ZX-YS", ["风景油画", "山水", "自然", "展览"]),
    "caa-young-lacquer": cat("中美协展览专项画稿", "第五届青年漆画展览征稿通知", "第五届青年漆画展览征稿通知", "ZX-QH", ["青年漆画", "人物", "风景", "展览"]),
    "caa-unknown-01": cat("中美协展览专项画稿", "未知画展 01", "未知画展 01", "ZX-W1", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-02": cat("中美协展览专项画稿", "未知画展 02", "未知画展 02", "ZX-W2", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-03": cat("中美协展览专项画稿", "未知画展 03", "未知画展 03", "ZX-W3", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-04": cat("中美协展览专项画稿", "未知画展 04", "未知画展 04", "ZX-W4", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-05": cat("中美协展览专项画稿", "未知画展 05", "未知画展 05", "ZX-W5", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-06": cat("中美协展览专项画稿", "未知画展 06", "未知画展 06", "ZX-W6", ["预留画展", "征稿", "专项", "空板块"], false, true),
    "caa-unknown-07": cat("中美协展览专项画稿", "未知画展 07", "未知画展 07", "ZX-W7", ["预留画展", "征稿", "专项", "空板块"], false, true)
  };

  const directoryGroups = [
    group("国画", "涵盖山水、人物、花鸟等传统题材，适合按创作方向与画面类型选图。", [["山水画", "GH-SS", "ink-landscape"], ["人物画", "GH-RW", "ink-figure"], ["花鸟画", "GH-HN", "ink-flower-bird"]]),
    group("油画", "覆盖人物、风景、静物三类常用题材，便于按画面结构和题材方向筛选。", [["人物画", "YH-RW", "oil-figure"], ["风景画", "YH-FJ", "oil-landscape"], ["静物画", "YH-JW", "oil-still"]]),
    group("版画", "整理黑白木刻、木刻水印、丝网版画等作品，突出版种、构成与视觉语言。", [["黑白木刻", "BH-HB", "print-black-woodcut"], ["木刻水印", "BH-SY", "print-watermark"], ["丝网版画", "BH-SW", "print-screen"]]),
    group("雕塑", "按圆雕、浮雕归档，适合查找空间造型、人物动物与装饰性题材。", [["圆雕", "DS-YD", "sculpture-round"], ["浮雕", "DS-FD", "sculpture-relief"]]),
    group("漆画", "围绕人物、风景、静物整理，突出漆艺肌理、色层和装饰表达。", [["人物", "QH-RW", "lacquer-figure"], ["风景", "QH-FJ", "lacquer-landscape"], ["静物", "QH-JW", "lacquer-still"]]),
    group("水彩", "涵盖人物、风景、静物方向，便于查找透明水色、光影和构图参考。", [["人物", "SC-RW", "watercolor-figure"], ["风景", "SC-FJ", "watercolor-landscape"], ["静物", "SC-JW", "watercolor-still"]]),
    group("农民画", "集中呈现乡土生活、节庆民俗与民间叙事题材作品。", [["查看全部作品", "NM-ZH", "folk-art"]], true),
    group("漫画", "集中呈现叙事表达、人物场景与主题创意类漫画作品。", [["查看全部作品", "MH-ZH", "comic"]], true),
    group("插画", "按绘画风格与数字插画归档，覆盖人物、场景和视觉概念方向。", [["绘画风格插画", "CH-HH", "illustration-painted"], ["数字插画", "CH-SZ", "illustration-digital"]]),
    group("中美协展览专项画稿", "按展览征稿与专项主题归档，便于围绕指定题材集中选图。", [["全部作品", "ZX", "caa-exhibitions"]], true)
  ];

  const workImages = {
    GH: "ink-mountain.jpg",
    YH: "oil-landscape.jpg",
    BH: "print-woodcut.jpg",
    DS: "mixed-material.jpg",
    QH: "mixed-material.jpg",
    SC: "watercolor-landscape.jpg",
    NM: "folk-festival.jpg",
    MH: "mixed-material.jpg",
    CH: "ink-flower-bird.jpg",
    ZX: "ink-flower-bird.jpg"
  };

  const homeStreamKeys = [
    "ink-landscape", "oil-figure", "print-black-woodcut", "sculpture-round",
    "lacquer-figure", "watercolor-landscape", "folk-art", "comic",
    "illustration-painted", "caa-plum-blossom", "ink-flower-bird",
    "oil-still", "print-screen", "sculpture-relief", "lacquer-still",
    "watercolor-still", "illustration-digital", "caa-landscape-oil"
  ];

  function cat(parent, name, title, prefix, tags, direct, reserved) {
    return {
      parent,
      name,
      title,
      prefix,
      tags,
      direct: Boolean(direct),
      reserved: Boolean(reserved),
      desc: reserved
        ? "该专项展览作品位正在整理中，上线后将按独立编号归档展示。"
        : direct
        ? "本类作品按统一编号归档展示，可直接浏览全量内容，并通过题材标签或作品编号进一步筛选。"
        : "收录“" + title + "”方向作品，按编号、题材标签与作品名称归档，适合用于选图、比稿和作品资料整理。"
    };
  }

  function group(title, desc, links, direct) {
    return { title, desc, links, direct: Boolean(direct) };
  }

  function isCaaKey(key) {
    return String(key || "").indexOf("caa-") === 0 || String(key || "") === "caa-exhibitions";
  }

  function isCaaCategory(category) {
    if (!category) return false;
    return isCaaKey(category.key)
      || String(category.name || "").indexOf("中美协展览专项画稿") >= 0
      || String(category.code_prefix || "").toUpperCase() === "ZX";
  }

  function isCompetitionCategory(category) {
    if (!category) return false;
    const name = String(category.name || category.title || "");
    const code = String(category.code_prefix || category.code || "").toUpperCase();
    return name.indexOf("比赛") >= 0 || code === "BS" || code.indexOf("BS-") === 0;
  }

  function isHiddenFrontendCategory(category) {
    return isCompetitionCategory(category);
  }

  function isCaaDirectoryGroup(item) {
    if (!item) return false;
    return String(item.title || "").indexOf("中美协展览专项画稿") >= 0
      || (item.links || []).some((link) => isCaaKey(link.key || link[2]) || String(link.code || link[1] || "").toUpperCase().indexOf("ZX") === 0);
  }

  function orderDirectoryGroups(groups) {
    return groups.slice().sort((a, b) => {
      const ap = isCaaDirectoryGroup(a) ? -1 : 0;
      const bp = isCaaDirectoryGroup(b) ? -1 : 0;
      return ap - bp;
    });
  }

  const categoryNameCollator = new Intl.Collator("zh-CN", {
    numeric: true,
    sensitivity: "base"
  });

  function compareCategoryRank(a, b) {
    return Number(a?.rank || 0) - Number(b?.rank || 0)
      || categoryNameCollator.compare(String(a?.name || ""), String(b?.name || ""));
  }

  function compareExhibitionCategories(a, b) {
    const aReserved = Boolean(a?.reserved);
    const bReserved = Boolean(b?.reserved);
    if (aReserved !== bReserved) return aReserved ? 1 : -1;
    if (aReserved) {
      return categoryNameCollator.compare(String(a?.name || ""), String(b?.name || ""));
    }
    return compareCategoryRank(a, b);
  }

  function orderedCategoryChildren(parent, items) {
    const sorter = isCaaCategory(parent) || parent?.kind === "exhibition"
      ? compareExhibitionCategories
      : compareCategoryRank;
    return (items || []).slice().sort(sorter);
  }

  directoryGroups.splice(0, directoryGroups.length, ...orderDirectoryGroups(directoryGroups));

  function pagePrefix() {
    return window.location.pathname.indexOf("/screens/") >= 0 ? "" : "screens/";
  }

  function assetPrefix() {
    if (window.GUOZHAN_ASSET_BASE) return window.GUOZHAN_ASSET_BASE;
    return window.location.pathname.indexOf("/screens/") >= 0 ? "../assets/" : "assets/";
  }

  function categoryListHref(key) {
    return pagePrefix() + "category-list.html?cat=" + encodeURIComponent(key);
  }

  function detailHref(code, key) {
    return pagePrefix() + "detail.html?code=" + encodeURIComponent(code) + "&cat=" + encodeURIComponent(key);
  }

  function appHref(page) {
    if (page === "home") return window.location.pathname.indexOf("/screens/") >= 0 ? "../index.html" : "index.html";
    return pagePrefix() + page + ".html";
  }

  function imageForCode(code) {
    const top = String(code || "").split("-")[0] || "YH";
    return assetPrefix() + "works/" + (workImages[top] || "mixed-material.jpg");
  }

  function padCode(index) {
    return String(index + 1).padStart(3, "0");
  }

  function codeFor(data, index) {
    return data.prefix + "-" + padCode(index);
  }

  function escapeRegExp(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function workCodeInfoFromValue(value) {
    const text = String(value || "").trim().toUpperCase();
    if (!text) return null;
    const entries = Object.entries(categories).sort((a, b) => b[1].prefix.length - a[1].prefix.length);
    for (const [key, data] of entries) {
      const match = text.match(new RegExp("(^|\\s)(" + escapeRegExp(data.prefix) + ")-(\\d{1,4})(?=$|\\s)", "i"));
      if (match) {
        return {
          key,
          data,
          code: data.prefix + "-" + match[3].padStart(3, "0")
        };
      }
    }
    return null;
  }

  function resultTitle(data, serial) {
    if (data.direct) return data.title + " " + serial;
    return data.title + " " + serial;
  }

  function keyForCategory(data) {
    return Object.keys(categories).find((key) => categories[key] === data) || "oil-landscape";
  }

  function directoryGroupForKey(key) {
    return directoryGroups.find((item) => item.links.some((link) => link[2] === key)) || directoryGroups[0];
  }

  function getCodeFromCard(card) {
    const direct = card.getAttribute("data-code");
    if (direct) return direct;
    const badge = card.querySelector(".code-badge");
    return badge ? badge.textContent.trim() : "";
  }

  function hydrateWorkImages(root) {
    (root || document).querySelectorAll(".work-card, .result-card").forEach((card) => {
      const image = card.querySelector("img");
      const code = getCodeFromCard(card);
      if (image && code && image.getAttribute("data-api-image") !== "true") image.src = imageForCode(code);
    });
  }

  function directoryCardMarkup(item, index) {
    const primary = item.links[0];
    const visibleLimit = 3;
    const hiddenCount = Math.max(0, item.links.length - visibleLimit);
    const links = item.links.map((link, linkIndex) => {
      const extra = linkIndex >= visibleLimit ? " is-extra" : "";
      return '<a class="sub-link' + extra + '" href="' + categoryListHref(link[2]) + '">' + link[0] + ' <span>' + link[1] + '</span></a>';
    }).join("");
    return '<article class="directory-card" data-index="' + String(index + 1).padStart(2, "0") + '">' +
      '<div><p class="small">分类入口</p><h3><a class="directory-title-link" href="' + categoryListHref(primary[2]) + '">' + item.title + '</a></h3><p class="muted">' + item.desc + '</p></div>' +
      '<div class="sub-links">' + links + (hiddenCount ? '<div class="sub-more-row"><button class="sub-more-button" type="button" data-sub-toggle aria-expanded="false">展开更多 ' + hiddenCount + '</button></div>' : "") + '</div>' +
    '</article>';
  }

  function compactDirectoryCardMarkup(item, index) {
    const primary = item.links[0];
    return '<a class="quick-category-card" href="' + categoryListHref(primary[2]) + '">' +
      '<span class="quick-category-index">' + String(index + 1).padStart(2, "0") + '</span>' +
      '<strong>' + item.title + '</strong>' +
      '<small>' + (item.direct ? "统一作品入口" : item.links.map((link) => link[0]).slice(0, 3).join(" / ")) + '</small>' +
    '</a>';
  }

  function setupHomeDirectoryPager(directory) {
    if (!directory || !body.hasAttribute("data-home-page")) return;
    let shell = directory.closest(".home-directory-carousel");
    if (!shell) {
      shell = document.createElement("div");
      shell.className = "home-directory-carousel";
      directory.parentNode.insertBefore(shell, directory);
      shell.appendChild(directory);
    }
    let prev = shell.querySelector("[data-home-directory-prev]");
    let next = shell.querySelector("[data-home-directory-next]");
    if (!prev) {
      prev = document.createElement("button");
      prev.className = "home-directory-arrow home-directory-arrow-prev";
      prev.type = "button";
      prev.setAttribute("aria-label", "上一页分类");
      prev.setAttribute("data-home-directory-prev", "");
      shell.insertBefore(prev, directory);
    }
    if (!next) {
      next = document.createElement("button");
      next.className = "home-directory-arrow home-directory-arrow-next";
      next.type = "button";
      next.setAttribute("aria-label", "下一页分类");
      next.setAttribute("data-home-directory-next", "");
      shell.appendChild(next);
    }
    prev.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m14 6-6 6 6 6"/></svg>';
    next.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m10 6 6 6-6 6"/></svg>';
    const update = () => {
      const maxScroll = Math.max(0, directory.scrollWidth - directory.clientWidth - 2);
      prev.disabled = directory.scrollLeft <= 2;
      next.disabled = directory.scrollLeft >= maxScroll;
    };
    const turn = (direction) => {
      const amount = Math.max(directory.clientWidth * 0.86, 280);
      directory.scrollBy({ left: direction * amount, behavior: "smooth" });
      window.setTimeout(update, 260);
    };
    if (directory.dataset.homePagerReady !== "true") {
      directory.dataset.homePagerReady = "true";
      prev.addEventListener("click", () => turn(-1));
      next.addEventListener("click", () => turn(1));
      directory.addEventListener("scroll", update, { passive: true });
      window.addEventListener("resize", update);
    }
    window.setTimeout(update, 0);
  }

  function renderStaticDirectory() {
    document.querySelectorAll(".directory").forEach((directory) => {
      const isHome = body.hasAttribute("data-home-page");
      directory.classList.toggle("compact-directory", isHome);
      directory.classList.toggle("full-directory", !isHome);
      if (!isHome && body.hasAttribute("data-categories-page")) {
        const pager = createPagedRenderer({
          container: directory,
          pagination: document.querySelector("[data-directory-pagination]"),
          pageSize: 10,
          renderItem: (entry) => entry.item && entry.item.api ? apiDirectoryCardMarkup(entry.item, entry.index) : directoryCardMarkup(entry.item, entry.index),
          unit: "类",
          afterRender: wireSubCategoryToggles
        });
        pager.setItems(directoryGroups.map((item, index) => ({ item, index })));
        return;
      }
      directory.innerHTML = directoryGroups.map(isHome ? compactDirectoryCardMarkup : directoryCardMarkup).join("");
      wireSubCategoryToggles(directory);
      setupHomeDirectoryPager(directory);
    });
  }

  function renderStaticSideNav() {
    const sideNav = document.querySelector("body[data-category-page] .side-nav");
    if (!sideNav) return;
    const currentKey = params.get("cat") || "ink-landscape";
    const currentGroup = directoryGroupForKey(currentKey);
    const currentLinks = currentGroup.links.map((link) => {
      const active = link[2] === currentKey ? ' class="is-active"' : "";
      return '<a href="' + categoryListHref(link[2]) + '"' + active + '>' + link[0] + ' <span>' + link[1] + '</span></a>';
    }).join("");
    const otherLinks = directoryGroups.filter((item) => item !== currentGroup).map((item) => {
      const first = item.links[0];
      return '<a class="side-nav-secondary" href="' + categoryListHref(first[2]) + '">' + item.title + ' <span>' + first[1] + '</span></a>';
    }).join("");
    sideNav.innerHTML = '<span class="side-nav-label">当前分类</span>' + currentLinks + '<span class="side-nav-label">其他分类</span>' + otherLinks;
  }

  function wireSubCategoryToggles(root) {
    (root || document).querySelectorAll("[data-sub-toggle]").forEach((button) => {
      if (button.dataset.toggleReady === "true") return;
      button.dataset.toggleReady = "true";
      button.addEventListener("click", () => {
        const card = button.closest(".directory-card");
        if (!card) return;
        const expanded = card.classList.toggle("is-expanded");
        button.setAttribute("aria-expanded", String(expanded));
        button.textContent = expanded ? "收起" : "展开更多 " + card.querySelectorAll(".sub-link.is-extra").length;
      });
    });
  }

  function showToast(message) {
    const toast = document.querySelector("[data-toast]");
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("is-visible");
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => toast.classList.remove("is-visible"), 1800);
  }

  function fallbackCopyText(value) {
    const input = document.createElement("textarea");
    input.value = value;
    input.setAttribute("readonly", "");
    input.style.position = "fixed";
    input.style.left = "-9999px";
    input.style.opacity = "0";
    document.body.appendChild(input);
    input.select();
    input.setSelectionRange(0, value.length);
    let copied = false;
    try {
      copied = document.execCommand("copy");
    } catch (error) {
      copied = false;
    }
    input.remove();
    return copied;
  }

  async function copyText(value, successMessage) {
    const text = String(value || "").trim();
    if (!text) return;
    let copied = false;
    if (navigator.clipboard && window.isSecureContext) {
      try {
        await navigator.clipboard.writeText(text);
        copied = true;
      } catch (error) {
        copied = false;
      }
    }
    if (!copied) copied = fallbackCopyText(text);
    showToast(copied ? successMessage : "请长按微信号手动复制");
  }

  function bindWechatCopyTargets(root) {
    const scope = root || document;
    scope.querySelectorAll("[data-contact-wechat]").forEach((target) => {
      const value = String(target.textContent || target.getAttribute("data-copy-wechat") || WECHAT).trim();
      if (!value) return;
      target.classList.add("contact-wechat-copy");
      target.setAttribute("data-copy-wechat", value);
      target.setAttribute("title", "点击复制微信号");
      target.setAttribute("aria-label", "复制微信号 " + value);
      if (!target.matches("button, a")) {
        target.setAttribute("role", "button");
        target.setAttribute("tabindex", "0");
      }
    });

    scope.querySelectorAll("[data-copy-wechat]").forEach((button) => {
      if (button.dataset.copyWechatReady === "true") return;
      button.dataset.copyWechatReady = "true";
      if (!button.getAttribute("data-copy-wechat")) button.setAttribute("data-copy-wechat", WECHAT);
      const copyWechat = () => {
        const value = String(button.getAttribute("data-copy-wechat") || WECHAT).trim();
        if (value) copyText(value, "已复制微信号：" + value);
      };
      button.addEventListener("click", copyWechat);
      if (!button.matches("button, a")) {
        button.addEventListener("keydown", (event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            copyWechat();
          }
        });
      }
    });
  }

  function openModal() {
    const modal = document.querySelector("[data-contact-modal]");
    if (!modal) return;
    modal.classList.add("is-open");
    body.classList.add("modal-open");
  }

  function closeModal() {
    const modal = document.querySelector("[data-contact-modal]");
    if (!modal) return;
    modal.classList.remove("is-open");
    body.classList.remove("modal-open");
  }

  let lightboxLastFocus = null;

  function ensureImageLightbox() {
    let lightbox = document.querySelector("[data-image-lightbox]");
    if (lightbox) return lightbox;
    lightbox = document.createElement("div");
    lightbox.className = "image-lightbox";
    lightbox.setAttribute("data-image-lightbox", "");
    lightbox.setAttribute("role", "dialog");
    lightbox.setAttribute("aria-modal", "true");
    lightbox.setAttribute("aria-label", "作品大图预览");
    lightbox.innerHTML = '<button class="image-lightbox-close" type="button" data-close-image-lightbox aria-label="关闭大图">×</button><figure><img alt="作品大图"><figcaption></figcaption></figure>';
    document.body.appendChild(lightbox);
    lightbox.addEventListener("click", (event) => {
      if (event.target === lightbox || event.target.closest("[data-close-image-lightbox]")) closeImageLightbox();
    });
    return lightbox;
  }

  function openImageLightbox(image) {
    if (!image || !image.src) return;
    const lightbox = ensureImageLightbox();
    const preview = lightbox.querySelector("img");
    const caption = lightbox.querySelector("figcaption");
    lightboxLastFocus = document.activeElement;
    preview.src = image.currentSrc || image.src;
    preview.alt = image.alt || "作品大图";
    caption.textContent = [
      document.querySelector("[data-detail-code]")?.textContent?.trim(),
      document.querySelector("[data-detail-category]")?.textContent?.trim()
    ].filter(Boolean).join(" · ");
    lightbox.classList.add("is-open");
    body.classList.add("modal-open");
    lightbox.querySelector("[data-close-image-lightbox]")?.focus();
  }

  function closeImageLightbox() {
    const lightbox = document.querySelector("[data-image-lightbox]");
    if (!lightbox || !lightbox.classList.contains("is-open")) return;
    lightbox.classList.remove("is-open");
    body.classList.remove("modal-open");
    if (lightboxLastFocus && typeof lightboxLastFocus.focus === "function") lightboxLastFocus.focus();
    lightboxLastFocus = null;
  }

  function wireImageLightbox() {
    document.querySelectorAll(".detail-art img").forEach((image) => {
      image.setAttribute("tabindex", "0");
      image.setAttribute("role", "button");
      image.setAttribute("aria-label", "查看作品大图");
      image.addEventListener("click", () => openImageLightbox(image));
      image.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          openImageLightbox(image);
        }
      });
    });
  }

  function wireCommon() {
    const menuToggle = document.querySelector("[data-menu-toggle]");
    const nav = document.querySelector("[data-nav]");
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        const open = nav.classList.toggle("is-open");
        menuToggle.setAttribute("aria-expanded", String(open));
      });
    }

    document.querySelectorAll("[data-open-contact]").forEach((button) => button.addEventListener("click", openModal));
    document.querySelectorAll("[data-close-contact]").forEach((button) => button.addEventListener("click", closeModal));
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        closeImageLightbox();
        closeModal();
      }
    });

    const modal = document.querySelector("[data-contact-modal]");
    if (modal) modal.addEventListener("click", (event) => {
      if (event.target === modal) closeModal();
    });

    bindWechatCopyTargets(document);

    wireImageLightbox();

    document.querySelectorAll("[data-copy-code]").forEach((button) => {
      button.addEventListener("click", () => {
        const code = (params.get("code") || document.querySelector("[data-detail-code]")?.textContent || "").trim();
        if (code) copyText(code, "已复制作品编号");
      });
    });

    document.querySelectorAll("[data-search-form]").forEach((form) => {
      form.addEventListener("submit", (event) => {
        const input = form.querySelector("input[name='q']");
        const value = input ? input.value.trim() : "";
        if (!input || !value) {
          event.preventDefault();
          if (input) input.focus();
          showToast("请输入作品关键词或编号");
          return;
        }
      });
    });
  }

  function createPagedRenderer(options) {
    const { container, pagination, pageSize, renderItem, afterRender } = options;
    const emptyMessage = options.emptyMessage || "该栏目作品正在整理中";
    const unit = options.unit || "张";
    const prev = pagination ? pagination.querySelector("[data-page-prev], [data-home-prev]") : null;
    const next = pagination ? pagination.querySelector("[data-page-next], [data-home-next]") : null;
    const status = pagination ? pagination.querySelector("[data-page-status], [data-home-status]") : null;
    let items = [];
    let page = 0;

    function render() {
      if (!container) return;
      const totalPages = Math.max(1, Math.ceil(items.length / pageSize));
      page = Math.min(Math.max(page, 0), totalPages - 1);
      const current = items.slice(page * pageSize, page * pageSize + pageSize);
      container.innerHTML = current.length ? current.map(renderItem).join("") : '<div class="empty-state">' + emptyMessage + '</div>';
      if (afterRender) afterRender(container);
      if (status) status.textContent = "第 " + (page + 1) + " / " + totalPages + " 页 · 共 " + items.length + " " + unit;
      if (prev) prev.disabled = page === 0;
      if (next) next.disabled = page >= totalPages - 1;
    }

    if (prev) prev.addEventListener("click", () => {
      if (page <= 0) return;
      page -= 1;
      render();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    });
    if (next) next.addEventListener("click", () => {
      if (page >= Math.ceil(items.length / pageSize) - 1) return;
      page += 1;
      render();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    });

    return {
      setItems(nextItems) {
        items = nextItems || [];
        page = 0;
        render();
      }
    };
  }

  function buildWork(data, key, index, scoreIndex) {
    if (data.reserved) return null;
    const orderIndex = typeof scoreIndex === "number" ? scoreIndex : index;
    const serial = padCode(index);
    const tag = data.tags[index % data.tags.length];
    const code = codeFor(data, index);
    return {
      key,
      data,
      code,
      tag,
      title: resultTitle(data, serial),
      featuredScore: 1000 - orderIndex,
      downloadScore: (orderIndex * 17 + data.prefix.charCodeAt(0)) % 100,
      hotScore: (orderIndex * 31 + data.name.length * 5) % 100,
      newestScore: 1000 - orderIndex,
      meta: data.parent + " / " + data.name + " / " + tag
    };
  }

  function buildWorks(data, key, total) {
    if (data.reserved) return [];
    return Array.from({ length: total }, (_, index) => buildWork(data, key, index));
  }

  function renderWorkCard(item, detailBase) {
    const href = (detailBase || detailHref)(item.code, item.key);
    return '<a class="work-card" href="' + href + '" data-code="' + item.code + '">' +
      '<figure class="work-thumb"><span class="code-badge">' + item.code + '</span><img loading="lazy" decoding="async" src="' + imageForCode(item.code) + '" alt="' + item.title + '"></figure>' +
      '<div class="work-caption work-caption-compact"><p class="card-meta">' + item.meta + '</p><p class="work-code-line">作品编号：' + item.code + '</p></div>' +
    '</a>';
  }

  function compareWorkCode(a, b) {
    return String(a.code || "").localeCompare(String(b.code || ""), "zh-Hans-CN", { numeric: true, sensitivity: "base" });
  }

  function compareNewestWork(a, b) {
    return Number(b.newestScore || 0) - Number(a.newestScore || 0)
      || Number(b.id || 0) - Number(a.id || 0)
      || compareWorkCode(a, b);
  }

  function sortedWorks(works, sortType) {
    const list = works.slice();
    if (sortType === "downloads") return list.sort((a, b) => Number(b.downloadScore || 0) - Number(a.downloadScore || 0) || Number(b.hotScore || 0) - Number(a.hotScore || 0) || compareNewestWork(a, b));
    if (sortType === "hot") return list.sort((a, b) => Number(b.hotScore || 0) - Number(a.hotScore || 0) || Number(b.downloadScore || 0) - Number(a.downloadScore || 0) || compareNewestWork(a, b));
    if (sortType === "newest") return list.sort(compareNewestWork);
    if (sortType === "code") return list.sort(compareWorkCode);
    return list.sort((a, b) => Number(b.featuredScore || 0) - Number(a.featuredScore || 0) || Number(a.raw?.sort_order || 0) - Number(b.raw?.sort_order || 0) || compareWorkCode(a, b));
  }

  function sortLabel(sortType) {
    return ({ custom: "综合排序", code: "编号", newest: "最新", hot: "热门", downloads: "下载量", featured: "精选" })[sortType] || "综合排序";
  }

  function mountHomeStream() {
    if (!body.hasAttribute("data-home-page")) return;
    const grid = document.querySelector("[data-home-stream]");
    const pagination = document.querySelector("[data-home-pagination]");
    if (!grid || !pagination) return;
    const streamKeys = homeStreamKeys;
    const perStreamKey = Math.max(1, Math.ceil(80 / streamKeys.length));
    const works = streamKeys.flatMap((key, keyIndex) => {
      return Array.from({ length: perStreamKey }, (_, serialIndex) => {
        const orderIndex = keyIndex * perStreamKey + serialIndex;
        const work = buildWork(categories[key], key, serialIndex, orderIndex);
        return work;
      });
    }).filter(Boolean).slice(0, 80);
    const pager = createPagedRenderer({ container: grid, pagination, pageSize: 16, renderItem: (item) => renderWorkCard(item), unit: "张" });
    let activeSort = document.querySelector('[data-home-sort][aria-pressed="true"]')?.getAttribute("data-home-sort") || "featured";
    let activeFilter = "全部";

    function apply() {
      const filtered = works;
      pager.setItems(sortedWorks(filtered, activeSort));
    }

    document.querySelectorAll("[data-home-sort]").forEach((button) => {
      button.addEventListener("click", () => {
        const groupNode = button.closest("[data-home-sort-group]");
        if (groupNode) groupNode.querySelectorAll("[data-home-sort]").forEach((item) => item.setAttribute("aria-pressed", "false"));
        button.setAttribute("aria-pressed", "true");
        activeSort = button.getAttribute("data-home-sort") || "featured";
        apply();
      });
    });
    if (body.hasAttribute("data-competition-page")) {
      document.querySelectorAll("[data-filter-group] [data-filter]").forEach((button) => {
        button.addEventListener("click", () => {
          const groupNode = button.closest("[data-filter-group]");
          if (groupNode) groupNode.querySelectorAll("[data-filter]").forEach((item) => item.setAttribute("aria-pressed", "false"));
          button.setAttribute("aria-pressed", "true");
          activeFilter = button.textContent.trim() || "全部";
          apply();
        });
      });
    }
    apply();
  }

  function applyCategoryPage() {
    if (!body.hasAttribute("data-category-page")) return;
    const key = params.get("cat") || "ink-landscape";
    const data = categories[key] || categories["ink-landscape"];
    const parent = data.parent;
    const filters = data.direct ? ["全部"] : ["全部"].concat(data.tags);
    const allWorks = buildWorks(data, key, 64);

    document.title = data.title + " · " + BRAND_NAME;
    document.querySelectorAll("[data-category-parent]").forEach((node) => { node.textContent = parent; });
    document.querySelectorAll("[data-category-name]").forEach((node) => { node.textContent = data.direct ? "全部作品" : data.name; });
    document.querySelectorAll("[data-category-title]").forEach((node) => { node.textContent = data.title; });
    document.querySelectorAll("[data-category-desc]").forEach((node) => { node.textContent = data.desc; });

    const filterRow = document.querySelector("[data-filter-group]");
    if (filterRow) {
      filterRow.hidden = true;
      filterRow.innerHTML = "";
    }
    const pager = createPagedRenderer({
      container: document.querySelector("[data-category-gallery]"),
      pagination: document.querySelector("[data-pagination]"),
      pageSize: 16,
      renderItem: (item) => renderWorkCard(item),
      emptyMessage: data.reserved ? "该专项展览作品正在整理中" : "未找到符合当前条件的作品",
      unit: "张"
    });

    function update() {
      const active = "全部";
      const filtered = allWorks;
      pager.setItems(filtered);
      const status = document.querySelector("[data-filter-status]");
      if (status) status.textContent = "当前查看：" + (active || "全部");
    }

    if (filterRow && !context.isCaaBoard) {
      filterRow.querySelectorAll("[data-filter]").forEach((button) => {
        button.addEventListener("click", () => {
          filterRow.querySelectorAll("[data-filter]").forEach((item) => item.setAttribute("aria-pressed", "false"));
          button.setAttribute("aria-pressed", "true");
          update();
        });
      });
    }
    update();
  }

  function inferCategoryByCode(code) {
    const text = String(code || "").toUpperCase();
    return Object.values(categories).find((item) => text.indexOf(item.prefix + "-") === 0) || categories["ink-landscape"];
  }

  function inferCategoryByTerm(value) {
    const cn = String(value || "").trim();
    const text = cn.toUpperCase();
    const codeMatch = Object.values(categories).find((item) => text.indexOf(item.prefix + "-") >= 0);
    if (codeMatch) return codeMatch;
    const byName = Object.values(categories).find((item) => cn.includes(item.parent) || cn.includes(item.name) || cn.includes(item.title));
    if (byName) return byName;
    if (cn.includes("山水")) return categories["ink-landscape"];
    if (cn.includes("花鸟") || cn.includes("梅花")) return categories["ink-flower-bird"];
    if (cn.includes("黑白") || cn.includes("木刻")) return categories["print-black-woodcut"];
    if (cn.includes("水印")) return categories["print-watermark"];
    if (cn.includes("丝网")) return categories["print-screen"];
    if (cn.includes("圆雕")) return categories["sculpture-round"];
    if (cn.includes("浮雕")) return categories["sculpture-relief"];
    if (cn.includes("农民画")) return categories["folk-art"];
    if (cn.includes("漫画")) return categories["comic"];
    if (cn.includes("数字插画")) return categories["illustration-digital"];
    if (cn.includes("插画")) return categories["illustration-painted"];
    if (cn.includes("中美协") || cn.includes("展览专项") || cn.includes("征稿")) return categories["caa-plum-blossom"];
    if (cn.includes("水彩")) return categories["watercolor-landscape"];
    if (cn.includes("漆画")) return categories["lacquer-figure"];
    if (cn.includes("油画")) return categories["oil-landscape"];
    return categories["ink-landscape"];
  }

  function applySearchPage() {
    if (!body.hasAttribute("data-search-page")) return;
    const query = params.get("q") || "国画";
    const codeInfo = workCodeInfoFromValue(query);
    if (codeInfo) {
      window.location.replace(detailHref(codeInfo.code, codeInfo.key));
      return;
    }
    const data = inferCategoryByTerm(query);
    const key = keyForCategory(data);
    document.querySelectorAll("[data-search-term]").forEach((node) => { node.textContent = query || "全部作品"; });
    const input = document.querySelector("[data-search-form] input[name='q']");
    if (input) input.value = query;
    const pager = createPagedRenderer({
      container: document.querySelector("[data-search-results]"),
      pagination: document.querySelector("[data-pagination]"),
      pageSize: 16,
      renderItem: (item) => '<article class="result-card" data-code="' + item.code + '"><a class="work-thumb" href="' + detailHref(item.code, item.key) + '"><span class="code-badge">' + item.code + '</span><img loading="lazy" decoding="async" src="' + imageForCode(item.code) + '" alt="' + item.code + '"></a><div class="work-caption work-caption-compact"><p class="card-meta">' + item.meta + '</p><p class="work-code-line">作品编号：' + item.code + '</p></div><a class="btn btn-secondary" href="' + detailHref(item.code, item.key) + '">查看详情</a></article>',
      unit: "条"
    });
    pager.setItems(buildWorks(data, key, 32));
  }

  function applyDetailPage() {
    if (!body.hasAttribute("data-detail-page")) return;
    const code = (params.get("code") || "GH-SS-001").toUpperCase();
    const data = inferCategoryByCode(code);
    const key = keyForCategory(data);
    const serial = code.split("-").pop() || "001";
    const title = resultTitle(data, serial);
    const category = data.parent + " / " + data.name;
    document.title = title + " · " + code;
    document.querySelectorAll("[data-detail-code]").forEach((node) => { node.textContent = code; });
    document.querySelectorAll("[data-detail-title]").forEach((node) => { node.textContent = title; });
    document.querySelectorAll("[data-detail-category]").forEach((node) => { node.textContent = category; });
    document.querySelectorAll("[data-detail-category-title]").forEach((node) => { node.textContent = data.title; });
    document.querySelectorAll("[data-detail-tags]").forEach((node) => { node.textContent = [data.parent, data.name].concat(data.tags.slice(0, 2)).join("、"); });
    document.querySelectorAll("[data-detail-desc]").forEach((node) => { node.textContent = "作品已归档至“" + data.title + "”，请记录编号以便确认高清素材与同类作品。"; });
    const mainImage = document.querySelector(".detail-art img");
    if (mainImage) {
      mainImage.src = imageForCode(code);
      mainImage.alt = title;
    }
    document.querySelectorAll("[data-detail-category-link], [data-related-more], [data-back-list]").forEach((link) => link.setAttribute("href", categoryListHref(key)));
    const relatedTitle = document.querySelector("[data-related-title]");
    if (relatedTitle) relatedTitle.textContent = data.title + "推荐";
    const related = buildWorks(data, key, 9).filter((item) => item.code !== code).slice(0, 8);
    const relatedGrid = document.querySelector("[data-related-grid]");
    if (relatedGrid) relatedGrid.innerHTML = related.map((item) => renderWorkCard(item)).join("");
  }

  function applyRoundedFavicon(src) {
    const link = document.querySelector('link[data-gzca-favicon]') || document.createElement("link");
    link.rel = "icon";
    link.type = "image/png";
    link.sizes = "128x128";
    link.setAttribute("data-gzca-favicon", "true");
    link.href = src;
    if (!link.parentNode) document.head.appendChild(link);

    const image = new Image();
    image.decoding = "async";
    image.onload = () => {
      try {
        const size = 128;
        const inset = 4;
        const radius = 24;
        const canvas = document.createElement("canvas");
        canvas.width = size;
        canvas.height = size;
        const context = canvas.getContext("2d");
        if (!context) return;

        context.beginPath();
        context.moveTo(inset + radius, inset);
        context.lineTo(size - inset - radius, inset);
        context.quadraticCurveTo(size - inset, inset, size - inset, inset + radius);
        context.lineTo(size - inset, size - inset - radius);
        context.quadraticCurveTo(size - inset, size - inset, size - inset - radius, size - inset);
        context.lineTo(inset + radius, size - inset);
        context.quadraticCurveTo(inset, size - inset, inset, size - inset - radius);
        context.lineTo(inset, inset + radius);
        context.quadraticCurveTo(inset, inset, inset + radius, inset);
        context.closePath();
        context.clip();

        const available = size - inset * 2;
        const scale = Math.max(available / image.naturalWidth, available / image.naturalHeight);
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;
        context.drawImage(image, (size - width) / 2, (size - height) / 2, width, height);
        link.href = canvas.toDataURL("image/png");
      } catch (error) {
        link.href = src;
      }
    };
    image.onerror = () => { link.href = src; };
    image.src = src;
  }

  function applyBrand() {
    const src = assetPrefix() + "logo-mark.png?v=20260712-logo-fulltext";
    applyRoundedFavicon(src);
    document.querySelectorAll(".brand-mark").forEach((node) => {
      const image = node.querySelector("img") || document.createElement("img");
      image.src = src;
      image.alt = "";
      if (!image.parentNode) {
        node.textContent = "";
        node.appendChild(image);
      }
    });
    applyContactData({
      brandName: BRAND_NAME,
      brandEn: BRAND_EN,
      wechat: WECHAT,
      note: "建议提供作品编号或页面截图，便于准确确认作品。",
      qrUrl: ""
    });
    getApiContact().then(applyContactData).catch((error) => {
      console.warn("Guozhan contact API unavailable", error);
    });
  }

  function applyStaticNavigation() {
    if (window.GUOZHAN_PIWIGO) return;
    const nav = document.querySelector("[data-nav]");
    if (!nav) return;
    const active = body.hasAttribute("data-home-page") ? "home"
      : body.hasAttribute("data-categories-page") || body.hasAttribute("data-category-page") || body.hasAttribute("data-detail-page") ? "categories"
      : body.hasAttribute("data-search-page") ? "search"
      : body.hasAttribute("data-contact-page") ? "contact"
      : "";
    const links = [
      ["home", "首页", appHref("home")],
      ["categories", "作品分类", appHref("categories")],
      ["search", "搜索", appHref("search")],
      ["contact", "联系客服", appHref("contact")]
    ];
    nav.innerHTML = links.map((item) => '<a href="' + item[2] + '"' + (item[0] === active ? ' aria-current="page"' : "") + '>' + item[1] + '</a>').join("");
  }

  const API_PAGE_SIZE = 20;
  const API_FETCH_LIMIT = 100;
  let apiCategoriesPromise = null;
  let apiContactPromise = null;
  let apiHomePromise = null;

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, (char) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      "\"": "&quot;",
      "'": "&#39;"
    })[char]);
  }

  function normalizeUrl(url) {
    if (!url) return "";
    try {
      const parsed = new URL(url, window.location.origin);
      if (window.location.protocol === "https:" && parsed.protocol === "http:" && parsed.host === window.location.host) {
        parsed.protocol = "https:";
      }
      return parsed.href;
    } catch (error) {
      return String(url);
    }
  }

  function apiUrl(method, params) {
    const query = new URLSearchParams({ format: "json", method });
    Object.entries(params || {}).forEach(([key, value]) => {
      if (key === "fallbackKey" || value === undefined || value === null || value === "") return;
      query.set(key, String(value));
    });
    return "/ws.php?" + query.toString();
  }

  async function apiFetch(method, params) {
    const response = await fetch(apiUrl(method, params), {
      credentials: "same-origin",
      headers: { Accept: "application/json" }
    });
    if (!response.ok) throw new Error("API HTTP " + response.status);
    const payload = await response.json();
    if (payload.stat && payload.stat !== "ok") {
      const message = payload.err?.msg || payload.err?.message || "API returned " + payload.stat;
      throw new Error(message);
    }
    return payload.result || payload;
  }

  function getApiCategories() {
    if (!apiCategoriesPromise) {
      apiCategoriesPromise = apiFetch("gzca.categories.getList").then((result) => result.categories || []);
    }
    return apiCategoriesPromise;
  }

  function getApiContact() {
    if (!apiContactPromise) {
      apiContactPromise = apiFetch("gzca.contact.get").then((result) => result || {});
    }
    return apiContactPromise;
  }

  function getApiHome() {
    if (!apiHomePromise) {
      apiHomePromise = apiFetch("gzca.home.get").then((result) => result || {});
    }
    return apiHomePromise;
  }

  function cleanCssUrl(value) {
    return String(value || "").replace(/["\\\n\r]/g, "");
  }

  function applyHeroSlides(slides) {
    if (!body.hasAttribute("data-home-page")) return;
    const items = (slides || []).filter((slide) => slide && slide.url);
    if (!items.length) return;
    const canvas = document.querySelector(".hero-canvas");
    if (!canvas) return;
    let nodes = Array.from(canvas.querySelectorAll(".hero-slide"));
    while (nodes.length < items.length) {
      const node = document.createElement("div");
      node.className = "hero-slide";
      canvas.insertBefore(node, canvas.firstChild);
      nodes.push(node);
    }
    nodes.forEach((node, index) => {
      if (index >= items.length) {
        node.remove();
        return;
      }
      const slide = items[index];
      node.style.backgroundImage = 'url("' + cleanCssUrl(normalizeUrl(slide.url)) + '")';
      node.style.backgroundPosition = slide.position || "center";
      if (items.length === 1) {
        node.style.animation = "none";
        node.style.opacity = "1";
        node.style.transform = "scale(1.02)";
      } else {
        node.style.animationDuration = (items.length * 6) + "s";
        node.style.animationDelay = (index * 6) + "s";
      }
    });
    if (items[0].title) canvas.setAttribute("aria-label", items[0].title);
  }

  function applyHomeSettings() {
    getApiHome().then((settings) => {
      applyHeroSlides(settings.heroSlides || []);
    }).catch((error) => {
      console.warn("Guozhan home API unavailable", error);
    });
  }

  function normalizeContactItems(contact) {
    const items = contact && Array.isArray(contact.contacts) ? contact.contacts : [];
    const normalized = items.map((item, index) => ({
      slot: Number(item.slot || index + 1),
      label: String(item.label || "客服" + (index + 1)).trim() || "客服" + (index + 1),
      wechat: String(item.wechat || "").trim(),
      note: String(item.note || "").trim(),
      qrUrl: String(item.qrUrl || "").trim()
    })).filter((item) => item.wechat || item.qrUrl);
    if (normalized.length) return normalized.slice(0, 2);
    return [{
      slot: 1,
      label: "客服1",
      wechat: (contact && contact.wechat) || WECHAT,
      note: (contact && contact.note) || "",
      qrUrl: (contact && contact.qrUrl) || ""
    }];
  }

  function contactCardMarkup(item, compact) {
    const wechat = String(item.wechat || WECHAT).trim() || WECHAT;
    const qr = item.qrUrl ? '<div class="qr-box" data-has-qr="true"><img src="' + escapeHtml(normalizeUrl(item.qrUrl)) + '" alt="' + escapeHtml(item.label) + '二维码" loading="lazy" decoding="async"></div>' : '<div class="qr-box">' + escapeHtml(item.label) + '<br>二维码</div>';
    const note = !compact && item.note ? '<p class="muted" data-contact-note>' + escapeHtml(item.note) + '</p>' : '';
    return '<article class="contact-card' + (compact ? ' is-compact' : '') + '">' +
      '<p class="eyebrow">' + escapeHtml(item.label) + '</p>' +
      qr +
      '<div class="contact-card-info"><span class="muted">微信号</span><button class="contact-wechat-copy" type="button" data-contact-wechat data-copy-wechat="' + escapeHtml(wechat) + '" title="点击复制微信号" aria-label="复制微信号 ' + escapeHtml(wechat) + '">' + escapeHtml(wechat) + '</button>' + note + '</div>' +
    '</article>';
  }

  function bindContactCopyButtons(container) {
    bindWechatCopyTargets(container);
  }

  function renderContactList(container, contacts, compact) {
    if (!container) return;
    container.innerHTML = contacts.map((item) => contactCardMarkup(item, compact)).join("");
    container.classList.toggle("is-single", contacts.length < 2);
    bindContactCopyButtons(container);
  }

  function contactGuideMarkup(contact, actionHref) {
    const data = contact || {};
    const safeSendContent = escapeHtml(data.sendContent || "作品编号 / 页面截图 / 学习需求");
    const safeCourseLearning = escapeHtml(data.courseLearning || "绘画课程咨询、学习方向与作品参考");
    const safeConsultationTip = escapeHtml(data.consultationTip || "请发送作品编号和学习需求，客服将及时协助。");
    return '<div class="contact-guide">' +
      '<p class="eyebrow">沟通说明</p>' +
      '<h3>扫码添加客服，发送作品编号确认素材</h3>' +
      '<p class="lead">客服会根据编号定位作品，协助确认高清素材、同类推荐、使用方式和交付信息。</p>' +
      '<div class="contact-guide-list">' +
        '<div class="contact-guide-row"><span>发送内容</span><strong>' + safeSendContent + '</strong></div>' +
        '<div class="contact-guide-row"><span>课程学习</span><strong>' + safeCourseLearning + '</strong></div>' +
        '<div class="contact-guide-row"><span>咨询提示</span><strong>' + safeConsultationTip + '</strong></div>' +
      '</div>' +
      '<div class="contact-actions"><a class="btn btn-secondary" href="' + actionHref + '">继续浏览作品</a></div>' +
    '</div>';
  }

  function renderContactModalBand(container, contacts, contact) {
    if (!container) return;
    container.classList.add("contact-modal-layout");
    container.removeAttribute("data-contact-modal-list");
    container.innerHTML = '<div class="contact-modal-qrs"><p class="eyebrow">客服二维码</p><div class="contact-list">' +
      contacts.map((item) => contactCardMarkup(item, true)).join("") +
      '</div></div>' + contactGuideMarkup(contact, appHref("categories"));
    bindContactCopyButtons(container);
  }

  function renderContactQr(container, qrUrl, label) {
    if (!container || !qrUrl) return;
    const src = normalizeUrl(qrUrl);
    const image = container.querySelector("img") || document.createElement("img");
    image.src = src;
    image.alt = (label || "客服") + "二维码";
    image.loading = "lazy";
    image.decoding = "async";
    if (!image.parentNode) {
      container.textContent = "";
      container.appendChild(image);
    }
    container.setAttribute("data-has-qr", "true");
  }

  function applyContactData(contact) {
    const brandName = (contact && contact.brandName) || BRAND_NAME;
    const brandEn = (contact && contact.brandEn) || BRAND_EN;
    const contacts = normalizeContactItems(contact);
    const primary = contacts[0] || { label: "客服1", wechat: WECHAT, note: "", qrUrl: "" };
    const wechat = primary.wechat || WECHAT;
    const note = primary.note || (contact && contact.note) || "";
    document.querySelectorAll(".brand-text strong").forEach((node) => { node.textContent = brandName; });
    document.querySelectorAll(".brand-text > span").forEach((node) => { node.textContent = brandEn; });
    document.querySelectorAll("[data-footer-brand], .footer-inner span:first-child").forEach((node) => { node.textContent = "© " + brandName; });
    document.querySelectorAll("[data-contact-list]").forEach((node) => renderContactList(node, contacts, false));
    document.querySelectorAll('body[data-contact-page] [data-od-id="contact-main"] .contact-band > div:last-child').forEach((node) => {
      node.outerHTML = contactGuideMarkup(contact, appHref("categories"));
    });
    document.querySelectorAll(".modal .contact-band").forEach((node) => renderContactModalBand(node, contacts, contact));
    document.querySelectorAll("[data-contact-modal-list]").forEach((node) => {
      if (!node.closest(".modal")) renderContactList(node, contacts, true);
    });
    document.querySelectorAll("[data-contact-wechat]").forEach((node) => { if (!node.closest(".contact-card")) node.textContent = wechat; });
    document.querySelectorAll("[data-copy-wechat]").forEach((node) => { if (!node.closest("[data-contact-list], .modal")) node.setAttribute("data-copy-wechat", wechat); });
    if (note) {
      document.querySelectorAll("[data-contact-note]").forEach((node) => { if (!node.closest(".contact-card")) node.textContent = note; });
    }
    if (primary.qrUrl) {
      document.querySelectorAll(".qr-box").forEach((node) => { if (!node.closest(".contact-card")) renderContactQr(node, primary.qrUrl, primary.label); });
    }
    bindWechatCopyTargets(document);
  }

  function categoryHrefFromApi(category) {
    const query = new URLSearchParams();
    if (category.key) query.set("cat", category.key);
    if (category.id) query.set("cat_id", category.id);
    return pagePrefix() + "category-list.html?" + query.toString();
  }

  function apiDirectoryCardMarkup(item, index) {
    const visibleLimit = item.kind === "exhibition" ? item.links.length : 3;
    const hiddenCount = Math.max(0, item.links.length - visibleLimit);
    const primaryHref = item.links[0]?.href || "#";
    const coverUrl = normalizeUrl(item.coverUrl);
    const links = item.links.map((link, linkIndex) => {
      const extra = linkIndex >= visibleLimit ? " is-extra" : "";
      return '<a class="sub-link' + extra + '" href="' + escapeHtml(link.href) + '">' + escapeHtml(link.name) + ' <span>' + escapeHtml(link.code || "") + '</span></a>';
    }).join("");
    const cover = coverUrl
      ? '<a class="directory-cover-link" href="' + escapeHtml(primaryHref) + '" aria-label="查看' + escapeHtml(item.title) + '"><img class="directory-cover" src="' + escapeHtml(coverUrl) + '" alt="" loading="lazy" decoding="async"></a>'
      : "";
    return '<article class="directory-card' + (coverUrl ? " has-cover" : "") + '" data-index="' + String(index + 1).padStart(2, "0") + '">' +
      cover +
      '<div><p class="small">分类入口</p><h3><a class="directory-title-link" href="' + escapeHtml(primaryHref) + '">' + escapeHtml(item.title) + '</a></h3><p class="muted">' + escapeHtml(item.desc || "后台分类已接入，可在后台新增和调整作品。") + '</p></div>' +
      '<div class="sub-links">' + links + (hiddenCount ? '<div class="sub-more-row"><button class="sub-more-button" type="button" data-sub-toggle aria-expanded="false">展开更多 ' + hiddenCount + '</button></div>' : "") + '</div>' +
    '</article>';
  }

  function apiCompactDirectoryCardMarkup(item, index) {
    const primary = item.links[0];
    const coverUrl = normalizeUrl(item.coverUrl);
    return '<a class="quick-category-card' + (coverUrl ? " has-cover" : "") + '" href="' + escapeHtml(primary?.href || "#") + '">' +
      (coverUrl ? '<img class="quick-category-cover" src="' + escapeHtml(coverUrl) + '" alt="" loading="lazy" decoding="async">' : "") +
      '<span class="quick-category-index">' + String(index + 1).padStart(2, "0") + '</span>' +
      '<strong>' + escapeHtml(item.title) + '</strong>' +
      '<small>' + escapeHtml(item.links.map((link) => link.name).slice(0, 3).join(" / ") || "后台分类入口") + '</small>' +
    '</a>';
  }

  function categoryCanShowDirectEntry(category) {
    return Boolean(category && (category.direct_upload || Number(category.nb_images || 0) > 0));
  }

  function apiDirectoryGroups(categoriesList) {
    const visibleCategories = categoriesList.filter((item) => !isHiddenFrontendCategory(item));
    const byId = new Map(visibleCategories.map((item) => [Number(item.id), item]));
    const children = new Map();
    visibleCategories.forEach((item) => {
      const parentId = Number(item.id_uppercat || 0);
      if (!parentId || !byId.has(parentId)) return;
      if (!children.has(parentId)) children.set(parentId, []);
      children.get(parentId).push(item);
    });
    const parents = visibleCategories
      .filter((item) => !item.id_uppercat || !byId.has(Number(item.id_uppercat)))
      .sort((a, b) => Number(a.rank || 0) - Number(b.rank || 0) || String(a.name || "").localeCompare(String(b.name || "")));

    const groups = parents.map((parent) => {
      const childItems = orderedCategoryChildren(parent, children.get(Number(parent.id)) || []);
      const caaGroup = isCaaCategory(parent);
      const linkItems = caaGroup
        ? [parent].concat(childItems)
        : (categoryCanShowDirectEntry(parent) ? [parent].concat(childItems) : (childItems.length ? childItems : [parent]));
      return {
        title: parent.name || "作品分类",
        desc: parent.comment || "后台分类已接入，可在后台新增和调整作品。",
        kind: parent.kind || "catalog",
        coverUrl: parent.tn_url || "",
        links: linkItems.map((category) => {
          const isParentDirect = Number(category.id) === Number(parent.id);
          return {
            id: category.id,
            key: category.key,
            name: isParentDirect ? "全部作品" : (category.name || "未命名分类"),
            code: category.code_prefix || "",
            href: categoryHrefFromApi(category)
          };
        })
      };
    }).filter((item) => item.links.length);
    return orderDirectoryGroups(groups);
  }

  function renderStaticDirectory() {
    document.querySelectorAll(".directory").forEach((directory) => {
      const isHome = body.hasAttribute("data-home-page");
      directory.classList.toggle("compact-directory", isHome);
      directory.classList.toggle("full-directory", !isHome);
      if (!isHome && body.hasAttribute("data-categories-page")) {
        const pager = createPagedRenderer({
          container: directory,
          pagination: document.querySelector("[data-directory-pagination]"),
          pageSize: 10,
          renderItem: (entry) => entry.item && entry.item.api ? apiDirectoryCardMarkup(entry.item, entry.index) : directoryCardMarkup(entry.item, entry.index),
          unit: "类",
          afterRender: wireSubCategoryToggles
        });
        pager.setItems(directoryGroups.map((item, index) => ({ item, index })));
        getApiCategories().then((list) => {
          const apiGroups = apiDirectoryGroups(list);
          if (apiGroups.length) {
            pager.setItems(apiGroups.map((item, index) => ({
              item: Object.assign({ api: true }, item),
              index
            })));
          }
        }).catch((error) => console.warn("Guozhan category API unavailable", error));
        return;
      }
      directory.innerHTML = directoryGroups.map(isHome ? compactDirectoryCardMarkup : directoryCardMarkup).join("");
      wireSubCategoryToggles(directory);
      setupHomeDirectoryPager(directory);
      getApiCategories().then((list) => {
        const apiGroups = apiDirectoryGroups(list);
        if (!apiGroups.length) return;
        directory.innerHTML = apiGroups.map(isHome ? apiCompactDirectoryCardMarkup : apiDirectoryCardMarkup).join("");
        wireSubCategoryToggles(directory);
        setupHomeDirectoryPager(directory);
      }).catch((error) => console.warn("Guozhan category API unavailable", error));
    });
  }

  function renderStaticSideNav() {
    const sideNav = document.querySelector("body[data-category-page] .side-nav");
    if (!sideNav) return;
    const currentKey = params.get("cat") || "ink-landscape";
    const currentGroup = directoryGroupForKey(currentKey);
    const currentLinks = currentGroup.links.map((link) => {
      const active = link[2] === currentKey ? ' class="is-active"' : "";
      return '<a href="' + categoryListHref(link[2]) + '"' + active + '>' + link[0] + ' <span>' + link[1] + '</span></a>';
    }).join("");
    const otherLinks = directoryGroups.filter((item) => item !== currentGroup).map((item) => {
      const first = item.links[0];
      return '<a class="side-nav-secondary" href="' + categoryListHref(first[2]) + '">' + item.title + ' <span>' + first[1] + '</span></a>';
    }).join("");
    sideNav.innerHTML = '<span class="side-nav-label">当前分类</span>' + currentLinks + '<span class="side-nav-label">其他分类</span>' + otherLinks;

    getApiCategories().then((list) => {
      const visibleList = list.filter((item) => !isHiddenFrontendCategory(item));
      const currentId = Number(params.get("cat_id") || 0);
      const byId = new Map(visibleList.map((item) => [Number(item.id), item]));
      const current = visibleList.find((item) => currentId && Number(item.id) === currentId) || visibleList.find((item) => item.key === currentKey);
      if (!current) return;
      const parent = current.id_uppercat && byId.has(Number(current.id_uppercat)) ? byId.get(Number(current.id_uppercat)) : current;
      const siblings = orderedCategoryChildren(
        parent,
        visibleList.filter((item) => Number(item.id_uppercat || 0) === Number(parent.id))
      );
      const caaGroup = isCaaCategory(parent);
      const primary = caaGroup ? [parent].concat(siblings) : (categoryCanShowDirectEntry(parent) ? [parent].concat(siblings) : (siblings.length ? siblings : [parent]));
      const primaryLinks = primary.map((item) => {
        const active = Number(item.id) === Number(current.id) || item.key === current.key ? ' class="is-active"' : "";
        const label = Number(item.id) === Number(parent.id) ? "全部作品" : (item.name || "分类");
        return '<a href="' + escapeHtml(categoryHrefFromApi(item)) + '"' + active + '>' + escapeHtml(label) + ' <span>' + escapeHtml(item.code_prefix || "") + '</span></a>';
      }).join("");
      const otherParents = visibleList
        .filter((item) => !item.id_uppercat && Number(item.id) !== Number(parent.id))
        .sort((a, b) => Number(a.rank || 0) - Number(b.rank || 0) || String(a.name || "").localeCompare(String(b.name || "")));
      const other = orderDirectoryGroups(otherParents.map((item) => ({
        title: item.name || "分类",
        links: [{ key: item.key, code: item.code_prefix || "", href: categoryHrefFromApi(item), name: item.name || "分类" }],
        raw: item
      })))
        .map((group) => '<a class="side-nav-secondary" href="' + escapeHtml(group.links[0].href) + '">' + escapeHtml(group.title) + ' <span>' + escapeHtml(group.links[0].code || "") + '</span></a>')
        .join("");
      sideNav.innerHTML = '<span class="side-nav-label">当前分类</span>' + primaryLinks + '<span class="side-nav-label">其他分类</span>' + other;
    }).catch((error) => console.warn("Guozhan side nav API unavailable", error));
  }

  function localKeyFromApiCategory(category, code, fallbackKey) {
    if (category?.key && categories[category.key]) return category.key;
    if (fallbackKey && categories[fallbackKey]) return fallbackKey;
    const prefix = String(category?.code_prefix || code || "").toUpperCase();
    const byPrefix = Object.entries(categories).find(([, data]) => prefix.indexOf(data.prefix) === 0 || String(code || "").toUpperCase().indexOf(data.prefix + "-") === 0);
    return byPrefix ? byPrefix[0] : (category?.key || fallbackKey || "oil-landscape");
  }

  function localDataForWork(category, key) {
    if (categories[key]) return categories[key];
    return {
      parent: category?.name || "作品分类",
      name: category?.name || "作品",
      title: category?.name || "作品",
      prefix: category?.code_prefix || "GZ",
      tags: [],
      direct: true,
      reserved: false,
      desc: category?.comment || "后台分类作品。"
    };
  }

  function bestDerivativeUrl(derivatives, names) {
    for (const name of names) {
      if (derivatives?.[name]?.url) return derivatives[name].url;
    }
    return "";
  }

  function normalizeApiWork(image, fallbackKey) {
    const category = image.category || {};
    const key = localKeyFromApiCategory(category, image.gzca_code, fallbackKey);
    const data = localDataForWork(category, key);
    const code = String(image.gzca_code || ("IMG-" + image.id)).toUpperCase();
    const title = image.name || code;
    const categoryName = category.name || data.name || "作品分类";
    const parentName = data.parent || categoryName;
    const isCaaWork = isCaaCategory(category) || isCaaKey(key);
    const displayParentName = isCaaWork ? "中美协展览专项画稿" : parentName;
    const displayCategoryName = isCaaWork ? "" : categoryName;
    const displayMeta = [displayParentName, displayCategoryName].filter(Boolean).join(" / ");
    const thumbDerivative = image.derivatives?.xsmall || image.derivatives?.thumb || image.derivatives?.square || null;
    const thumbUrl = normalizeUrl(image.thumbnail_url || bestDerivativeUrl(image.derivatives, ["xsmall", "small", "thumb", "square"]));
    const displayUrl = normalizeUrl(image.display_url || image.element_url || bestDerivativeUrl(image.derivatives, ["xlarge", "large", "medium", "small"]) || thumbUrl);
    const sortOrder = Number(image.sort_order || 0);
    const timestamp = image.date_available ? Date.parse(String(image.date_available).replace(" ", "T")) || 0 : 0;
    return {
      id: image.id,
      key,
      data,
      code,
      tag: isCaaWork ? "中美协展览专项画稿" : categoryName,
      title,
      meta: displayMeta,
      desc: image.comment || "作品已从后台数据库读取。",
      tags: displayMeta.replace(/ \/ /g, "、"),
      categoryTitle: isCaaWork ? "中美协展览专项画稿" : (data.title || categoryName),
      categoryName: isCaaWork ? "中美协展览专项画稿" : categoryName,
      parentName: displayParentName,
      featuredScore: image.featured ? 100000 - sortOrder : 1000 - sortOrder,
      downloadScore: Number(image.download_count || 0),
      hotScore: Number(image.hit || 0) + Number(image.download_count || 0),
      newestScore: timestamp,
      thumbUrl,
      thumbWidth: Number(thumbDerivative?.width || image.width || 4),
      thumbHeight: Number(thumbDerivative?.height || image.height || 3),
      displayUrl,
      raw: image
    };
  }

  async function fetchApiWorks(options, limit) {
    const all = [];
    const maxItems = limit || API_FETCH_LIMIT;
    let page = 0;
    let total = null;
    while (all.length < maxItems) {
      const result = await apiFetch("gzca.images.getList", {
        ...options,
        per_page: API_PAGE_SIZE,
        page
      });
      const images = result.images || [];
      all.push(...images);
      total = Number(result.paging?.total_count ?? images.length);
      if (!images.length || all.length >= total) break;
      page += 1;
      if (page > 20) break;
    }
    return all.slice(0, maxItems).map((image) => normalizeApiWork(image, options?.fallbackKey));
  }

  function workDetailHref(item) {
    const href = detailHref(item.code, item.key);
    return item.id ? href + "&image_id=" + encodeURIComponent(item.id) : href;
  }

  function renderWorkCard(item, detailBase, renderOptions) {
    const options = renderOptions || {};
    const href = item.href || (detailBase ? detailBase(item.code, item.key, item) : workDetailHref(item));
    const imageSrc = item.thumbUrl || imageForCode(item.code);
    const loading = options.eager ? "eager" : "lazy";
    const fetchPriority = options.eager ? "high" : "auto";
    const width = Math.max(1, Number(item.thumbWidth || 4));
    const height = Math.max(1, Number(item.thumbHeight || 3));
    return '<a class="work-card" href="' + escapeHtml(href) + '" data-code="' + escapeHtml(item.code) + '"' + (item.id ? ' data-image-id="' + escapeHtml(item.id) + '"' : "") + '>' +
      '<figure class="work-thumb"><span class="code-badge">' + escapeHtml(item.code) + '</span><img loading="' + loading + '" fetchpriority="' + fetchPriority + '" decoding="async" width="' + width + '" height="' + height + '" data-api-image="' + (item.thumbUrl ? "true" : "false") + '" src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(item.code) + '"></figure>' +
      '<div class="work-caption work-caption-compact"><p class="card-meta">' + escapeHtml(item.meta) + '</p><p class="work-code-line">作品编号：' + escapeHtml(item.code) + '</p></div>' +
    '</a>';
  }

  function renderResultCard(item) {
    const href = workDetailHref(item);
    const imageSrc = item.thumbUrl || imageForCode(item.code);
    return '<article class="result-card" data-code="' + escapeHtml(item.code) + '">' +
      '<a class="work-thumb" href="' + escapeHtml(href) + '"><span class="code-badge">' + escapeHtml(item.code) + '</span><img loading="lazy" decoding="async" data-api-image="' + (item.thumbUrl ? "true" : "false") + '" src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(item.code) + '"></a>' +
      '<div class="work-caption work-caption-compact"><p class="card-meta">' + escapeHtml(item.meta) + '</p><p class="work-code-line">作品编号：' + escapeHtml(item.code) + '</p></div>' +
      '<a class="btn btn-secondary" href="' + escapeHtml(href) + '">查看详情</a>' +
    '</article>';
  }

  function categoryContextFromList(list, requestedKey, requestedId) {
    const matchedCategory = list.find((item) => requestedId && Number(item.id) === Number(requestedId)) || list.find((item) => item.key === requestedKey) || null;
    const byId = new Map(list.map((item) => [Number(item.id), item]));
    const parentApi = matchedCategory?.id_uppercat && byId.has(Number(matchedCategory.id_uppercat)) ? byId.get(Number(matchedCategory.id_uppercat)) : null;
    const caaBoard = isCaaCategory(parentApi || matchedCategory) || isCaaKey(requestedKey);
    const apiCategory = matchedCategory;
    const caaRoot = caaBoard ? (parentApi || matchedCategory) : null;
    const key = apiCategory?.key || (caaBoard ? "caa-exhibitions" : requestedKey);
    const data = categories[key] || localDataForWork(apiCategory, key);
    const caaChild = caaBoard && Boolean(parentApi);
    return {
      key,
      data,
      apiCategory,
      parentApi,
      isCaaBoard: caaBoard,
      parentName: caaBoard ? (caaRoot?.name || "中美协展览专项画稿") : (data.parent || parentApi?.name || apiCategory?.name || "作品分类"),
      name: caaBoard ? (caaChild ? (apiCategory?.name || data.name) : "全部作品") : (data.direct ? "全部作品" : (data.name || apiCategory?.name || "作品")),
      title: caaBoard ? (caaChild ? (apiCategory?.name || data.title) : (caaRoot?.name || "中美协展览专项画稿")) : (data.title || apiCategory?.name || "作品分类"),
      desc: apiCategory?.comment || data.desc || "后台分类作品。",
      coverUrl: apiCategory?.tn_url || ""
    };
  }

  async function getCategoryContext() {
    const requestedKey = params.get("cat") || "ink-landscape";
    const requestedId = Number(params.get("cat_id") || 0);
    let list = [];
    try {
      list = await getApiCategories();
    } catch (error) {
      console.warn("Guozhan category API unavailable", error);
    }
    return categoryContextFromList(list, requestedKey, requestedId);
  }

  async function mountHomeStream() {
    if (!body.hasAttribute("data-home-page")) return;
    const grid = document.querySelector("[data-home-stream]");
    const pagination = document.querySelector("[data-home-pagination]");
    const previous = pagination?.querySelector("[data-home-prev]");
    const next = pagination?.querySelector("[data-home-next]");
    const status = pagination?.querySelector("[data-home-status]");
    if (!grid || !pagination || !previous || !next || !status) return;
    let activeSort = document.querySelector('[data-home-sort][aria-pressed="true"]')?.getAttribute("data-home-sort") || "featured";
    let pageNumber = 0;
    let totalCount = 0;
    let totalPages = 1;
    let loading = false;
    let generation = 0;

    function updatePagination(message) {
      status.textContent = message || ("第 " + (pageNumber + 1) + " / " + totalPages + " 页 · 共 " + totalCount + " 张");
      previous.disabled = loading || pageNumber <= 0;
      next.disabled = loading || pageNumber >= totalPages - 1;
      pagination.setAttribute("aria-busy", loading ? "true" : "false");
    }

    async function loadPage(targetPage, scrollToGrid) {
      const expectedGeneration = ++generation;
      let finalMessage = "";
      loading = true;
      grid.setAttribute("aria-busy", "true");
      updatePagination("正在加载第 " + (targetPage + 1) + " 页…");

      try {
        const result = await apiFetch("gzca.images.getList", {
          sort: activeSort,
          per_page: HOME_PAGE_SIZE,
          page: targetPage
        });
        if (expectedGeneration !== generation) return;

        const items = (result.images || []).map((image) => normalizeApiWork(image));
        pageNumber = targetPage;
        totalCount = Number(result.paging?.total_count ?? items.length);
        totalPages = Math.max(1, Math.ceil(totalCount / HOME_PAGE_SIZE));
        if (!items.length) {
          grid.innerHTML = '<div class="empty-state">作品正在整理中</div>';
        } else {
          grid.innerHTML = items.map((item, index) => renderWorkCard(item, null, {
            eager: index < 4
          })).join("");
        }
        if (scrollToGrid) grid.scrollIntoView({ behavior: "smooth", block: "start" });
      } catch (error) {
        if (expectedGeneration !== generation) return;
        console.error("Guozhan works API unavailable", error);
        grid.innerHTML = '<div class="empty-state">作品加载失败，请稍后重试。</div>';
        finalMessage = "作品加载失败";
      } finally {
        if (expectedGeneration === generation) {
          loading = false;
          grid.setAttribute("aria-busy", "false");
          updatePagination(finalMessage);
        }
      }
    }

    document.querySelectorAll("[data-home-sort]").forEach((button) => {
      button.addEventListener("click", () => {
        const groupNode = button.closest("[data-home-sort-group]");
        if (groupNode) groupNode.querySelectorAll("[data-home-sort]").forEach((item) => item.setAttribute("aria-pressed", "false"));
        button.setAttribute("aria-pressed", "true");
        activeSort = button.getAttribute("data-home-sort") || "featured";
        loadPage(0, false);
      });
    });

    previous.addEventListener("click", () => {
      if (loading || pageNumber <= 0) return;
      loadPage(pageNumber - 1, true);
    });
    next.addEventListener("click", () => {
      if (loading || pageNumber >= totalPages - 1) return;
      loadPage(pageNumber + 1, true);
    });

    loadPage(0, false);
  }

  async function applyCategoryPage() {
    if (!body.hasAttribute("data-category-page")) return;
    const context = await getCategoryContext();
    const filterRow = document.querySelector("[data-filter-group]");
    const sortSelect = document.querySelector("[data-sort-select]");
    const filters = context.isCaaBoard ? [] : (context.data.direct ? ["全部"] : ["全部"].concat(context.data.tags || []));
    let allWorks = [];
    let currentSort = sortSelect?.value || "custom";
    let reloadGeneration = 0;

    document.title = context.title + " · " + BRAND_NAME;
    document.querySelectorAll("[data-category-parent]").forEach((node) => { node.textContent = context.parentName; });
    document.querySelectorAll("[data-category-name]").forEach((node) => { node.textContent = context.name; });
    document.querySelectorAll("[data-category-title]").forEach((node) => { node.textContent = context.title; });
    document.querySelectorAll("[data-category-desc]").forEach((node) => { node.textContent = context.desc; });
    const pageHero = document.querySelector("body[data-category-page] .page-hero");
    if (pageHero && context.coverUrl) {
      pageHero.style.setProperty("--page-hero-image", 'url("' + cleanCssUrl(normalizeUrl(context.coverUrl)) + '")');
      pageHero.classList.add("has-category-cover");
    }

    if (filterRow) {
      filterRow.hidden = true;
      filterRow.innerHTML = "";
    }

    const pager = createPagedRenderer({
      container: document.querySelector("[data-category-gallery]"),
      pagination: document.querySelector("[data-pagination]"),
      pageSize: 16,
      renderItem: (item) => renderWorkCard(item),
      emptyMessage: context.data.reserved ? "该专项展览作品正在整理中" : "未找到符合当前条件的作品",
      unit: "张"
    });

    function update() {
      const active = "全部";
      const filtered = allWorks;
      const sorted = sortedWorks(filtered, currentSort);
      pager.setItems(sorted);
      const status = document.querySelector("[data-filter-status]");
      if (status) {
        const singleNote = sorted.length <= 1 ? "（当前作品数量不足，排序结果不会明显变化）" : "";
        status.textContent = "当前查看：" + (active || "全部") + " · 排序：" + sortLabel(currentSort) + singleNote;
      }
    }

    async function reload() {
      const expectedGeneration = ++reloadGeneration;
      const requestedSort = sortSelect?.value || "custom";
      currentSort = requestedSort;
      update();
      try {
        const query = { sort: requestedSort, recursive: true, fallbackKey: context.key };
        if (context.apiCategory?.id) {
          query.cat_id = context.apiCategory.id;
        }
        else if (context.isCaaBoard) query.query = "ZX-";
        else query.query = context.title;
        const works = await fetchApiWorks(query, 100);
        if (expectedGeneration !== reloadGeneration) return;
        allWorks = works;
      } catch (error) {
        if (expectedGeneration !== reloadGeneration) return;
        console.error("Guozhan category works API unavailable", error);
        allWorks = [];
      }
      update();
    }

    if (filterRow) {
      filterRow.querySelectorAll("[data-filter]").forEach((button) => {
        button.addEventListener("click", () => {
          filterRow.querySelectorAll("[data-filter]").forEach((item) => item.setAttribute("aria-pressed", "false"));
          button.setAttribute("aria-pressed", "true");
          update();
        });
      });
    }
    sortSelect?.addEventListener("change", reload);
    await reload();
  }

  async function applySearchPage() {
    if (!body.hasAttribute("data-search-page")) return;
    const query = (params.get("q") || "").trim();
    document.querySelectorAll("[data-search-term]").forEach((node) => { node.textContent = query || "全部作品"; });
    const input = document.querySelector("[data-search-form] input[name='q']");
    if (input) input.value = query;
    const pager = createPagedRenderer({
      container: document.querySelector("[data-search-results]"),
      pagination: document.querySelector("[data-pagination]"),
      pageSize: 16,
      renderItem: renderResultCard,
      emptyMessage: query ? "未找到该作品，可能已下架或不存在" : "暂无可展示作品",
      unit: "条"
    });
    try {
      const works = await fetchApiWorks({ query, sort: query ? "custom" : "featured" }, 100);
      pager.setItems(works);
    } catch (error) {
      console.error("Guozhan search API unavailable", error);
      pager.setItems([]);
    }
  }

  function setTextAll(selector, value) {
    document.querySelectorAll(selector).forEach((node) => { node.textContent = value; });
  }

  function showUnavailableDetail(code) {
    const displayCode = code || "未指定";
    document.title = "作品已下架或不存在 · " + BRAND_NAME;
    setTextAll("[data-detail-code]", displayCode);
    setTextAll("[data-detail-title]", "作品已下架或不存在");
    setTextAll("[data-detail-category]", "不可用");
    setTextAll("[data-detail-category-title]", "作品不可用");
    setTextAll("[data-detail-tags]", "已下架、未公开或编号不存在");
    setTextAll("[data-detail-desc]", "该作品当前未在前台公开展示。如需确认，请联系管理员在后台检查作品状态。");
    const mainImage = document.querySelector(".detail-art img");
    if (mainImage) {
      mainImage.src = assetPrefix() + "art-placeholder.svg";
      mainImage.alt = "作品不可用";
      mainImage.removeAttribute("data-api-image");
    }
    document.querySelectorAll("[data-detail-category-link], [data-related-more], [data-back-list]").forEach((link) => link.setAttribute("href", appHref("categories")));
    const relatedGrid = document.querySelector("[data-related-grid]");
    if (relatedGrid) relatedGrid.innerHTML = '<div class="empty-state">该作品当前未公开展示，暂无同类推荐。</div>';
  }

  async function fetchDetailWork() {
    const requestedId = params.get("image_id");
    const requestedCode = (params.get("code") || "").trim().toUpperCase();
    const fallbackKey = params.get("cat") || "";
    if (requestedId && /^\d+$/.test(requestedId)) {
      try {
        const detail = await apiFetch("gzca.images.getInfo", { image_id: requestedId });
        return normalizeApiWork(detail, fallbackKey);
      } catch (error) {
        console.warn("Guozhan detail by id unavailable", error);
      }
    }
    if (!requestedCode) return null;
    const result = await apiFetch("gzca.images.getList", { query: requestedCode, per_page: API_PAGE_SIZE, page: 0 });
    const images = result.images || [];
    const exact = images.find((image) => String(image.gzca_code || "").toUpperCase() === requestedCode) || null;
    if (!exact) return null;
    if (exact.id) {
      try {
        const detail = await apiFetch("gzca.images.getInfo", { image_id: exact.id });
        return normalizeApiWork(detail, fallbackKey);
      } catch (error) {
        console.warn("Guozhan detail fallback to list item", error);
      }
    }
    return normalizeApiWork(exact, fallbackKey);
  }

  async function applyDetailPage() {
    if (!body.hasAttribute("data-detail-page")) return;
    const requestedCode = (params.get("code") || "").trim().toUpperCase();
    try {
      const work = await fetchDetailWork();
      if (!work) {
        showUnavailableDetail(requestedCode);
        return;
      }
      document.title = work.title + " · " + work.code;
      setTextAll("[data-detail-code]", work.code);
      setTextAll("[data-detail-title]", work.title);
      setTextAll("[data-detail-category]", work.parentName + " / " + work.categoryName);
      setTextAll("[data-detail-category-title]", work.categoryTitle);
      setTextAll("[data-detail-tags]", work.tags || work.meta);
      setTextAll("[data-detail-desc]", work.desc || "作品已从后台数据库读取。");
      const mainImage = document.querySelector(".detail-art img");
      if (mainImage) {
        mainImage.src = work.displayUrl || work.thumbUrl || imageForCode(work.code);
        mainImage.alt = work.title;
        mainImage.setAttribute("data-api-image", work.displayUrl || work.thumbUrl ? "true" : "false");
      }
      document.querySelectorAll("[data-detail-category-link], [data-related-more], [data-back-list]").forEach((link) => link.setAttribute("href", categoryListHref(work.key)));
      const relatedTitle = document.querySelector("[data-related-title]");
      if (relatedTitle) relatedTitle.textContent = work.categoryTitle + "推荐";
      const relatedGrid = document.querySelector("[data-related-grid]");
      if (relatedGrid) {
        relatedGrid.innerHTML = '<div class="empty-state">正在读取同类作品...</div>';
        try {
          const apiCategory = (await getApiCategories()).find((item) => item.key === work.key);
          const related = await fetchApiWorks({ cat_id: apiCategory?.id, query: apiCategory ? "" : work.categoryName, sort: "featured", fallbackKey: work.key }, 9);
          const filtered = related.filter((item) => String(item.id) !== String(work.id) && item.code !== work.code).slice(0, 8);
          relatedGrid.innerHTML = filtered.length ? filtered.map((item) => renderWorkCard(item)).join("") : '<div class="empty-state">暂无同类推荐作品。</div>';
        } catch (error) {
          console.warn("Guozhan related API unavailable", error);
          relatedGrid.innerHTML = '<div class="empty-state">暂无同类推荐作品。</div>';
        }
      }
    } catch (error) {
      console.error("Guozhan detail API unavailable", error);
      showUnavailableDetail(requestedCode);
    }
  }

  renderStaticDirectory();
  renderStaticSideNav();
  mountHomeStream();
  applyCategoryPage();
  applySearchPage();
  applyDetailPage();
  hydrateWorkImages();
  applyBrand();
  applyHomeSettings();
  applyStaticNavigation();
  wireCommon();
})();
