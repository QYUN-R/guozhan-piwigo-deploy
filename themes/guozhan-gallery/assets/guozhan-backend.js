(function () {
  var app = document.getElementById("guozhan-app");
  if (!app || !window.fetch) return;

  var root = window.GUOZHAN_ASSET_BASE || "/themes/guozhan-gallery/assets/";
  var params = new URLSearchParams(window.location.search);
  var nativeRoot = document.getElementById("pwg-native");
  var siteRoot = new URL(window.GUOZHAN_ROOT_URL || "./", window.location.href);
  siteRoot.search = "";
  siteRoot.hash = "";
  if (siteRoot.pathname.slice(-1) !== "/") siteRoot.pathname += "/";

  function siteUrl(path) {
    return new URL(String(path || "").replace(/^\//, ""), siteRoot).toString();
  }

  function appUrl(page, values) {
    if (window.GUOZHAN_APP_URL) return window.GUOZHAN_APP_URL(page, values || {});
    var query = new URLSearchParams(values || {});
    if (page && page !== "home") query.set("gz_page", page);
    var text = query.toString();
    return siteRoot.pathname + (text ? "?" + text : "");
  }

  function escapeHtml(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function stripHtml(value) {
    return String(value || "")
      .replace(/<[^>]*>/g, " ")
      .replace(/\s+/g, " ")
      .trim();
  }

  function ws(method, values) {
    var query = new URLSearchParams(values || {});
    query.set("format", "json");
    query.set("method", method);
    return fetch(siteUrl("ws.php") + "?" + query.toString(), { credentials: "same-origin" })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.stat !== "ok") throw new Error(json && json.message || "Piwigo API error");
        return json.result || {};
      });
  }

  function categoryIdFromUrl(url) {
    var text = String(url || window.location.href);
    var match = text.match(/\/category\/(\d+)/) || text.match(/[?&]cat_id=(\d+)/);
    return match ? Number(match[1]) : null;
  }

  function pictureIdFromUrl(url) {
    var text = String(url || window.location.href);
    var match = text.match(/picture\.php\?\/(\d+)/) || text.match(/\/picture\/(\d+)/) || text.match(/[?&](?:image_id|id)=(\d+)/);
    return match ? Number(match[1]) : null;
  }

  function codeFromUrl(url) {
    try {
      return new URL(String(url || window.location.href), window.location.href).searchParams.get("code") || "";
    } catch (error) {
      var text = String(url || window.location.href);
      var match = text.match(/[?&]code=([^&#]+)/);
      return match ? decodeURIComponent(match[1].replace(/\+/g, " ")) : "";
    }
  }

  function isTopCategory(cat) {
    return cat && (cat.id_uppercat === null || cat.id_uppercat === undefined || cat.id_uppercat === "");
  }

  function sameId(a, b) {
    return String(a) === String(b);
  }

  function categoryDisplayName(cat) {
    return stripHtml(cat && cat.name || "").replace(/\s+/g, "");
  }

  function namedCategoryRank(name) {
    if (/梅花之韵|中国画花鸟作品展|梅花/.test(name)) return 10;
    if (/山水滋美|风景油画展/.test(name)) return 20;
    if (/第五届青年漆画|青年漆画/.test(name)) return 30;
    return null;
  }

  function unknownCategoryNumber(name) {
    var match = name.match(/未知画展0*(\d+)/);
    return match ? parseInt(match[1], 10) : null;
  }

  function categoryOrderValue(cat) {
    var name = categoryDisplayName(cat);
    var namedRank = namedCategoryRank(name);
    if (namedRank !== null) return [0, namedRank, name];
    var unknownNo = unknownCategoryNumber(name);
    if (unknownNo !== null) return [1, unknownNo, name];
    var rank = Number(cat && cat.rank);
    return [2, Number.isFinite(rank) && rank > 0 ? rank : 9999, name];
  }

  function orderedCategories(items) {
    return (items || []).slice().sort(function (a, b) {
      var left = categoryOrderValue(a);
      var right = categoryOrderValue(b);
      return left[0] - right[0] || left[1] - right[1] || left[2].localeCompare(right[2], "zh-Hans-CN");
    });
  }

  function childCategories(categories, parentId) {
    return orderedCategories(categories.filter(function (cat) { return sameId(cat.id_uppercat, parentId); }));
  }

  function topCategories(categories) {
    return orderedCategories(categories.filter(isTopCategory));
  }

  function thumbnailImageUrl(image) {
    var d = image && image.derivatives || {};
    return (image && image.thumbnail_url) ||
      (d.xsmall && d.xsmall.url) ||
      (d.thumb && d.thumb.url) ||
      (d.square && d.square.url) ||
      root + "art-placeholder.svg";
  }

  function detailImageUrl(image) {
    var d = image && image.derivatives || {};
    return (image && image.display_url) ||
      (image && image.element_url) ||
      (d.xxlarge && d.xxlarge.url) ||
      (d.xlarge && d.xlarge.url) ||
      (d.large && d.large.url) ||
      (d.medium && d.medium.url) ||
      thumbnailImageUrl(image);
  }

  function imageCode(image) {
    image = image || {};
    if (image && image.gzca_code) return String(image.gzca_code).toUpperCase();
    var source = [image.name, image.comment, image.file, image.id].join(" ");
    var match = source.match(/[A-Z]{2,}(?:-[A-Z0-9]+)+-\d{3,}/i);
    return match ? match[0].toUpperCase() : "ID-" + image.id;
  }

  function imageTitle(image) {
    return stripHtml(image.name || image.file || ("作品 " + image.id));
  }

  function imageMeta(image, fallback) {
    var comment = stripHtml(image.comment || "");
    if (comment) return comment;
    if (image && image.category && image.category.name) return stripHtml(image.category.name);
    return fallback || "作品素材";
  }

  function renderWorkCard(image, fallbackMeta) {
    var code = imageCode(image);
    var title = imageTitle(image);
    return '<a class="work-card" href="' + escapeHtml(detailHref(image)) + '" data-code="' + escapeHtml(code) + '">' +
      '<figure class="work-thumb"><span class="code-badge">' + escapeHtml(code) + '</span><img loading="lazy" decoding="async" src="' + escapeHtml(thumbnailImageUrl(image)) + '" alt="' + escapeHtml(title) + '"></figure>' +
      '<div class="work-caption"><h3>' + escapeHtml(title) + '</h3><p class="card-meta">' + escapeHtml(imageMeta(image, fallbackMeta)) + '</p><p class="work-code-line">作品编码：' + escapeHtml(code) + '</p></div>' +
    '</a>';
  }

  function renderResultCard(image, fallbackMeta) {
    var code = imageCode(image);
    var title = imageTitle(image);
    var href = detailHref(image);
    return '<article class="result-card" data-code="' + escapeHtml(code) + '">' +
      '<a class="work-thumb" href="' + escapeHtml(href) + '"><span class="code-badge">' + escapeHtml(code) + '</span><img loading="lazy" decoding="async" src="' + escapeHtml(thumbnailImageUrl(image)) + '" alt="' + escapeHtml(title) + '"></a>' +
      '<div><h3>' + escapeHtml(title) + '</h3><p class="card-meta">' + escapeHtml(imageMeta(image, fallbackMeta)) + '</p><p class="work-code-line">作品编码：' + escapeHtml(code) + '</p></div>' +
      '<a class="btn btn-secondary" href="' + escapeHtml(href) + '">查看详情</a>' +
    '</article>';
  }

  function detailHref(image) {
    if (image && image.id) return appUrl("detail", { image_id: image.id });
    if (image && image.page_url) return image.page_url;
    return appUrl("detail", { code: imageCode(image) });
  }

  function categoryHref(cat) {
    return cat && cat.id ? appUrl("category", { cat_id: cat.id }) : appUrl("categories");
  }

  function createPageJump(pagination, onJump) {
    if (!pagination || typeof onJump !== "function") return null;
    var form = pagination.querySelector("[data-page-jump]");
    if (!form) {
      form = document.createElement("form");
      form.className = "page-jump";
      form.setAttribute("data-page-jump", "");
      form.innerHTML = '<label><span>跳到</span><input class="page-jump-input" data-page-jump-input type="number" min="1" step="1" inputmode="numeric" aria-label="跳转页码"><span>页</span></label><button class="btn btn-secondary page-jump-button" type="submit" data-page-jump-submit>跳转</button>';
      pagination.appendChild(form);
    }
    var input = form.querySelector("[data-page-jump-input]");
    var submit = form.querySelector("[data-page-jump-submit]");
    if (!input || !submit) return null;

    form.onsubmit = function (event) {
      event.preventDefault();
      var maxPage = Math.max(1, parseInt(input.max, 10) || 1);
      var rawPage = parseInt(input.value, 10);
      if (!Number.isFinite(rawPage)) {
        input.focus();
        return;
      }
      var targetPage = Math.min(maxPage, Math.max(1, rawPage));
      input.value = String(targetPage);
      onJump(targetPage - 1);
    };

    return {
      update: function (currentPage, totalPages, disabled) {
        var safeTotal = Math.max(1, Number(totalPages) || 1);
        var safeCurrent = Math.min(safeTotal, Math.max(1, Number(currentPage) + 1 || 1));
        input.max = String(safeTotal);
        if (document.activeElement !== input) input.value = String(safeCurrent);
        input.disabled = Boolean(disabled) || safeTotal <= 1;
        submit.disabled = Boolean(disabled) || safeTotal <= 1;
      }
    };
  }

  function renderPager(container, pagination, items, pageSize, renderItem, unit) {
    if (!container || !pagination) return;
    var page = 0;
    var prev = pagination.querySelector("[data-home-prev], [data-page-prev]");
    var next = pagination.querySelector("[data-home-next], [data-page-next]");
    var status = pagination.querySelector("[data-home-status], [data-page-status]");
    var afterRender = typeof arguments[6] === "function" ? arguments[6] : null;
    var jump = createPageJump(pagination, function (targetPage) {
      page = targetPage;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    });

    function draw() {
      var total = Math.max(1, Math.ceil(items.length / pageSize));
      page = Math.min(Math.max(page, 0), total - 1);
      var start = page * pageSize;
      var current = items.slice(start, start + pageSize);
      container.innerHTML = current.length ? current.map(renderItem).join("") : '<div class="empty-state">该栏目作品正在整理中</div>';
      if (afterRender) afterRender(container);
      if (status) status.textContent = "第 " + (page + 1) + " / " + total + " 页 · 共 " + items.length + " " + (unit || "张");
      if (prev) prev.disabled = page === 0;
      if (next) next.disabled = page >= total - 1;
      if (jump) jump.update(page, total, false);
    }

    if (prev) prev.onclick = function () {
      if (page <= 0) return;
      page -= 1;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    };
    if (next) next.onclick = function () {
      var total = Math.max(1, Math.ceil(items.length / pageSize));
      if (page >= total - 1) return;
      page += 1;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    };

    draw();
  }

  function createRemotePager(container, pagination, pageSize, renderItem, unit) {
    if (!container || !pagination) return null;
    var page = 0;
    var totalPages = 1;
    var loader = null;
    var requestId = 0;
    var fallbackHtml = container.innerHTML;
    var prev = pagination.querySelector("[data-home-prev], [data-page-prev]");
    var next = pagination.querySelector("[data-home-next], [data-page-next]");
    var status = pagination.querySelector("[data-home-status], [data-page-status]");
    var jump = createPageJump(pagination, function (targetPage) {
      page = targetPage;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    });

    function draw() {
      if (!loader) return Promise.resolve();
      var currentRequest = ++requestId;
      container.setAttribute("aria-busy", "true");
      container.innerHTML = '<div class="empty-state">正在整理作品列表...</div>';
      if (jump) jump.update(page, totalPages, true);
      return loader(page, pageSize).then(function (result) {
        if (currentRequest !== requestId) return;
        var items = result && result.images || [];
        var paging = result && result.paging || {};
        var totalCount = Number(paging.total_count == null ? items.length : paging.total_count);
        totalPages = Math.max(1, Math.ceil(totalCount / pageSize));
        page = Math.min(page, totalPages - 1);
        if (items.length) {
          container.innerHTML = items.map(renderItem).join("");
        } else if (fallbackHtml && fallbackHtml.trim()) {
          container.innerHTML = fallbackHtml;
        } else {
          container.innerHTML = '<div class="empty-state">未找到符合条件的作品</div>';
        }
        container.removeAttribute("aria-busy");
        if (status) status.textContent = "第 " + (page + 1) + " / " + totalPages + " 页 · 共 " + totalCount + " " + (unit || "张");
        if (prev) prev.disabled = page === 0;
        if (next) next.disabled = page >= totalPages - 1;
        if (jump) jump.update(page, totalPages, false);
      }).catch(function () {
        if (currentRequest !== requestId) return;
        container.removeAttribute("aria-busy");
        container.innerHTML = '<div class="empty-state">作品列表加载失败，请刷新后重试</div>';
        if (status) status.textContent = "加载失败";
        if (prev) prev.disabled = true;
        if (next) next.disabled = true;
        if (jump) jump.update(page, totalPages, true);
      });
    }

    if (prev) prev.onclick = function () {
      if (page <= 0) return;
      page -= 1;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    };
    if (next) next.onclick = function () {
      if (page >= totalPages - 1) return;
      page += 1;
      draw();
      container.scrollIntoView({ behavior: "smooth", block: "start" });
    };

    return {
      setLoader: function (nextLoader) {
        loader = nextLoader;
        page = 0;
        return draw();
      },
      reload: draw
    };
  }

  function imageScore(image, mode) {
    if (!image) return 0;
    if (mode === "downloads") {
      return Number(image.hit || image.visits || image.downloads || image.id || 0);
    }
    if (mode === "hot") {
      return Number(image.hit || 0) * 2 + Number(image.id || 0);
    }
    var date = Date.parse(image.date_available || image.date_creation || image.date_posted || "");
    return Number.isFinite(date) ? date : Number(image.id || 0);
  }

  function sortedImages(images, mode) {
    return (images || []).slice().sort(function (a, b) {
      return imageScore(b, mode) - imageScore(a, mode) || imageCode(a).localeCompare(imageCode(b));
    });
  }

  function categoryCard(cat, index, children) {
    var childLinks = children.length ? children : [cat];
    var visibleLimit = 3;
    var hiddenCount = Math.max(0, childLinks.length - visibleLimit);
    var primaryHref = categoryHref(cat);
    var cover = cat.tn_url ? '<img class="directory-cover" src="' + escapeHtml(cat.tn_url) + '" alt="" loading="lazy">' : "";
    return '<article class="directory-card" data-index="' + String(index + 1).padStart(2, "0") + '">' +
      '<div class="directory-card-copy">' + cover + '<p class="small">分类入口</p><h3><a class="directory-title-link" href="' + escapeHtml(primaryHref) + '">' + escapeHtml(cat.name) + '</a></h3><p class="muted">' + escapeHtml(stripHtml(cat.comment) || ("共 " + (cat.total_nb_images || cat.nb_images || 0) + " 张作品")) + '</p></div>' +
      '<div class="sub-links">' + childLinks.map(function (item, itemIndex) {
        return '<a class="sub-link' + (itemIndex >= visibleLimit ? ' is-extra' : '') + '" href="' + escapeHtml(categoryHref(item)) + '">' + escapeHtml(item.name) + ' <span>' + escapeHtml((item.total_nb_images || item.nb_images || 0) + "张") + '</span></a>';
      }).join("") + (hiddenCount ? '<button class="sub-more-button" type="button" data-sub-toggle aria-expanded="false">展开更多 ' + hiddenCount + '</button>' : "") + '</div>' +
    '</article>';
  }

  function wireSubCategoryToggles(root) {
    (root || app).querySelectorAll("[data-sub-toggle]").forEach(function (button) {
      if (button.getAttribute("data-toggle-ready") === "true") return;
      button.setAttribute("data-toggle-ready", "true");
      button.addEventListener("click", function () {
        var card = button.closest(".directory-card");
        if (!card) return;
        var expanded = card.classList.toggle("is-expanded");
        button.setAttribute("aria-expanded", String(expanded));
        button.textContent = expanded ? "收起" : "展开更多 " + card.querySelectorAll(".sub-link.is-extra").length;
      });
    });
  }

  function compactCategoryCard(cat, index) {
    var primaryHref = categoryHref(cat);
    return '<a class="quick-category-card" href="' + escapeHtml(primaryHref) + '">' +
      (cat.tn_url ? '<img class="quick-category-cover" src="' + escapeHtml(cat.tn_url) + '" alt="" loading="lazy">' : "") +
      '<span class="quick-category-index">' + String(index + 1).padStart(2, "0") + '</span>' +
      '<strong>' + escapeHtml(cat.name) + '</strong>' +
      '<small>' + escapeHtml((cat.total_nb_images || cat.nb_images || 0) + " 张作品") + '</small>' +
    '</a>';
  }

  function renderDirectory(categories) {
    var directory = app.querySelector(".directory");
    if (!directory || !categories.length) return;
    var tops = topCategories(categories);
    var compact = document.body.hasAttribute("data-home-page");
    directory.classList.toggle("compact-directory", compact);
    directory.classList.toggle("full-directory", !compact);
    if (!compact && app.querySelector("[data-directory-pagination]")) {
      var items = tops.map(function (cat, index) { return { cat: cat, index: index }; });
      renderPager(directory, app.querySelector("[data-directory-pagination]"), items, 8, function (item) {
        return categoryCard(item.cat, item.index, childCategories(categories, item.cat.id));
      }, "类", wireSubCategoryToggles);
    } else {
      directory.innerHTML = tops.map(function (cat, index) {
        return compact ? compactCategoryCard(cat, index) : categoryCard(cat, index, childCategories(categories, cat.id));
      }).join("");
      wireSubCategoryToggles(directory);
    }
  }

  function fetchCategories() {
    return ws("gzca.categories.getList", { recursive: "true", public: "true", thumbnail_size: "small" })
      .then(function (result) { return result.categories || []; })
      .catch(function () {
        return [];
      });
  }

  function fetchContact() {
    return ws("gzca.contact.get", {}).catch(function () { return null; });
  }

  var customApiAvailable = null;

  function normalizePage(result, pageNumber, perPage) {
    var images = result && result.images || [];
    var paging = result && result.paging || {};
    return {
      images: images,
      paging: {
        page: Number(paging.page == null ? pageNumber : paging.page),
        per_page: Number(paging.per_page == null ? perPage : paging.per_page),
        count: Number(paging.count == null ? images.length : paging.count),
        total_count: Number(paging.total_count == null ? images.length : paging.total_count)
      }
    };
  }

  function fetchCoreImagePage(options) {
    var pageNumber = Number(options.page || 0);
    var perPage = Number(options.perPage || 16);
    if (options.query) {
      return ws("pwg.images.search", { query: options.query, per_page: perPage, page: pageNumber })
        .then(function (result) { return normalizePage(result, pageNumber, perPage); });
    }
    var values = { recursive: "true", per_page: perPage, page: pageNumber };
    if (options.catId) values.cat_id = options.catId;
    if (options.sort === "hot" || options.sort === "downloads") values.order = "hit desc";
    if (options.sort === "newest" || options.sort === "featured") values.order = "date_available desc";
    return ws("pwg.categories.getImages", values).then(function (result) {
      var normalized = normalizePage(result, pageNumber, perPage);
      if (options.sort === "code") normalized.images.sort(function (a, b) { return imageCode(a).localeCompare(imageCode(b)); });
      return normalized;
    });
  }

  function fetchImagePage(options) {
    options = options || {};
    if (customApiAvailable === false) return fetchCoreImagePage(options);
    var values = {
      recursive: "true",
      per_page: options.perPage || 16,
      page: options.page || 0,
      sort: options.sort || "custom",
      query: options.query || ""
    };
    if (options.catId) values.cat_id = options.catId;
    return ws("gzca.images.getList", values).then(function (result) {
      customApiAvailable = true;
      return normalizePage(result, values.page, values.per_page);
    }).catch(function (error) {
      customApiAvailable = null;
      return fetchCoreImagePage(options);
    });
  }

  function fetchImages(options) {
    options = options || {};
    options.page = 0;
    return fetchImagePage(options).then(function (result) { return result.images; });
  }

  function applyHome(categories) {
    renderDirectory(categories);
    var grid = app.querySelector("[data-home-stream]");
    var pagination = app.querySelector("[data-home-pagination]");
    var pager = createRemotePager(grid, pagination, 16, function (image) {
      return renderWorkCard(image);
    }, "张");
    if (!pager) return Promise.resolve();

    function renderHomeImages(mode) {
      return pager.setLoader(function (pageNumber, pageSize) {
        return fetchImagePage({ page: pageNumber, perPage: pageSize, sort: mode || "featured" });
      });
    }

    app.querySelectorAll("[data-home-sort]").forEach(function (button) {
      button.addEventListener("click", function () {
        var group = button.closest("[data-home-sort-group]");
        if (group) {
          group.querySelectorAll("[data-home-sort]").forEach(function (item) {
            item.setAttribute("aria-pressed", "false");
          });
        }
        button.setAttribute("aria-pressed", "true");
        renderHomeImages(button.getAttribute("data-home-sort"));
      });
    });

    var active = app.querySelector('[data-home-sort][aria-pressed="true"]');
    return renderHomeImages(active ? active.getAttribute("data-home-sort") : "featured");
  }

  function updateCategoryHeader(cat, parent) {
    if (!cat) return;
    document.title = cat.name + " · 图物计划国展素材馆";
    app.querySelectorAll("[data-category-parent]").forEach(function (node) { node.textContent = (parent || cat).name; });
    app.querySelectorAll("[data-category-name]").forEach(function (node) { node.textContent = cat.id_uppercat ? cat.name : "全部作品"; });
    app.querySelectorAll("[data-category-title]").forEach(function (node) { node.textContent = cat.name; });
    app.querySelectorAll("[data-category-desc]").forEach(function (node) {
      node.textContent = stripHtml(cat.comment) || ("“" + cat.name + "”作品已按编号归档，可结合题材标签与排序方式进行选图。");
    });
  }

  function navCode(cat, categories) {
    if (!cat) return "";
    var children = childCategories(categories || [], cat.id);
    return (children[0] && children[0].code_prefix) || cat.code_prefix || ((cat.total_nb_images || cat.nb_images || 0) + "张");
  }

  function isMainArtworkCategory(cat) {
    var name = categoryDisplayName(cat);
    return ["国画", "油画", "版画", "雕塑", "漆画", "水彩", "农民画", "漫画"].indexOf(name) !== -1;
  }

  function renderSideNav(categories, current, parent) {
    var nav = app.querySelector(".side-nav");
    if (!nav || !current) return;
    var parentCat = parent || current;
    var currentItems = childCategories(categories, parentCat.id);
    if (!currentItems.length) currentItems = [parentCat];
    var activeId = current.id_uppercat ? current.id : (currentItems[0] && currentItems[0].id);
    var currentLinks = currentItems.map(function (cat) {
      var active = sameId(cat.id, activeId) ? ' class="is-active"' : "";
      return '<a' + active + ' href="' + escapeHtml(categoryHref(cat)) + '">' + escapeHtml(cat.name) + ' <span>' + escapeHtml(navCode(cat, categories)) + '</span></a>';
    }).join("");

    var otherLinks = "";
    if (isMainArtworkCategory(parentCat)) {
      otherLinks = topCategories(categories).filter(function (cat) {
        return isMainArtworkCategory(cat) && !sameId(cat.id, parentCat.id);
      }).map(function (cat) {
        return '<a class="side-nav-secondary" href="' + escapeHtml(categoryHref(cat)) + '">' + escapeHtml(cat.name) + ' <span>' + escapeHtml(navCode(cat, categories)) + '</span></a>';
      }).join("");
    }

    nav.innerHTML = '<span class="side-nav-label">当前分类</span>' + currentLinks + (otherLinks ? '<span class="side-nav-label">其他分类</span>' + otherLinks : "");
  }

  function renderCategoryFilters(children, currentCat, renderForCat) {
    var row = app.querySelector("[data-filter-group]");
    var status = app.querySelector("[data-filter-status]");
    if (!row) return;
    var items = [{ id: currentCat.id, name: "全部" }].concat(children);
    row.innerHTML = items.map(function (cat, index) {
      return '<button class="filter-chip" type="button" aria-pressed="' + (index === 0 ? "true" : "false") + '" data-backend-cat="' + escapeHtml(cat.id) + '">' + escapeHtml(cat.name) + '</button>';
    }).join("");
    row.querySelectorAll("[data-backend-cat]").forEach(function (button) {
      button.addEventListener("click", function () {
        row.querySelectorAll("[data-backend-cat]").forEach(function (item) { item.setAttribute("aria-pressed", "false"); });
        button.setAttribute("aria-pressed", "true");
        if (status) status.textContent = "当前查看：" + button.textContent.trim();
        renderForCat(Number(button.getAttribute("data-backend-cat")));
      });
    });
  }

  function applyCategory(categories) {
    var catId = categoryIdFromUrl();
    if (!catId) return Promise.resolve();
    var current = categories.find(function (cat) { return sameId(cat.id, catId); });
    if (!current) return Promise.resolve();
    var parent = current.id_uppercat
      ? categories.find(function (cat) { return sameId(cat.id, current.id_uppercat); })
      : current;
    var children = childCategories(categories, current.id);
    var siblings = current.id_uppercat ? childCategories(categories, current.id_uppercat) : topCategories(categories);

    updateCategoryHeader(current, parent || current);
    renderSideNav(categories, current, parent || current);

    var gallery = app.querySelector("[data-category-gallery]");
    var pagination = app.querySelector("[data-pagination]");
    var sortSelect = app.querySelector("[data-sort-select]");
    var activeCatId = current.id;
    var activeSort = sortSelect ? sortSelect.value : "custom";
    var pager = createRemotePager(gallery, pagination, 16, function (image) {
      return renderWorkCard(image, current.name);
    }, "张");

    function renderForCat(nextCatId) {
      activeCatId = nextCatId || current.id;
      if (!pager) return Promise.resolve();
      return pager.setLoader(function (pageNumber, pageSize) {
        return fetchImagePage({ catId: activeCatId, page: pageNumber, perPage: pageSize, sort: activeSort });
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener("change", function () {
        activeSort = sortSelect.value || "custom";
        renderForCat(activeCatId);
      });
    }

    renderCategoryFilters(children, current, renderForCat);
    return renderForCat(current.id);
  }

  function applySearch() {
    var query = params.get("q") || "";
    var term = app.querySelector("[data-search-term]");
    if (term) term.textContent = query || "全部作品";
    var input = app.querySelector("input[name='q']");
    if (input) input.value = query;
    var pager = createRemotePager(app.querySelector("[data-search-results]"), app.querySelector("[data-pagination]"), 5, function (image) {
      return renderResultCard(image);
    }, "条");
    if (!pager) return Promise.resolve();
    return pager.setLoader(function (pageNumber, pageSize) {
      return fetchImagePage({ query: query, page: pageNumber, perPage: pageSize, sort: "custom" });
    });
  }

  function applyContact(contact) {
    if (!contact) return;
    var brandName = contact.brandName || "";
    var brandEn = contact.brandEn || "";
    var wechat = contact.wechat || "";
    var note = contact.note || "";
    var phone = contact.phone || "";
    var qrUrl = contact.qrUrl || "";
    var logoUrl = contact.logoUrl || "";

    if (brandName) {
      app.querySelectorAll(".brand-text strong").forEach(function (node) { node.textContent = brandName; });
      app.querySelectorAll("[data-footer-brand]").forEach(function (node) { node.textContent = "© " + brandName; });
    }
    if (brandEn) app.querySelectorAll(".brand-text > span").forEach(function (node) { node.textContent = brandEn; });
    if (wechat) {
      app.querySelectorAll("[data-contact-wechat]").forEach(function (node) { node.textContent = wechat; });
      app.querySelectorAll("[data-copy-wechat]").forEach(function (button) { button.setAttribute("data-copy-wechat", wechat); });
    }
    if (note) app.querySelectorAll("[data-contact-note]").forEach(function (node) { node.textContent = note; });
    if (phone) app.querySelectorAll("[data-contact-phone]").forEach(function (node) { node.textContent = phone; });
    if (qrUrl) {
      app.querySelectorAll(".qr-box").forEach(function (node) {
        node.innerHTML = '<img src="' + escapeHtml(qrUrl) + '" alt="客服二维码" loading="lazy">';
      });
    }
    if (logoUrl) {
      app.querySelectorAll(".brand-mark img").forEach(function (image) {
        image.src = logoUrl;
        image.alt = brandName || "站点标识";
      });
    }
  }

  function fetchImageInfo(imageId) {
    if (customApiAvailable !== false) {
      return ws("gzca.images.getInfo", { image_id: imageId }).then(function (image) {
        customApiAvailable = true;
        return image;
      }).catch(function () {
        customApiAvailable = null;
        return ws("pwg.images.getInfo", { image_id: imageId });
      });
    }
    return ws("pwg.images.getInfo", { image_id: imageId });
  }

  function fetchImageInfoByCode(code) {
    var normalizedCode = String(code || "").trim().toUpperCase();
    if (!normalizedCode) return Promise.reject(new Error("Missing artwork code"));
    return fetchImagePage({ query: normalizedCode, page: 0, perPage: 5, sort: "code" }).then(function (result) {
      var images = result && result.images || [];
      var exact = images.find(function (item) {
        return imageCode(item) === normalizedCode;
      }) || images[0];
      if (!exact) throw new Error("Artwork not found");
      return exact.id ? fetchImageInfo(exact.id).catch(function () { return exact; }) : exact;
    });
  }

  function detailCategory(image) {
    if (image && image.category) return image.category;
    var categories = image && image.categories;
    if (categories && !Array.isArray(categories)) categories = categories.category || [];
    if (!Array.isArray(categories) || !categories.length) return { id: null, name: "作品分类", url: "" };
    var category = categories[categories.length - 1];
    return {
      id: category.id,
      name: category.name || "作品分类",
      url: category.url || category.page_url || ""
    };
  }

  function applyDetailImage(image) {
    var imageId = Number(image.id || pictureIdFromUrl());
    var title = imageTitle(image);
    var code = imageCode(image);
    var category = detailCategory(image);
    var categoryText = category.name || "作品分类";
    var description = imageMeta(image, "可凭作品编号咨询高清素材与同类作品。");
    var tags = image.tags;
    if (tags && !Array.isArray(tags)) tags = tags.tag || [];
    var tagText = Array.isArray(tags) && tags.length
      ? tags.map(function (tag) { return tag.name || tag; }).join("、")
      : categoryText + "、作品素材";

    document.title = title + " · " + code;
    app.querySelectorAll("[data-detail-title]").forEach(function (node) { node.textContent = title; });
    app.querySelectorAll("[data-detail-code]").forEach(function (node) { node.textContent = code; });
    app.querySelectorAll("[data-detail-category]").forEach(function (node) { node.textContent = categoryText; });
    app.querySelectorAll("[data-detail-category-title]").forEach(function (node) { node.textContent = categoryText; });
    app.querySelectorAll("[data-detail-tags]").forEach(function (node) { node.textContent = tagText; });
    app.querySelectorAll("[data-detail-desc]").forEach(function (node) { node.textContent = description; });

    var mainImage = app.querySelector(".detail-art img");
    if (mainImage) {
      mainImage.src = detailImageUrl(image);
      mainImage.alt = title;
    }

    var categoryHref = category.id ? appUrl("category", { cat_id: category.id }) : appUrl("categories");
    app.querySelectorAll("[data-back-list], [data-related-more], [data-detail-category-link]").forEach(function (link) {
      link.href = categoryHref;
    });
    var relatedTitle = app.querySelector("[data-related-title]");
    if (relatedTitle) relatedTitle.textContent = categoryText + "推荐";

    if (!category.id) return Promise.resolve(true);
    return fetchImagePage({ catId: category.id, page: 0, perPage: 9, sort: "custom" }).then(function (result) {
      var related = (result.images || []).filter(function (item) { return !sameId(item.id, imageId); }).slice(0, 8);
      var grid = app.querySelector("[data-related-grid]");
      if (grid) grid.innerHTML = related.length ? related.map(function (item) { return renderWorkCard(item, categoryText); }).join("") : '<div class="empty-state">该分类更多作品正在整理中</div>';
      var nextLink = app.querySelector("[data-next-detail]");
      if (nextLink && related.length) nextLink.href = detailHref(related[0]);
      return true;
    });
  }

  function applyDetail() {
    var imageId = pictureIdFromUrl();
    if (!imageId) {
      var code = codeFromUrl();
      return code ? fetchImageInfoByCode(code).then(applyDetailImage).catch(function () {
        return applyDetailFromNative();
      }) : applyDetailFromNative();
    }
    return fetchImageInfo(imageId).then(applyDetailImage).catch(function () {
      return applyDetailFromNative();
    });
  }

  function applyDetailFromNative() {
    if (!nativeRoot || !pictureIdFromUrl()) return Promise.resolve(false);
    var mainImage = nativeRoot.querySelector("#theMainImage");
    var titleNode = nativeRoot.querySelector("h2, #imageHeaderBar h2, .titrePage h2");
    var title = titleNode ? titleNode.textContent.trim() : document.title;
    var code = (title + " " + (mainImage && mainImage.title || "")).match(/[A-Z]{2,}(?:-[A-Z0-9]+)+-\d{3,}/i);
    var cleanCode = code ? code[0].toUpperCase() : "ID-" + pictureIdFromUrl();
    var categoryLink = Array.prototype.slice.call(nativeRoot.querySelectorAll("a[href*='/category/']")).pop();
    var categoryText = categoryLink ? categoryLink.textContent.trim() : "作品分类";
    var src = mainImage ? mainImage.src : root + "art-placeholder.svg";
    var desc = mainImage && mainImage.title ? mainImage.title : "可凭作品编号咨询高清素材与同类作品。";

    document.title = title + " · " + cleanCode;
    app.querySelectorAll("[data-detail-title]").forEach(function (node) { node.textContent = title; });
    app.querySelectorAll("[data-detail-code]").forEach(function (node) { node.textContent = cleanCode; });
    app.querySelectorAll("[data-detail-category]").forEach(function (node) { node.textContent = categoryText; });
    app.querySelectorAll("[data-detail-category-title]").forEach(function (node) { node.textContent = categoryText; });
    app.querySelectorAll("[data-detail-tags]").forEach(function (node) { node.textContent = categoryText + "、作品素材"; });
    app.querySelectorAll("[data-detail-desc]").forEach(function (node) { node.textContent = desc; });

    var image = app.querySelector(".detail-art img");
    if (image) {
      image.src = src;
      image.alt = title;
    }
    var backList = app.querySelector("[data-back-list], [data-related-more], [data-detail-category-link]");
    if (backList && categoryLink) backList.href = categoryLink.href;

    var catId = categoryIdFromUrl(categoryLink && categoryLink.href || window.location.href);
    if (catId) {
      fetchImages({ catId: catId, perPage: 9 }).then(function (images) {
        var related = images.filter(function (image) { return !sameId(image.id, pictureIdFromUrl()); }).slice(0, 8);
        var grid = app.querySelector("[data-related-grid]");
        if (grid) grid.innerHTML = related.map(function (image) { return renderWorkCard(image, categoryText); }).join("");
      });
    }
    return Promise.resolve(true);
  }

  function currentAppPage() {
    if (document.body.hasAttribute("data-detail-page")) return "detail";
    if (document.body.hasAttribute("data-category-page")) return "category";
    if (document.body.hasAttribute("data-search-page")) return "search";
    if (params.get("gz_page") === "categories") return "categories";
    return "home";
  }

  setTimeout(function () {
    var page = currentAppPage();
    fetchCategories().then(function (categories) {
      var routeTask;
      if (page === "category") routeTask = applyCategory(categories);
      else if (page === "search") routeTask = applySearch();
      else if (page === "detail") routeTask = applyDetail();
      else routeTask = applyHome(categories);
      fetchContact().then(applyContact);
      return routeTask;
    }).catch(function (error) {
      console.warn("[guozhan] backend bridge fallback:", error && error.message || error);
      if (page === "detail") applyDetailFromNative();
    });
  }, 0);
})();
