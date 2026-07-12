(function () {
  const body = document.body;
  const params = new URLSearchParams(window.location.search);
  const BRAND_NAME = "图物计划国展素材馆";
  const BRAND_EN = "AI Graphics Learning Plan";
  const WECHAT = "aiguozhanhuihua";
  const isPiwigo = Boolean(window.GUOZHAN_PIWIGO);

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
    group("油画", "覆盖人物、风景、静物三类常用题材，便于按画面结构和比赛方向筛选。", [["人物画", "YH-RW", "oil-figure"], ["风景画", "YH-FJ", "oil-landscape"], ["静物画", "YH-JW", "oil-still"]]),
    group("版画", "整理黑白木刻、木刻水印、丝网版画等作品，突出版种、构成与视觉语言。", [["黑白木刻", "BH-HB", "print-black-woodcut"], ["木刻水印", "BH-SY", "print-watermark"], ["丝网版画", "BH-SW", "print-screen"]]),
    group("雕塑", "按圆雕、浮雕归档，适合查找空间造型、人物动物与装饰性题材。", [["圆雕", "DS-YD", "sculpture-round"], ["浮雕", "DS-FD", "sculpture-relief"]]),
    group("漆画", "围绕人物、风景、静物整理，突出漆艺肌理、色层和装饰表达。", [["人物", "QH-RW", "lacquer-figure"], ["风景", "QH-FJ", "lacquer-landscape"], ["静物", "QH-JW", "lacquer-still"]]),
    group("水彩", "涵盖人物、风景、静物方向，便于查找透明水色、光影和构图参考。", [["人物", "SC-RW", "watercolor-figure"], ["风景", "SC-FJ", "watercolor-landscape"], ["静物", "SC-JW", "watercolor-still"]]),
    group("农民画", "集中呈现乡土生活、节庆民俗与民间叙事题材作品。", [["查看全部作品", "NM-ZH", "folk-art"]], true),
    group("漫画", "集中呈现叙事表达、人物场景与主题创意类漫画作品。", [["查看全部作品", "MH-ZH", "comic"]], true),
    group("插画", "按绘画风格与数字插画归档，覆盖人物、场景和视觉概念方向。", [["绘画风格插画", "CH-HH", "illustration-painted"], ["数字插画", "CH-SZ", "illustration-digital"]]),
    group("中美协展览专项画稿", "按展览征稿与专项主题归档，便于围绕指定题材集中选图。", [
      ["梅花之韵——2026·中国画花鸟作品展", "ZX-MH", "caa-plum-blossom"],
      ["山水滋美——2026风景油画展", "ZX-YS", "caa-landscape-oil"],
      ["第五届青年漆画展览征稿通知", "ZX-QH", "caa-young-lacquer"],
      ["未知画展 01", "ZX-W1", "caa-unknown-01"],
      ["未知画展 02", "ZX-W2", "caa-unknown-02"],
      ["未知画展 03", "ZX-W3", "caa-unknown-03"],
      ["未知画展 04", "ZX-W4", "caa-unknown-04"],
      ["未知画展 05", "ZX-W5", "caa-unknown-05"],
      ["未知画展 06", "ZX-W6", "caa-unknown-06"],
      ["未知画展 07", "ZX-W7", "caa-unknown-07"]
    ])
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
        : "收录“" + title + "”方向作品，按编号、题材标签与作品名称归档，适合用于选图、比稿和参赛资料整理。"
    };
  }

  function group(title, desc, links, direct) {
    return { title, desc, links, direct: Boolean(direct) };
  }

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
      if (image && code) image.src = imageForCode(code);
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
          renderItem: (entry) => directoryCardMarkup(entry.item, entry.index),
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

  async function copyText(value, successMessage) {
    try {
      await navigator.clipboard.writeText(value);
      showToast(successMessage);
    } catch (error) {
      showToast("当前浏览器不支持自动复制");
    }
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
      if (event.key === "Escape") closeModal();
    });

    const modal = document.querySelector("[data-contact-modal]");
    if (modal) modal.addEventListener("click", (event) => {
      if (event.target === modal) closeModal();
    });

    document.querySelectorAll("[data-copy-wechat]").forEach((button) => {
      if (!button.getAttribute("data-copy-wechat")) button.setAttribute("data-copy-wechat", WECHAT);
      button.addEventListener("click", () => copyText(button.getAttribute("data-copy-wechat") || WECHAT, "已复制微信号"));
    });

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
        const codeInfo = workCodeInfoFromValue(value);
        if (!isPiwigo && codeInfo) {
          event.preventDefault();
          window.location.href = detailHref(codeInfo.code, codeInfo.key);
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
      '<div class="work-caption"><h3>' + item.title + '</h3><p class="card-meta">' + item.meta + '</p><p class="work-code-line">作品编号：' + item.code + '</p></div>' +
    '</a>';
  }

  function sortedWorks(works, sortType) {
    const list = works.slice();
    if (sortType === "downloads") return list.sort((a, b) => b.downloadScore - a.downloadScore || a.code.localeCompare(b.code));
    if (sortType === "hot") return list.sort((a, b) => b.hotScore - a.hotScore || a.code.localeCompare(b.code));
    if (sortType === "newest") return list.sort((a, b) => b.newestScore - a.newestScore || a.code.localeCompare(b.code));
    if (sortType === "code") return list.sort((a, b) => a.code.localeCompare(b.code));
    return list.sort((a, b) => b.featuredScore - a.featuredScore || a.code.localeCompare(b.code));
  }

  function mountHomeStream() {
    if (!body.hasAttribute("data-home-page") && !body.hasAttribute("data-competition-page")) return;
    const grid = document.querySelector("[data-home-stream]");
    const pagination = document.querySelector("[data-home-pagination]");
    if (!grid || !pagination) return;
    const isCompetitionPage = body.hasAttribute("data-competition-page");
    const competitionGroups = [
      { label: "水墨", keys: ["ink-landscape"] },
      { label: "油画", keys: ["oil-landscape"] },
      { label: "版画", keys: ["print-black-woodcut"] },
      { label: "水彩", keys: ["watercolor-landscape"] }
    ];
    const competitionTypeForKey = (key) => competitionGroups.find((item) => item.keys.includes(key))?.label || "";
    const streamKeys = isCompetitionPage
      ? competitionGroups.flatMap((item) => item.keys)
      : homeStreamKeys;
    const perStreamKey = Math.max(1, Math.ceil(80 / streamKeys.length));
    const works = streamKeys.flatMap((key, keyIndex) => {
      return Array.from({ length: perStreamKey }, (_, serialIndex) => {
        const orderIndex = keyIndex * perStreamKey + serialIndex;
        const work = buildWork(categories[key], key, serialIndex, orderIndex);
        if (work) work.competitionType = competitionTypeForKey(key);
        return work;
      });
    }).filter(Boolean).slice(0, 80);
    const pager = createPagedRenderer({ container: grid, pagination, pageSize: 16, renderItem: (item) => renderWorkCard(item), unit: "张" });
    let activeSort = document.querySelector('[data-home-sort][aria-pressed="true"]')?.getAttribute("data-home-sort") || "featured";
    let activeFilter = "全部";

    function apply() {
      const filtered = isCompetitionPage && activeFilter !== "全部"
        ? works.filter((item) => item.competitionType === activeFilter)
        : works;
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
    if (isCompetitionPage) {
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
      filterRow.innerHTML = filters.map((label, index) => '<button class="filter-chip" type="button" aria-pressed="' + (index === 0 ? "true" : "false") + '" data-filter>' + label + '</button>').join("");
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
      const active = filterRow ? filterRow.querySelector('[data-filter][aria-pressed="true"]')?.textContent.trim() : "全部";
      const filtered = active && active !== "全部" ? allWorks.filter((item) => item.tag === active) : allWorks;
      pager.setItems(filtered);
      const status = document.querySelector("[data-filter-status]");
      if (status) status.textContent = "当前查看：" + (active || "全部");
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
      renderItem: (item) => '<article class="result-card" data-code="' + item.code + '"><a class="work-thumb" href="' + detailHref(item.code, item.key) + '"><span class="code-badge">' + item.code + '</span><img loading="lazy" decoding="async" src="' + imageForCode(item.code) + '" alt="' + item.title + '"></a><div><h3>' + item.title + '</h3><p class="card-meta">' + item.meta + '</p><p class="work-code-line">作品编号：' + item.code + '</p></div><a class="btn btn-secondary" href="' + detailHref(item.code, item.key) + '">查看详情</a></article>',
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
    const related = buildWorks(data, key, 7).filter((item) => item.code !== code).slice(0, 6);
    const relatedGrid = document.querySelector("[data-related-grid]");
    if (relatedGrid) relatedGrid.innerHTML = related.map((item) => renderWorkCard(item)).join("");
  }

  function applyBrand() {
    document.querySelectorAll(".brand-mark").forEach((node) => {
      const src = assetPrefix() + "logo-mark.png?v=20260712-logo-fulltext";
      const image = node.querySelector("img") || document.createElement("img");
      image.src = src;
      image.alt = "";
      if (!image.parentNode) {
        node.textContent = "";
        node.appendChild(image);
      }
    });
    document.querySelectorAll(".brand-text strong").forEach((node) => { node.textContent = BRAND_NAME; });
    document.querySelectorAll(".brand-text > span").forEach((node) => { node.textContent = BRAND_EN; });
    document.querySelectorAll("[data-footer-brand], .footer-inner span:first-child").forEach((node) => { node.textContent = "© " + BRAND_NAME; });
    document.querySelectorAll("[data-contact-wechat], .contact-band h3").forEach((node) => { node.textContent = WECHAT; });
  }

  function applyStaticNavigation() {
    if (isPiwigo) return;
    const nav = document.querySelector("[data-nav]");
    if (!nav) return;
    const active = body.hasAttribute("data-home-page") ? "home"
      : body.hasAttribute("data-categories-page") || body.hasAttribute("data-category-page") || body.hasAttribute("data-detail-page") ? "categories"
      : body.hasAttribute("data-competition-page") ? "competition"
      : body.hasAttribute("data-search-page") ? "search"
      : body.hasAttribute("data-contact-page") ? "contact"
      : "";
    const links = [
      ["home", "首页", appHref("home")],
      ["categories", "作品分类", appHref("categories")],
      ["competition", "比赛", appHref("competition")],
      ["search", "搜索", appHref("search")],
      ["contact", "联系客服", appHref("contact")]
    ];
    nav.innerHTML = links.map((item) => '<a href="' + item[2] + '"' + (item[0] === active ? ' aria-current="page"' : "") + '>' + item[1] + '</a>').join("");
  }

  if (!isPiwigo) {
    renderStaticDirectory();
    renderStaticSideNav();
    mountHomeStream();
    applyCategoryPage();
    applySearchPage();
    applyDetailPage();
    hydrateWorkImages();
    applyBrand();
    applyStaticNavigation();
  }
  wireCommon();
})();
